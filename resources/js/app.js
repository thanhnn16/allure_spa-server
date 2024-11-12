import './bootstrap';
import '../css/main.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import axios from 'axios';
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import { initializeApp } from 'firebase/app'
import { getMessaging, onMessage } from 'firebase/messaging'
import { useNotificationStore } from '@/Stores/notificationStore'

const appName = import.meta.env.VITE_APP_NAME || 'AllureSpa';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

const firebaseConfig = {
    // Your Firebase config here
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    // ... other config
}

// Initialize Firebase
const firebaseApp = initializeApp(firebaseConfig)
const messaging = getMessaging(firebaseApp)

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
