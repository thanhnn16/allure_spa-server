<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import UserAvatar from './UserAvatar.vue';

const userName = computed(() => {
  const user = usePage().props.auth.user
  return user ? user.full_name : 'Anonymouse'
})
</script>

<template>
    <UserAvatar :full_name="userName" api="initials">
        <slot />
    </UserAvatar>
</template>
