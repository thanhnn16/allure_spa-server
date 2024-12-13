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
// Register service worker and get FCM token
const registerServiceWorker = async () => {
    try {
        if ('serviceWorker' in navigator) {
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
                scope: '/'
            });

            // Kiểm tra trạng thái quyền thông báo
            if (Notification.permission === 'denied') {
                // Sử dụng vue-toastification để hiển thị hướng dẫn
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
                return registration;
            }

            return registration;
        }
    } catch (error) {
        console.error('Service worker registration failed:', error);
    }
};

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered:', registration);
            })
            .catch(error => {
                console.log('SW registration failed:', error);
            });
    });
}

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

        // Thay đổi cách xử lý service worker và quyền thông báo
        registerServiceWorker().then(async (registration) => {
            if (!registration) return;
            // Kiểm tra và yêu cầu quyền thông báo
            if (Notification.permission === 'default') {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    console.warn('Notification permission not granted');
                    return;
                }
            }

            // Nếu đã có quyền, tiếp tục xử lý token FCM
            if (Notification.permission === 'granted') {
                const vapidKey = import.meta.env.VITE_FIREBASE_VAPID_KEY;
                if (!vapidKey) {
                    throw new Error('VAPID key is missing');
                }

                const token = await getToken(messaging, {
                    vapidKey: vapidKey,
                    serviceWorkerRegistration: registration
                });

                if (token) {
                    try {
                        await axios.post('/api/fcm/token', {
                            token,
                            device_type: 'web'
                        });
                    } catch (error) {
                        if (error.response && error.response.status === 401) {
                            console.warn('Unauthorized. Please log in again.');
                        } else {
                            throw error;
                        }
                    }
                }
            }
        });

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
