<script setup>
import { ref, onMounted } from 'vue'
import { mdiDelete } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import axios from 'axios'

const notifications = ref([])
const loading = ref(false)
const currentPage = ref(1)
const hasMore = ref(true)

const notificationStatus = {
  UNREAD: 'unread',
  READ: 'read'
}

const getStatusClass = (status) => {
  return status === notificationStatus.UNREAD 
    ? 'bg-blue-50 dark:bg-blue-900/30' 
    : 'bg-white dark:bg-gray-800'
}

const fetchNotifications = async (page = 1) => {
    try {
        loading.value = true
        const response = await axios.get(`/api/notifications/all?page=${page}&include=user`)
        console.log('Dữ liệu notification nhận được:', response.data)
        
        const notificationsData = response.data.data.data || []
            
        const processedNotifications = notificationsData.map(notification => ({
            ...notification,
            created_at: notification.created_at_timestamp || notification.created_at,
            user: notification.user || {}
        }))
        
        if (page === 1) {
            notifications.value = processedNotifications
        } else {
            notifications.value.push(...processedNotifications)
        }
        
        hasMore.value = response.data.data.hasMore
        currentPage.value = page
    } catch (error) {
        console.error('Lỗi khi tải thông báo:', error)
    } finally {
        loading.value = false
    }
}

const loadMore = () => {
    if (!loading.value && hasMore.value) {
        fetchNotifications(currentPage.value + 1)
    }
}

const deleteNotification = async (id) => {
    if (!confirm('Bạn có chắc muốn xóa thông báo này?')) return

    try {
        await axios.delete(`/api/notifications/${id}`)
        notifications.value = notifications.value.filter(n => n.id !== id)
    } catch (error) {
        console.error('Lỗi khi xóa thông báo:', error)
    }
}

const formatDate = (timestamp) => {
    try {
        if (!timestamp) {
            return 'Chưa có ngày'
        }

        let dateObj;
        if (typeof timestamp === 'string') {
            dateObj = new Date(timestamp);
        } else if (typeof timestamp === 'number') {
            dateObj = new Date(timestamp * 1000);
        } else {
            throw new Error('Định dạng timestamp không hợp lệ');
        }

        if (isNaN(dateObj.getTime())) {
            throw new Error('Ngày không hợp lệ');
        }

        return dateObj.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        console.error('Lỗi định dạng ngày:', error, 'timestamp:', timestamp);
        return 'Ngày không xác định';
    }
}

onMounted(() => {
    fetchNotifications()
})
</script>

<template>
    <div>
        <h2 class="text-2xl font-bold mb-6">Lịch sử thông báo</h2>
        
        <div class="space-y-4">
            <div v-for="notification in notifications" 
                :key="notification.id"
                :class="[
                    'border rounded-lg p-4 transition-colors duration-200',
                    getStatusClass(notification.status),
                    'hover:bg-gray-50 dark:hover:bg-gray-800'
                ]"
            >
                <div class="flex justify-between items-start gap-4">
                    <div class="flex-1">
                        <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-medium">Người nhận:</span>
                            <span class="ml-2">{{ notification.user?.full_name || 'Không xác định' }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ notification.user?.phone_number || 'Không có SĐT' }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold">{{ notification.title }}</h3>
                            <span v-if="notification.status === notificationStatus.UNREAD"
                                class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                                Mới
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 whitespace-pre-line">
                            {{ notification.content }}
                        </p>
                        
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                {{ formatDate(notification.created_at) }}
                            </span>
                            <span class="mx-2">•</span>
                            <span class="flex items-center">
                                <i class="fas fa-tag mr-1"></i>
                                {{ notification.type }}
                            </span>
                            <span v-if="notification.category" class="flex items-center">
                                <span class="mx-2">•</span>
                                <i class="fas fa-folder mr-1"></i>
                                {{ notification.category }}
                            </span>
                        </div>
                    </div>
                    
                    <BaseButton
                        color="danger"
                        :icon="mdiDelete"
                        small
                        class="shrink-0"
                        title="Xóa thông báo"
                        @click="deleteNotification(notification.id)"
                    />
                </div>
            </div>

            <div v-if="loading" class="text-center py-4">
                Đang tải...
            </div>

            <div v-if="hasMore && !loading" class="text-center">
                <BaseButton
                    color="info"
                    label="Tải thêm"
                    @click="loadMore"
                />
            </div>

            <div v-if="!hasMore && notifications.length > 0" class="text-center text-gray-500">
                Đã hiển thị tất cả thông báo
            </div>

            <div v-if="notifications.length === 0 && !loading" class="text-center text-gray-500">
                Chưa có thông báo nào
            </div>
        </div>
    </div>
</template> 