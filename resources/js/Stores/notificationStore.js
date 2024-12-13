import { defineStore } from 'pinia'
import { useToast } from "vue-toastification"
import { useRouter } from 'vue-router'
import { getMessaging, getToken, onMessage } from 'firebase/messaging'
import { initializeApp } from 'firebase/app'

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        fcmToken: null,
        loading: false
    }),
    actions: {
        // Handle incoming FCM messages
        handleFCMMessage(payload) {
            const toast = useToast()
            
            // Kiểm tra và lấy nội dung thông báo từ cả notification và data
            const notification = payload.notification || {};
            const data = payload.data || {};
            
            const title = notification.title || data.title || 'Thông báo mới';
            const body = notification.body || data.content || data.message || '';
            const type = data.type || 'info';

            // Thêm notification vào state
            if (data.notification_id) {
                this.notifications.unshift({
                    id: data.notification_id,
                    title: title,
                    body: body, 
                    type: type,
                    data: data,
                    timestamp: new Date(),
                    read: false
                })
                this.unreadCount++
            }

            // Hiển thị toast với nội dung đầy đủ
            toast.info({
                title: title,
                description: body, // Sử dụng description thay vì message
                duration: 5000,
                onClick: () => {
                    if (data.chat_id) {
                        window.dispatchEvent(new CustomEvent('refresh-chat-messages', {
                            detail: { chatId: data.chat_id }
                        }))
                    }
                }
            })
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
        },

        async fetchUnreadCount() {
            try {
                const response = await axios.get('/api/notifications/unread-count')
                this.unreadCount = response.data.data.count
            } catch (error) {
                console.error('Error fetching unread count:', error)
            }
        }
    }
})