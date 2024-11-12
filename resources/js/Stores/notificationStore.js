import { defineStore } from 'pinia'
import { useToast } from "vue-toastification"
import { useRouter } from 'vue-router'

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
            const router = useRouter()

            // Add notification to state
            this.notifications.unshift({
                id: payload.data.notification_id,
                title: payload.notification.title,
                body: payload.notification.body,
                type: payload.data.type,
                data: payload.data,
                timestamp: new Date(),
                read: false
            })

            // Update unread count
            this.unreadCount++

            // Show toast with action based on type
            const actions = {
                new_appointment: () => router.push(`/admin/appointments/${payload.data.appointment_id}`),
                new_order: () => router.push(`/admin/orders/${payload.data.order_id}`),
                new_review: () => router.push(`/admin/reviews/${payload.data.review_id}`)
            }

            const options = {
                title: payload.notification.title,
                message: payload.notification.body,
                onClick: actions[payload.data.type]
            }

            // Add icon and style based on type
            if (payload.data.type === 'new_appointment') {
                options.type = 'info'
                options.timeout = 8000 // Show longer for appointments
            }

            toast(options)
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