<script setup>
defineProps({
    modelValue: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <button @click="emit('update:modelValue', !modelValue)" class="inline-flex items-center">
        <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out"
            :class="modelValue ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out"
                :class="modelValue ? 'translate-x-6' : 'translate-x-1'" />
        </div>
    </button>
</template>