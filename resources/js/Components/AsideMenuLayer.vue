<script setup>
import { computed } from 'vue'
import { mdiLogout, mdiMenu, mdiClose } from '@mdi/js'
import { useForm } from '@inertiajs/vue3'
import AsideMenuItem from '@/Components/AsideMenuItem.vue'
import AsideMenuList from '@/Components/AsideMenuList.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { useLayoutStore } from '@/Stores/layoutStore'

const layoutStore = useLayoutStore()

defineProps({
  menu: {
    type: Array,
    required: true
  },
  isAsideMobileExpanded: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['menu-click'])

const logoutItem = computed(() => ({
  label: 'Logout',
  icon: mdiLogout,
  color: 'info',
  isLogout: true
}))

const form = useForm({})

const handleLogout = () => {
  form.post(route('logout'))
}

const menuClick = (event, item) => {
  if (item.isLogout) {
    handleLogout()
  } else {
    emit('menu-click', event, item)
  }
}
</script>

<template>
  <aside class="fixed flex z-40 top-16 h-[calc(100vh-4rem)]
              transition-all duration-300 ease-in-out lg:translate-x-0 w-78"
    :class="[isAsideMobileExpanded ? 'translate-x-0' : '-translate-x-full']">
    <div class="aside flex-1 flex flex-col overflow-hidden
                dark:bg-dark-surface bg-white shadow-aside
                border border-gray-100 dark:border-dark-border
                lg:rounded-r-2xl">

      <!-- Burger Menu Button - Only show on mobile -->
      <div class="lg:hidden flex items-center h-14 px-4 justify-end">
        <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
          @click="layoutStore.toggleAside">
          <BaseIcon :path="mdiClose" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
        </button>
      </div>

      <!-- Menu -->
      <div class="flex-1 overflow-y-auto no-scrollbar">
        <div class="py-4">
          <AsideMenuList :menu="menu" @menu-click="menuClick" />
        </div>
        <div class="px-4 py-2">
          <AsideMenuItem :item="logoutItem" @menu-click="menuClick" />
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
