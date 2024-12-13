import './bootstrap';
import '../css/main.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import axios from 'axios';
import Toast, { useToast } from "vue-toastification";
import "vue-toastification/dist/index.css";
import { initializeApp } from 'firebase/app'
import { getMessaging, onMessage, getToken } from 'firebase/messaging'
import { useNotificationStore } from '@/Stores/notificationStore'
import { useLayoutStore } from '@/Stores/layoutStore'

const appName = import.meta.env.VITE_APP_NAME || 'AllureSpa';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

axios.interceptors.request.use(config => {
    const token = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='));

    if (token) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token.split('=')[1]);
    }
    return config;
}, error => {
    return Promise.reject(error);
});

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            window.location.reload();
        }
        return Promise.reject(error);
    }
);

const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID,
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID
};

// Initialize Firebase
const firebaseApp = initializeApp(firebaseConfig);
const messaging = getMessaging(firebaseApp);

// Hàm kiểm tra và yêu cầu quyền thông báo
const requestNotificationPermission = async () => {
    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            console.warn('Notification permission not granted');
            return false;
        }
        return true;
    } catch (error) {
        console.error('Error requesting notification permission:', error);
        return false;
    }
};

// Hàm đăng ký service worker
const registerServiceWorker = async () => {
    if (!('serviceWorker' in navigator)) {
        console.warn('Service Worker is not supported');
        return null;
    }

    try {
        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
            scope: '/'
        });
        console.log('Service Worker registered successfully', registration);
        return registration;
    } catch (error) {
        console.error('Service Worker registration failed:', error);
        return null;
    }
};

// Hàm lấy FCM token
const getFCMToken = async (registration) => {
    try {
        const vapidKey = import.meta.env.VITE_FIREBASE_VAPID_KEY;
        if (!vapidKey) {
            throw new Error('VAPID key is missing');
        }

        const token = await getToken(messaging, {
            vapidKey,
            serviceWorkerRegistration: registration
        });

        if (!token) {
            throw new Error('Failed to get FCM token');
        }

        return token;
    } catch (error) {
        console.error('Error getting FCM token:', error);
        return null;
    }
};

// Hàm gửi token lên server
const sendTokenToServer = async (token) => {
    try {
        await axios.post('/api/fcm/token', {
            token,
            device_type: 'web'
        });
        console.log('FCM token sent to server successfully');
    } catch (error) {
        console.error('Error sending FCM token to server:', error);
        if (error.response?.status === 401) {
            console.warn('Unauthorized. Please log in again.');
        }
    }
};

// Hàm chính để khởi tạo notification system
const initializeNotificationSystem = async () => {
    // Kiểm tra quyền thông báo
    const hasPermission = await requestNotificationPermission();
    if (!hasPermission) {
        const toast = useToast();
        toast.warning(
            'Thông báo đã bị tắt. Để bật lại: \n' +
            '1. Nhấp vào biểu tượng 🔒 bên cạnh URL\n' +
            '2. Tìm mục "Thông báo"\n' +
            '3. Thay đổi từ "Chặn" sang "Cho phép"',
            {
                timeout: 10000,
                closeButton: true,
                position: "bottom-right",
                icon: "🔔"
            }
        );
        return;
    }

    // Đăng ký service worker
    const registration = await registerServiceWorker();
    if (!registration) return;

    // Sau khi đăng ký service worker thành công
    if (registration) {
        registration.active.postMessage({
            type: 'FIREBASE_CONFIG',
            config: firebaseConfig
        });
    }

    // Lấy và gửi token
    const token = await getFCMToken(registration);
    if (token) {
        await sendTokenToServer(token);
    }
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .use(Toast)

        // Initialize layout settings
        const layoutStore = useLayoutStore()
        layoutStore.initDarkMode()

        // Khởi tạo notification system
        initializeNotificationSystem();

        // Initialize FCM after app creation
        const notificationStore = useNotificationStore()
        notificationStore.initializeFCM()

        // Handle foreground messages
        onMessage(messaging, (payload) => {
            notificationStore.handleFCMMessage(payload)
        })

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
}).then(() => {
    console.log('Inertia app created');
}).catch((error) => {
    console.error('Inertia app creation failed', error);
});
