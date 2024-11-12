import { defineStore } from 'pinia'
import { useToast } from "vue-toastification"

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        fcmToken: null
    }),
    actions: {
        // Handle incoming FCM messages
        handleFCMMessage(payload) {
            const toast = useToast()
            
            // Add notification to state
            this.notifications.unshift({
                id: Date.now(),
                title: payload.notification.title,
                body: payload.notification.body,
                data: payload.data,
                timestamp: new Date(),
                read: false
            })
            
            // Update unread count
            this.unreadCount++
            
            // Show toast notification
            toast.info(`${payload.notification.title}: ${payload.notification.body}`)
        },

        // Request FCM permission and get token
        async initializeFCM() {
            try {
                const messaging = firebase.messaging()
                
                const permission = await Notification.requestPermission()
                if (permission === 'granted') {
                    const token = await messaging.getToken()
                    this.fcmToken = token
                    
                    // Send token to backend
                    await axios.post('/api/fcm/token', { token })
                }
            } catch (error) {
                console.error('FCM initialization failed:', error)
            }
        }
    }
}) 