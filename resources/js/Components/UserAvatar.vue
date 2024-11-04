<script setup>
import { computed, ref } from 'vue'
import { mdiAccount } from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
  fullName: {
    type: String,
    default: 'Guest'
  },
  avatarUrl: {
    type: String,
    default: null
  },
  size: {
    type: String,
    default: 'md' // sm, md, lg
  }
})

const imageError = ref(false)

const displayAvatarUrl = computed(() => {
  if (props.avatarUrl && !imageError.value) {
    return props.avatarUrl
  }
  return null
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'w-8 h-8',
    md: 'w-12 h-12',
    lg: 'w-24 h-24'
  }
  return sizes[props.size] || sizes.md
})

const handleImageError = () => {
  imageError.value = true
}
</script>

<template>
  <div :class="['relative', sizeClasses]">
    <img v-if="displayAvatarUrl" 
      :src="displayAvatarUrl" 
      :alt="fullName" 
      :class="['rounded-full object-cover', sizeClasses]" 
      @error="handleImageError">
    
    <div v-else 
      :class="['rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900', sizeClasses]">
      <BaseIcon 
        :path="mdiAccount" 
        :class="['text-blue-500 dark:text-blue-400', size === 'sm' ? 'w-4 h-4' : 'w-8 h-8']" />
    </div>
  </div>
</template>
