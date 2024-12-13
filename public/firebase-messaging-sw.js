importScripts('https://www.gstatic.com/firebasejs/10.13.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.1/firebase-messaging-compat.js');

// Lấy config từ environment variables
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'FIREBASE_CONFIG') {
        initializeFirebase(event.data.config);
    }
});

function initializeFirebase(config) {
    if (!firebase.apps.length) {
        firebase.initializeApp(config);
        initializeMessaging();
    }
}

function initializeMessaging() {
    const messaging = firebase.messaging();

    messaging.onBackgroundMessage((payload) => {
        const notificationOptions = {
            body: payload.notification.body,
            icon: '/images/logo.png',
            badge: '/images/badge.png',
            data: payload.data,
            vibrate: [200, 100, 200],
            actions: [{
                action: 'open',
                title: 'Xem chi tiết'
            }],
            tag: payload.data?.notificationId || 'default' // Tránh trùng lặp thông báo
        };

        return self.registration.showNotification(
            payload.notification.title,
            notificationOptions
        );
    });
}

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'open' || !event.action) {
        const urlToOpen = event.notification.data?.url || '/notifications';
        
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true })
                .then(windowClients => {
                    // Kiểm tra xem có tab nào đang mở không
                    const hadWindowToFocus = windowClients.some(windowClient => {
                        if (windowClient.url === urlToOpen) {
                            windowClient.focus();
                            return true;
                        }
                        return false;
                    });

                    // Nếu không có tab nào đang mở, mở tab mới
                    if (!hadWindowToFocus) {
                        return clients.openWindow(urlToOpen);
                    }
                })
        );
    }
}); 