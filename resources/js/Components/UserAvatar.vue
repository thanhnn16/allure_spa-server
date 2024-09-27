<script setup>
import { computed } from 'vue'

const props = defineProps({
  fullName: {
    type: String,
    default: 'Guest'
  },
})

const avatarUrl = computed(() => {
  if (!props.fullName) {
    return 'https://allurespa.com.vn/wp-content/uploads/2024/08/logo_homepage-e1723436204113.png'
  }
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(props.fullName)}`
})

const userInitial = computed(() => {
  if (!props.fullName) return '?'
  return props.fullName.charAt(0).toUpperCase()
})
</script>

<template>
  <img :src="avatarUrl" :alt="fullName" class="rounded-full">
</template>
