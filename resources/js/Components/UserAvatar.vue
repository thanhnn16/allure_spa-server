<script setup>
import { computed } from 'vue'

const props = defineProps({
  full_name: {
    type: String,
  },
  avatar: {
    type: String,
    default: null
  },
  api: {
    type: String,
    default: 'avataaars'
  }
})

const avatar = computed(() => {
  if (props.avatar) return props.avatar
  return `https://api.dicebear.com/7.x/${props.api}/svg?seed=${props.full_name.replace(
    /[^a-z0-9]+/gi,
    '-'
  )}.svg`
})

const username = computed(() => props.full_name)

</script>

<template>
  <div>
    <img v-if="avatar" :src="avatar" :alt="username"
      class="rounded-full block h-auto w-full max-w-full bg-gray-100 dark:bg-slate-800" />
    <slot />
  </div>
</template>
