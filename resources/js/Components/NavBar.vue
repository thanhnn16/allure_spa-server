<script setup>
import { ref, computed } from 'vue'
import { containerMaxW } from '@/config.js'
import BaseIcon from '@/Components/BaseIcon.vue'
import NavBarMenuList from '@/Components/NavBarMenuList.vue'
import NavBarItemPlain from '@/Components/NavBarItemPlain.vue'
import { mdiMenu } from '@mdi/js'
import { useLayoutStore } from '@/Stores/layoutStore'

const layoutStore = useLayoutStore()

defineProps({
  menu: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['menu-click'])

const menuClick = (event, item) => {
  emit('menu-click', event, item)
}
</script>

<template>
  <nav class="top-0 inset-x-0 fixed bg-white dark:bg-slate-800 h-14 z-30 transition-all duration-300 ease-in-out
              border-b border-gray-100 dark:border-slate-700">
    <div class="flex h-full items-center justify-between px-4 lg:px-6">
      <!-- Left section -->
      <div class="flex items-center gap-4">
        <!-- Mobile Burger Menu -->
        <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
          @click="layoutStore.toggleAside">
          <BaseIcon :path="mdiMenu" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
        </button>

        <!-- Brand/Logo -->
        <div class="flex items-center">
          <h1 class="text-xl font-bold bg-gradient-to-r from-primary-600 to-primary-400 
                     bg-clip-text text-transparent">
            Allure Spa
          </h1>
        </div>

        <slot name="left" />
      </div>

      <!-- Center section -->
      <div class="flex-1 flex items-center justify-center">
        <slot />
      </div>

      <!-- Right section -->
      <div class="flex items-center gap-3">
        <NavBarMenuList :menu="menu" @menu-click="menuClick" />
        <slot name="right" />
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* Add smooth transition for menu items */
:deep(.navbar-item-label) {
  @apply transition-colors duration-200;
}

:deep(.navbar-item-label-active) {
  @apply text-primary-600 dark:text-primary-400;
}

/* Add hover effect for menu items */
:deep(.navbar-item-label:hover) {
  @apply text-primary-600 dark:text-primary-400;
}
</style>
