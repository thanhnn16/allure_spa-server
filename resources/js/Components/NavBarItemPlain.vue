<script setup>
defineProps({
  display: {
    type: String,
    default: 'flex'
  },
  useMargin: Boolean
})
</script>

<template>
  <div :class="[
    display,
    useMargin ? 'mx-3' : 'px-3 py-2',
    'items-center cursor-pointer rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors'
  ]" class="navbar-item-label">
    <slot />
  </div>
</template>
