<script setup>
import { computed } from 'vue'
import { mdiLogout } from '@mdi/js'
import { useForm } from '@inertiajs/vue3'
import AsideMenuItem from '@/Components/AsideMenuItem.vue'
import AsideMenuList from '@/Components/AsideMenuList.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { mdiClose } from '@mdi/js'

defineProps({
  menu: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['menu-click', 'aside-lg-close-click'])

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

const asideLgCloseClick = (event) => {
  emit('aside-lg-close-click', event)
}
</script>

<template>
  <div> <!-- Added a single root node -->
    <aside 
      class="lg:py-2 lg:pl-2 w-78 fixed flex z-40 top-0 h-screen transition-all duration-300 ease-in-out"
    >
      <div class="aside lg:rounded-2xl flex-1 flex flex-col overflow-hidden dark:bg-slate-900 bg-white shadow-lg">
        <!-- Header with close button -->
        <div class="flex items-center h-14 px-4 border-b dark:border-slate-800">
          <div class="flex-1">
            <b class="font-black">Allure Spa</b>
          </div>
          <button 
            class="hidden lg:inline-block xl:hidden p-3 hover:text-blue-500"
            @click.prevent="$emit('aside-lg-close-click')"
          >
            <BaseIcon :path="mdiClose" />
          </button>
        </div>

        <!-- Menu content -->
        <div class="flex-1 overflow-y-auto aside-scrollbars">
          <AsideMenuList 
            :menu="menu" 
            @menu-click="menuClick" 
          />
        </div>
      </div>
    </aside>
  </div>
</template>
