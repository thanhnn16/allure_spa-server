<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import UserAvatarCurrentUser from '@/Components/UserAvatarCurrentUser.vue'
import NavBarMenuList from '@/Components/NavBarMenuList.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import { usePage } from '@inertiajs/vue3'
import { mdiChevronDown, mdiChevronUp, mdiBell, mdiWeatherNight, mdiWeatherSunny } from '@mdi/js'
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
            ? `navbar-item-label-active dark:text-slate-400`
            : `navbar-item-label dark:text-white dark:hover:text-slate-400`,
        props.item.menu ? 'lg:py-2 lg:px-3' : 'py-2 px-3'
    ]

    if (props.item.isDesktopNoLabel) {
        base.push('lg:w-16', 'lg:justify-center')
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
    <component :is="is" v-else ref="root" class="block lg:flex items-center relative cursor-pointer"
        :class="componentClass" :href="itemHref" :target="item.target ?? null" @click="menuClick">
        <div class="flex items-center" :class="{
            'bg-gray-100 dark:bg-slate-800 lg:bg-transparent lg:dark:bg-transparent p-3 lg:p-0':
                item.menu
        }">
            <UserAvatarCurrentUser v-if="item.isCurrentUser" :fullName="item.fullName || ''"
                :avatar-url="item.avatarUrl" size="sm" class="mr-3 inline-flex" />
            <div v-else-if="item.icon === mdiBell" class="relative">
                <BaseIcon :path="item.icon" class="transition-colors w-6 h-6 mr-3" />
                <span v-if="notificationStore.unreadCount"
                    class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                    {{ notificationStore.unreadCount }}
                </span>
            </div>
            <BaseIcon 
                v-else-if="item.icon" 
                :path="darkModeIcon" 
                class="transition-colors w-6 h-6 mr-3"
                :class="{'text-yellow-500': !layoutStore.isDark && item.isToggleLightDark,
                        'text-blue-500': layoutStore.isDark && item.isToggleLightDark}" 
            />
            <span class="px-2 transition-colors" :class="{ 'lg:hidden': item.isDesktopNoLabel && item.icon }">
                {{ itemLabel }}
            </span>
            <BaseIcon v-if="item.menu" :path="isDropdownActive ? mdiChevronUp : mdiChevronDown"
                class="hidden lg:inline-flex transition-colors" />
        </div>
        <div v-if="item.menu || item.isNotification"
            class="text-sm border-b border-gray-100 lg:border lg:bg-white lg:absolute lg:top-full lg:right-0 lg:min-w-[300px] lg:z-20 lg:rounded-lg lg:shadow-lg lg:dark:bg-slate-800 dark:border-slate-700"
            :class="{ 'lg:hidden': !isDropdownActive }">
            <div v-if="item.isNotification">
                <div v-if="notificationMenu.length === 0" class="p-4 text-center text-gray-500">
                    Không có thông báo mới
                </div>
                <div v-else class="max-h-[400px] overflow-y-auto">
                    <div v-for="(notification, index) in notificationMenu" :key="index" @click="notification.onClick"
                        class="p-4 hover:bg-gray-50 dark:hover:bg-slate-700 cursor-pointer border-b last:border-b-0 dark:border-slate-700">
                        <div class="flex items-start">
                            <BaseIcon :path="notification.icon" class="w-5 h-5 mr-3 mt-1" />
                            <div class="flex-1">
                                <div class="font-medium"
                                    :class="{ 'text-gray-900 dark:text-white': !notification.isRead, 'text-gray-500': notification.isRead }">
                                    {{ notification.label }}
                                </div>
                                <div class="text-sm text-gray-500">{{ notification.description }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ new Date(notification.timestamp).toLocaleString() }}
                                </div>
                            </div>
                            <div v-if="!notification.isRead" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
                <div class="p-2 border-t dark:border-slate-700">
                    <button @click="notificationStore.markAllAsRead()"
                        class="w-full text-center text-sm text-gray-500 hover:text-gray-700 dark:hover:text-white py-1">
                        Đánh dấu tất cả là đã đọc
                    </button>
                </div>
            </div>
            <NavBarMenuList v-else :menu="item.menu" @menu-click="menuClickDropdown" />
        </div>
    </component>
</template>
