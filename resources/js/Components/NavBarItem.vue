<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import UserAvatarCurrentUser from '@/Components/UserAvatarCurrentUser.vue'
import NavBarMenuList from '@/Components/NavBarMenuList.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import { usePage } from '@inertiajs/vue3'
import { mdiChevronDown, mdiChevronUp, mdiBell, mdiWeatherNight, mdiWeatherSunny, mdiBellOffOutline } from '@mdi/js'
import { useNotificationStore } from '@/Stores/notificationStore'
import { useLayoutStore } from '@/Stores/layoutStore'

const props = defineProps({
    item: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['menu-click'])

const is = computed(() => {
    if (props.item.href) {
        return 'a'
    }

    if (props.item.route) {
        return Link
    }

    return 'div'
})

const itemHref = computed(() => {
    if (props.item.route) {
        try {
            return route(props.item.route);
        } catch (error) {
            console.warn(`Không tìm thấy route '${props.item.route}'. Sử dụng href thay thế.`);
            return props.item.href || '#';
        }
    }
    return props.item.href || '#';
})

const componentClass = computed(() => {
    const base = [
        isDropdownActive.value
            ? `navbar-item-label-active`
            : `navbar-item-label`,
        'relative flex items-center px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700'
    ]

    if (props.item.isDesktopNoLabel) {
        base.push('lg:w-10', 'lg:justify-center')
    }

    return base
})

const itemLabel = computed(() =>
    props.item.isCurrentUser ? usePage().props.auth.user.name : props.item.label
)

const isDropdownActive = ref(false)

const layoutStore = useLayoutStore()

const menuClick = (event) => {
    if (props.item.isToggleLightDark) {
        event.preventDefault()
        layoutStore.toggleDarkMode()
        return
    }

    emit('menu-click', event, props.item)

    if (props.item.menu) {
        isDropdownActive.value = !isDropdownActive.value
    }
}

const menuClickDropdown = (event, item) => {
    emit('menu-click', event, item)
}

const root = ref(null)

const forceClose = (event) => {
    if (root.value && !root.value.contains(event.target)) {
        isDropdownActive.value = false
    }
}

onMounted(() => {
    if (props.item.menu) {
        window.addEventListener('click', forceClose)
    }
})

onBeforeUnmount(() => {
    if (props.item.menu) {
        window.removeEventListener('click', forceClose)
    }
})

const notificationStore = useNotificationStore()

const notificationMenu = computed(() => {
    if (props.item.isNotification) {
        return notificationStore.notifications.map(notification => ({
            label: notification.title,
            description: notification.body,
            icon: notification.type === 'new_order' ? 'mdiPackage' :
                notification.type === 'new_appointment' ? 'mdiCalendar' :
                    notification.type === 'new_review' ? 'mdiStar' : 'mdiBell',
            timestamp: notification.timestamp,
            isRead: notification.read,
            onClick: () => {
                // Xử lý click vào từng thông báo
                if (notification.type === 'new_order') {
                    router.push(`/admin/orders/${notification.data.order_id}`)
                } else if (notification.type === 'new_appointment') {
                    router.push(`/admin/appointments/${notification.data.appointment_id}`)
                } else if (notification.type === 'new_review') {
                    router.push(`/admin/reviews/${notification.data.review_id}`)
                }
                // Đánh dấu thông báo đã đọc
                notificationStore.markAsRead(notification.id)
            }
        }))
    }
    return props.item.menu || []
})

const darkModeIcon = computed(() => {
    if (props.item.isToggleLightDark) {
        return layoutStore.isDark ? mdiWeatherNight : mdiWeatherSunny
    }
    return props.item.icon
})

</script>

<template>
    <BaseDivider v-if="item.isDivider" nav-bar />
    <component :is="is" ref="root" class="block lg:flex items-center relative cursor-pointer" :class="componentClass"
        :href="itemHref" :target="item.target ?? null" @click="menuClick">
        <div class="flex items-center gap-2">
            <div v-if="item.icon === mdiBell" class="relative">
                <BaseIcon :path="item.icon" class="transition-colors w-5 h-5" />
                <span v-if="notificationStore.unreadCount"
                    class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs font-medium">
                    {{ notificationStore.unreadCount }}
                </span>
            </div>
            <BaseIcon v-else-if="item.icon" :path="darkModeIcon" class="transition-colors w-5 h-5" :class="{
                'text-yellow-500': !layoutStore.isDark && item.isToggleLightDark,
                'text-blue-500': layoutStore.isDark && item.isToggleLightDark
            }" />
            <span class="transition-colors" :class="{ 'lg:hidden': item.isDesktopNoLabel && item.icon }">
                {{ itemLabel }}
            </span>
            <BaseIcon v-if="item.menu" :path="isDropdownActive ? mdiChevronUp : mdiChevronDown"
                class="hidden lg:inline-flex w-4 h-4 transition-transform" />
        </div>
        <div v-if="item.menu || item.isNotification"
            class="lg:absolute lg:top-full lg:right-0 lg:mt-2 lg:min-w-[300px] bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-gray-100 dark:border-slate-700 overflow-hidden"
            :class="{ 'lg:hidden': !isDropdownActive }">
            <div v-if="item.isNotification">
                <div v-if="notificationMenu.length === 0" class="p-8 text-center">
                    <BaseIcon :path="mdiBellOffOutline"
                        class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
                    <p class="text-gray-500 dark:text-gray-400">Không có thông báo mới</p>
                </div>
                <div v-else class="max-h-[400px] overflow-y-auto divide-y dark:divide-slate-700">
                    <div v-for="(notification, index) in notificationMenu" :key="index" @click="notification.onClick"
                        class="p-4 hover:bg-gray-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-200">
                        <div class="flex items-start space-x-3">
                            <div :class="{
                                'p-2 rounded-full': true,
                                'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300': notification.type === 'new_order',
                                'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300': notification.type === 'new_appointment',
                                'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300': notification.type === 'new_review'
                            }">
                                <BaseIcon :path="notification.icon" class="w-5 h-5" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <p class="font-medium truncate" :class="{
                                        'text-gray-900 dark:text-white': !notification.isRead,
                                        'text-gray-500 dark:text-gray-400': notification.isRead
                                    }">
                                        {{ notification.label }}
                                    </p>
                                    <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                        {{ new Date(notification.timestamp).toLocaleTimeString([], {
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                    {{ notification.description }}
                                </p>
                            </div>

                            <div v-if="!notification.isRead"
                                class="w-2.5 h-2.5 bg-blue-500 rounded-full flex-shrink-0" />
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                    <div class="flex justify-between items-center">
                        <button @click="notificationStore.markAllAsRead()"
                            class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                            Đánh dấu tất cả là đã đọc
                        </button>
                        <Link href="/notifications"
                            class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors duration-200">
                        Xem tất cả
                        </Link>
                    </div>
                </div>
            </div>
            <NavBarMenuList v-else :menu="item.menu" @menu-click="menuClickDropdown" />
        </div>
    </component>
</template>
