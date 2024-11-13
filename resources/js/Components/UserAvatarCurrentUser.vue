A<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import UserAvatar from '@/Components/UserAvatar.vue';

const props = defineProps({
  avatarUrl: {
    type: String,
    default: null
  },
  size: {
    type: String,
    default: 'md'
  }
})

const userName = computed(() => {
  const user = usePage().props.auth.user
  return user && user.full_name ? user.full_name : 'Guest'
})

</script>

<template>
  <UserAvatar :fullName="userName" :avatar-url="avatarUrl" :size="size">
    <slot />
  </UserAvatar>
</template>
