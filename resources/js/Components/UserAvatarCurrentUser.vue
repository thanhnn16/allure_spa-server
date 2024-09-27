<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import UserAvatar from '@/Components/UserAvatar.vue';

const userName = computed(() => {
  const user = usePage().props.auth.user
  return user && user.full_name ? user.full_name : 'Guest'
})

</script>

<template>
  <UserAvatar :fullName="userName">
    <slot />
  </UserAvatar>
</template>
