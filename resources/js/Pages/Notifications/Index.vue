<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { useNotificationStore } from '@/Stores/notificationStore'
import { formatDistanceToNow } from 'date-fns'
import { vi } from 'date-fns/locale'
import { mdiBell, mdiPackage, mdiCalendar, mdiStar, mdiBellOff, mdiCheckAll } from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue'
import { router } from '@inertiajs/vue3'

const notificationStore = useNotificationStore()
const notifications = ref([])
const loading = ref(true)
const isAsideLgActive = ref(true)

const getNotificationIcon = (type) => {
    switch (type) {
        case 'new_order':
            return mdiPackage
        case 'new_appointment':
            return mdiCalendar
        case 'new_review':
            return mdiStar
        default:
            return mdiBell
    }
}

const handleNotificationClick = (notification) => {
    if (notification.type === 'new_order') {
        router.push(`/admin/orders/${notification.data.order_id}`)
    } else if (notification.type === 'new_appointment') {
        router.push(`/admin/appointments/${notification.data.appointment_id}`)
    } else if (notification.type === 'new_review') {
        router.push(`/admin/reviews/${notification.data.review_id}`)
    }
    notificationStore.markAsRead(notification.id)
}

const markAllAsRead = async () => {
    await notificationStore.markAllAsRead()
    notifications.value = notifications.value.map(notification => ({
        ...notification,
        is_read: true
    }))
}

onMounted(async () => {
    try {
        const response = await axios.get('/api/notifications')
        notifications.value = Array.isArray(response.data.data) ? response.data.data : []
    } catch (error) {
        console.error('Error fetching notifications:', error)
        notifications.value = []
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Thông báo" />

        <SectionMain :is-aside-lg-active="isAsideLgActive">
            <CardBox class="mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-2">
                        <BaseIcon :path="mdiBell" class="w-6 h-6 text-gray-500" />
                        <h1 class="text-2xl font-semibold">Thông báo</h1>
                    </div>
                    <BaseButton 
                        v-if="notifications.length > 0 && notifications.some(n => !n.is_read)" 
                        color="info" 
                        @click="markAllAsRead"
                        :icon="mdiCheckAll" 
                        label="Đánh dấu tất cả là đã đọc"
                        class="hover:shadow-lg transition-shadow" 
                    />
                </div>

                <div v-if="loading" class="flex justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                </div>

                <div v-else-if="!notifications || notifications.length === 0" class="text-center py-12">
                    <BaseIcon 
                        :path="mdiBellOff" 
                        class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4"
                    />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Không có thông báo nào
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Bạn sẽ nhận được thông báo khi có cập nhật mới.
                    </p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="handleNotificationClick(notification)"
                        class="p-4 rounded-lg transition-all duration-200 hover:shadow-md cursor-pointer"
                        :class="{
                            'bg-blue-50 dark:bg-slate-700 border border-blue-200 dark:border-slate-600': !notification.is_read,
                            'bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700': notification.is_read
                        }"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <BaseIcon
                                    :path="getNotificationIcon(notification.type)"
                                    class="w-6 h-6"
                                    :class="{
                                        'text-blue-500': !notification.is_read,
                                        'text-gray-400 dark:text-gray-500': notification.is_read
                                    }"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p 
                                    class="text-sm font-medium mb-0.5"
                                    :class="{
                                        'text-gray-900 dark:text-white': !notification.is_read,
                                        'text-gray-600 dark:text-gray-300': notification.is_read
                                    }"
                                >
                                    {{ notification.title }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                    {{ notification.content }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ formatDistanceToNow(new Date(notification.created_at), { addSuffix: true, locale: vi }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>