<script setup>
import { ref, computed } from 'vue'
import menuAside from '@/menuAside.js'
import menuNavBar from '@/menuNavBar.js'
import { useLayoutStore } from '@/Stores/layoutStore'
import BaseIcon from '@/Components/BaseIcon.vue'
import FormControl from '@/Components/FormControl.vue'
import NavBar from '@/Components/NavBar.vue'
import NavBarItemPlain from '@/Components/NavBarItemPlain.vue'
import AsideMenu from '@/Components/AsideMenu.vue'
import FooterBar from '@/Components/FooterBar.vue'
import { router } from '@inertiajs/vue3'
import {
    mdiMenu,
    mdiClose,
    mdiForwardburger,
    mdiBackburger,
    mdiBell,
    mdiBellOutline
} from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import { useNotificationStore } from '@/Stores/notificationStore'
import axios from 'axios'

const layoutStore = useLayoutStore()
const notificationStore = useNotificationStore()

// Thêm state để quản lý menu
const isAsideMobileExpanded = ref(false)
const isAsideLgActive = computed(() => layoutStore.isAsideLgActive)

// Thêm method để toggle menu
const toggleMenu = () => {
    layoutStore.toggleAside()
}

// Reset menu state khi navigate
router.on('navigate', () => {
    isAsideMobileExpanded.value = false
})

const menuClick = (event, item) => {
    if (item.isToggleLightDark) {
        layoutStore.toggleDarkMode()
    }
}

// Thêm hàm markAsRead
const markAsRead = async (notification) => {
    try {
        await axios.post(`/notifications/${notification.id}/mark-as-read`)
        notification.read = true
        notificationStore.unreadCount--
    } catch (error) {
        console.error('Error marking notification as read:', error)
    }
}

// Thêm hàm markAllAsRead
const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-all-as-read')
        notificationStore.notifications.forEach(n => n.read = true)
        notificationStore.unreadCount = 0
    } catch (error) {
        console.error('Error marking all notifications as read:', error)
    }
}
</script>

<template>
    <div class="bg-gray-50 dark:bg-slate-800 min-h-screen">
        <NavBar :menu="menuNavBar" class="fixed w-full top-0 z-50" @menu-click="menuClick" />

        <div class="flex min-h-screen pt-14">
            <AsideMenu :is-aside-mobile-expanded="layoutStore.isAsideMobileExpanded"
                :is-aside-lg-active="layoutStore.isAsideLgActive" :menu="menuAside" @menu-click="menuClick" />

            <main class="flex-1 transition-all duration-300 ease-in-out w-full"
                :class="[layoutStore.isAsideLgActive ? 'lg:pl-24' : 'lg:pl-16']">
                <slot />
                <FooterBar />
            </main>
        </div>
    </div>
</template>

<style scoped>
.aside-menu {
    z-index: 40;
    transition: transform 0.3s ease;
}

@media (max-width: 1024px) {
    .aside-menu {
        transform: translateX(-100%);
    }

    .aside-menu[data-expanded="true"] {
        transform: translateX(0);
    }
}
</style>