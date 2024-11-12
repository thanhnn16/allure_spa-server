importScripts('https://www.gstatic.com/firebasejs/9.x.x/firebase-app-compat.js')
importScripts('https://www.gstatic.com/firebasejs/9.x.x/firebase-messaging-compat.js')

firebase.initializeApp({
    apiKey: 'YOUR_API_KEY',
    authDomain: 'YOUR_AUTH_DOMAIN',
    projectId: 'YOUR_PROJECT_ID',
    // ... other config
})

const messaging = firebase.messaging()

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    const { title, body } = payload.notification
    
    self.registration.showNotification(title, {
        body,
        icon: '/path/to/notification-icon.png'
    })
}) 