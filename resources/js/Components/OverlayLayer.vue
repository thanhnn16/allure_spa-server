<script setup>
defineProps({
  zIndex: {
    type: String,
    default: 'z-50'
  },
  type: {
    type: String,
    default: 'flex'
  }
})

const emit = defineEmits(['overlay-click'])

const overlayClick = (event) => {
  emit('overlay-click', event)
}
</script>

<template>
  <div :class="[type, zIndex]" class="items-center flex-col justify-center overflow-hidden fixed inset-0">
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm dark:bg-black/70" @click="overlayClick" />
    </transition>
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
      <slot />
    </transition>
  </div>
</template>
