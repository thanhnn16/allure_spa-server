import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.Pusher = Pusher;

// Configure axios defaults
const configureAxios = () => {
    // Set up CSRF token
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    } else {
        console.error('CSRF token not found');
    }

    // Configure other defaults
    window.axios.defaults.withCredentials = true;
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}

configureAxios();

// Kiểm tra xem có key không trước khi khởi tạo Echo
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;

if (pusherKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: true
    });
} else {
    console.error('Pusher key not found');
}
