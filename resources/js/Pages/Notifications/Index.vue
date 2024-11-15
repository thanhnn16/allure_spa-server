<script setup>
import { ref, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { useNotificationStore } from '@/Stores/notificationStore'
import { formatDistanceToNow, parseISO } from 'date-fns'
import { vi } from 'date-fns/locale'
import { mdiBell, mdiPackage, mdiCalendar, mdiStar, mdiBellOff, mdiCheckAll } from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue'
import { router } from '@inertiajs/vue3'

const notificationStore = useNotificationStore()
const page = usePage()
const notifications = ref(page.props.initialNotifications || [])
const hasMore = ref(page.props.hasMore || false)
const currentPage = ref(1)
const loading = ref(false)
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

const loadMore = async () => {
    if (!hasMore.value || loading.value) return

    try {
        loading.value = true
        currentPage.value++
        
        const response = await axios.get('/api/notifications', {
            params: { page: currentPage.value }
        })
        
        if (response.data.data) {
            notifications.value = [...notifications.value, ...response.data.data]
            hasMore.value = response.data.hasMore
        }
    } catch (error) {
        console.error('Error loading more notifications:', error)
    } finally {
        loading.value = false
    }
}

const handleScroll = (e) => {
    const element = e.target
    if (element.scrollHeight - element.scrollTop === element.clientHeight) {
        loadMore()
    }
}

const handleNotificationClick = async (notification) => {
    try {
        await axios.post(`/api/notifications/${notification.id}/mark-as-read`)
        notification.is_read = true
        
        if (notification.url) {
            router.push(notification.url)
        }
    } catch (error) {
        console.error('Error marking notification as read:', error)
    }
}

const markAllAsRead = async () => {
    try {
        await axios.post('/api/notifications/mark-all-as-read')
        notifications.value = notifications.value.map(notification => ({
            ...notification,
            is_read: true
        }))
    } catch (error) {
        console.error('Error marking all as read:', error)
    }
}

const formatNotificationDate = (dateString) => {
    try {
        if (!dateString) return 'Invalid date'
        const date = parseISO(dateString)
        if (isNaN(date.getTime())) return 'Invalid date'
        return formatDistanceToNow(date, { addSuffix: true, locale: vi })
    } catch (error) {
        console.error('Error formatting date:', error)
        return 'Invalid date'
    }
}
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
                    <BaseButton v-if="notifications.length > 0 && notifications.some(n => !n.is_read)" color="info"
                        @click="markAllAsRead" :icon="mdiCheckAll" label="Đánh dấu tất cả là đã đọc"
                        class="hover:shadow-lg transition-shadow" />
                </div>

                <div 
                    class="space-y-3 overflow-y-auto max-h-[600px]" 
                    @scroll="handleScroll"
                >
                    <div v-if="!notifications || notifications.length === 0" class="text-center py-12">
                        <BaseIcon :path="mdiBellOff" class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Không có thông báo nào
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Bạn sẽ nhận được thông báo khi có cập nhật mới.
                        </p>
                    </div>

                    <div v-else>
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
                                    <BaseIcon :path="getNotificationIcon(notification.type)" class="w-6 h-6" :class="{
                                        'text-blue-500': !notification.is_read,
                                        'text-gray-400 dark:text-gray-500': notification.is_read
                                    }" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium mb-0.5" :class="{
                                        'text-gray-900 dark:text-white': !notification.is_read,
                                        'text-gray-600 dark:text-gray-300': notification.is_read
                                    }">
                                        {{ notification.title }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {{ notification.content }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        {{ formatNotificationDate(notification.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="loading" class="flex justify-center py-4">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                        </div>

                        <div v-if="hasMore && !loading" class="text-center py-4">
                            <BaseButton
                                color="info"
                                @click="loadMore"
                                label="Tải thêm"
                                class="hover:shadow-lg transition-shadow"
                            />
                        </div>
                    </div>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
.space-y-3 {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.space-y-3::-webkit-scrollbar {
    width: 6px;
}

.space-y-3::-webkit-scrollbar-track {
    background: transparent;
}

.space-y-3::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}
</style>