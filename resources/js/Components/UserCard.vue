<script setup>
import { computed } from 'vue'
import { useMainStore } from '@/Stores/main'
import { mdiCheckDecagram, mdiAccountTie, mdiClockOutline } from '@mdi/js'
import BaseLevel from '@/Components/BaseLevel.vue'
import UserAvatarCurrentUser from '@/Components/UserAvatarCurrentUser.vue'
import CardBox from '@/Components/CardBox.vue'
import PillTag from '@/Components/PillTag.vue'
import BaseIcon from '@/Components/BaseIcon.vue'

const mainStore = useMainStore()

const props = defineProps({
  userData: {
    type: Object,
    required: true
  }
})

const userName = computed(() => mainStore.fullName)
const userRole = computed(() => {
  const roles = {
    'admin': 'Quản trị viên',
    'staff': 'Nhân viên',
    'user': 'Khách hàng'
  }
  return roles[mainStore.user.role] || mainStore.user.role
})
</script>

<template>
  <CardBox>
    <BaseLevel type="justify-around lg:justify-center">
      <UserAvatarCurrentUser class="lg:mx-12" :avatar-url="mainStore.avatarUrl" />
      <div class="space-y-3 text-center md:text-left lg:mx-12">
        <h1 class="text-2xl">
          Xin chào, <b>{{ userName }}</b>!
        </h1>

        <!-- Role -->
        <div class="flex items-center justify-center md:justify-start text-gray-500">
          <BaseIcon :path="mdiAccountTie" class="mr-2" size="20" />
          <span>{{ userRole }}</span>
        </div>

        <!-- Email -->
        <div v-if="mainStore.user.email" class="flex items-center justify-center md:justify-start text-gray-500">
          <BaseIcon :path="mdiClockOutline" class="mr-2" size="20" />
          <span>{{ mainStore.user.email }}</span>
        </div>

        <div class="flex justify-center md:block">
          <PillTag v-if="mainStore.user.email_verified_at" label="Đã xác thực" color="info" :icon="mdiCheckDecagram" />
        </div>
      </div>
    </BaseLevel>
  </CardBox>
</template>
