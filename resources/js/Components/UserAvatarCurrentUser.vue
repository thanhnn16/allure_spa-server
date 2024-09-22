<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import UserAvatar from './UserAvatar.vue';

const userName = computed(() => {
  const user = usePage().props.auth.user
  return user ? user.name : ''
})
</script>

<template>
    <UserAvatar :username="userName" api="initials">
        <slot />
    </UserAvatar>
</template>
