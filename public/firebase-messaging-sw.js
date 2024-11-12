importScripts('https://www.gstatic.com/firebasejs/10.13.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.1/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "your-api-key",
    authDomain: "your-auth-domain",
    projectId: "your-project-id",
    storageBucket: "your-storage-bucket",
    messagingSenderId: "your-messaging-sender-id",
    appId: "your-app-id"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Get messaging instance
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    const { title, body } = payload.notification;

    self.registration.showNotification(title, {
        body,
        icon: '/images/logo.png',
        badge: '/images/badge.png',
        data: payload.data,
        vibrate: [200, 100, 200],
        actions: [{
            action: 'open',
            title: 'Xem chi tiết'
        }]
    });
});

// Handle notification click
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    if (event.action === 'open') {
        const urlToOpen = event.notification.data?.url || '/notifications';
        event.waitUntil(
            clients.openWindow(urlToOpen)
        );
    }
}); 