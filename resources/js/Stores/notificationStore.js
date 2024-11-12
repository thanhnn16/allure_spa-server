import { defineStore } from 'pinia'
import { useToast } from "vue-toastification"
import { useRouter } from 'vue-router'
import { getMessaging, getToken, onMessage } from 'firebase/messaging'
import { initializeApp } from 'firebase/app'

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
                const firebaseConfig = {
                    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
                    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
                    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
                    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
                    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
                    appId: import.meta.env.VITE_FIREBASE_APP_ID,
                    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID
                }

                const app = initializeApp(firebaseConfig)
                const messaging = getMessaging(app)

                const permission = await Notification.requestPermission()
                if (permission === 'granted') {
                    const token = await getToken(messaging, {
                        vapidKey: import.meta.env.VITE_FIREBASE_VAPID_KEY
                    })

                    this.fcmToken = token

                    // Thêm device_type vào request
                    await axios.post('/api/fcm/token', {
                        token,
                        device_type: 'web'  // Thêm device_type
                    })
                }
            } catch (error) {
                console.error('FCM initialization failed:', error)
            }
        }
    }
})