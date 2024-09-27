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
  label: 'Đăng xuất',
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
  <aside id="aside" class="lg:py-2 lg:pl-2 w-78 fixed flex z-40 top-0 h-screen transition-position overflow-hidden">
    <div class="aside lg:rounded-2xl flex-1 flex flex-col overflow-hidden dark:bg-slate-900">
      <div class="aside-brand flex flex-row h-14 items-center justify-between dark:bg-slate-900">
        <div class="text-center flex-1 lg:text-left xl:text-center xl:pl-0">
          <b class="font-black">Allure Spa</b>
        </div>
        <button class="hidden lg:inline-block xl:hidden p-3" @click.prevent="asideLgCloseClick">
          <BaseIcon :path="mdiClose" />
        </button>
      </div>
      <div class="flex-1 overflow-y-auto overflow-x-hidden aside-scrollbars dark:aside-scrollbars-[slate]">
        <AsideMenuList :menu="menu" @menu-click="menuClick" />
      </div>

      <ul>
        <AsideMenuItem :item="logoutItem" @menu-click="menuClick" />
      </ul>
    </div>
  </aside>
</template>
