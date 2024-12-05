<script setup>
import { ref, onMounted } from 'vue'
import { mdiDelete } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import axios from 'axios'

const notifications = ref([])
const loading = ref(false)
const currentPage = ref(1)
const hasMore = ref(true)

const fetchNotifications = async (page = 1) => {
    try {
        loading.value = true
        const response = await axios.get(`/api/notifications/all?page=${page}`)
        console.log('Dữ liệu notification nhận được:', response.data)
        
        const notificationsData = response.data.data.data || []
            
        console.log('notificationsData:', notificationsData)
        
        const processedNotifications = notificationsData.map(notification => ({
            ...notification,
            created_at: notification.created_at_timestamp || notification.created_at
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
            <div v-for="notification in notifications" :key="notification.id"
                class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">{{ notification.title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ notification.content }}</p>
                        <div class="mt-2 text-xs text-gray-500">
                            <span>{{ formatDate(notification.created_at) }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ notification.type }}</span>
                        </div>
                    </div>
                    <BaseButton
                        color="danger"
                        :icon="mdiDelete"
                        small
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