<script setup>
import { ref } from 'vue'
import menuAside from '@/menuAside.js'
import menuNavBar from '@/menuNavBar.js'
import { useDarkModeStore } from '@/Stores/darkMode.js'
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
    mdiBackburger
} from '@mdi/js'

const layoutAsidePadding = 'xl:pl-78'

const darkModeStore = useDarkModeStore()

// Thêm state để quản lý menu
const isAsideMobileExpanded = ref(false)
const isAsideLgActive = ref(false)

// Thêm method để toggle menu
const toggleMenu = () => {
    isAsideMobileExpanded.value = !isAsideMobileExpanded.value
}

// Reset menu state khi navigate
router.on('navigate', () => {
    isAsideMobileExpanded.value = false
    isAsideLgActive.value = false
})

const menuClick = (event, item) => {
    if (item.isToggleLightDark) {
        darkModeStore.set()
    }
}
</script>

<template>
    <div :class="{ 'overflow-hidden lg:overflow-visible': isAsideMobileExpanded }">
        <div :class="[layoutAsidePadding, { 'ml-78 lg:ml-0': isAsideMobileExpanded }]"
            class="pt-14 min-h-screen w-screen transition-position lg:w-auto bg-gray-50 dark:bg-slate-800 dark:text-slate-100">

            <!-- Navbar với burger button -->
            <NavBar :menu="menuNavBar" :class="[layoutAsidePadding, { 'ml-78 lg:ml-0': isAsideMobileExpanded }, 'z-50']"
                @menu-click="menuClick">
                <!-- Burger button cho mobile -->
                <NavBarItemPlain display="flex lg:hidden" @click.prevent="toggleMenu">
                    <BaseIcon :path="isAsideMobileExpanded ? mdiBackburger : mdiForwardburger" size="24" />
                </NavBarItemPlain>

                <!-- Burger button cho desktop -->
                <NavBarItemPlain display="hidden lg:flex xl:hidden" @click.prevent="isAsideLgActive = true">
                    <BaseIcon :path="mdiMenu" size="24" />
                </NavBarItemPlain>

                <NavBarItemPlain use-margin>
                    <FormControl placeholder="Tìm kiếm (ctrl+k)" ctrl-k-focus transparent borderless />
                </NavBarItemPlain>
            </NavBar>

            <!-- AsideMenu với các props mới -->
            <AsideMenu v-bind:class="[
                isAsideMobileExpanded ? 'left-0' : '-left-78',
                { 'lg:left-0': !isAsideLgActive },
                'transition-all duration-300 ease-in-out'
            ]" :menu="menuAside" :is-aside-mobile-expanded="isAsideMobileExpanded" :is-aside-lg-active="isAsideLgActive"
                @menu-click="menuClick" @aside-lg-close-click="isAsideLgActive = false" />

            <slot />
            <FooterBar>
                Cần hỗ trợ? Gọi ngay:
                <a href="tel:0346542636" target="_blank" class="text-blue-600">0346542636 - Thành</a>
            </FooterBar>
        </div>
    </div>
</template>
