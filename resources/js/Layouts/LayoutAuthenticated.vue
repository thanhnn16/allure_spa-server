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
    if (window.innerWidth >= 1024) {
        layoutStore.toggleAside()
    } else {
        isAsideMobileExpanded.value = !isAsideMobileExpanded.value
    }
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
    <div :class="{ 'overflow-hidden lg:overflow-visible': isAsideMobileExpanded }">
        <div :class="[
            'pt-14 min-h-screen w-screen transition-position lg:w-auto bg-gray-50 dark:bg-slate-800 dark:text-slate-100',
            { 'lg:ml-78': isAsideLgActive }
        ]">
            <!-- Navbar -->
            <NavBar :menu="menuNavBar" class="fixed w-full top-0 z-50" @menu-click="menuClick">
                <!-- Burger button -->
                <NavBarItemPlain display="flex" @click.prevent="toggleMenu">
                    <BaseIcon :path="isAsideMobileExpanded || isAsideLgActive ? mdiBackburger : mdiForwardburger"
                        size="24" />
                </NavBarItemPlain>

                <NavBarItemPlain display="hidden lg:flex xl:hidden" @click.prevent="isAsideLgActive = true">
                    <BaseIcon :path="mdiMenu" size="24" />
                </NavBarItemPlain>

                <NavBarItemPlain use-margin>
                    <FormControl placeholder="Tìm kiếm (ctrl+k)" ctrl-k-focus transparent borderless />
                </NavBarItemPlain>
            </NavBar>

            <!-- AsideMenu -->
            <AsideMenu :class="[
                isAsideMobileExpanded ? 'left-0' : '-left-78',
                isAsideLgActive ? 'lg:left-0' : 'lg:-left-78',
                'fixed top-0 h-screen z-40 transition-all duration-300 ease-in-out'
            ]" :menu="menuAside" :is-aside-mobile-expanded="isAsideMobileExpanded"
                :is-aside-lg-active="isAsideLgActive" @menu-click="menuClick"
                @aside-lg-close-click="isAsideLgActive = false" />

            <!-- Main content -->
            <div :class="[
                isAsideLgActive ? 'lg:ml-78' : 'lg:ml-0',
                'transition-all duration-300 ease-in-out'
            ]">
                <SectionMain :is-aside-lg-active="isAsideLgActive">
                    <slot />
                </SectionMain>

                <FooterBar>
                    Cần hỗ trợ? Gọi ngay:
                    <a href="tel:0346542636" target="_blank" class="text-blue-600">0346542636 - Thành</a>
                </FooterBar>
            </div>
        </div>
    </div>
</template>