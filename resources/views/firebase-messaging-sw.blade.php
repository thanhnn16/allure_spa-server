importScripts('https://www.gstatic.com/firebasejs/9.x.x/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.x.x/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "{{ $config['apiKey'] }}",
    authDomain: "{{ $config['authDomain'] }}",
    projectId: "{{ $config['projectId'] }}",
    storageBucket: "{{ $config['storageBucket'] }}",
    messagingSenderId: "{{ $config['messagingSenderId'] }}",
    appId: "{{ $config['appId'] }}",
    measurementId: "{{ $config['measurementId'] }}"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const { title, body } = payload.notification;
    
    self.registration.showNotification(title, {
        body,
        icon: '/images/logo.png',
        badge: '/images/badge.png',
        data: payload.data,
        vibrate: [200, 100, 200],
        actions: [
            {
                action: 'open',
                title: 'Xem chi tiết'
            }
        ]
    });
});

// Handle notification click
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    if (event.action === 'open') {
        // Handle click action
        const urlToOpen = event.notification.data?.url || '/notifications';
        
        event.waitUntil(
            clients.openWindow(urlToOpen)
        );
    }
}); 