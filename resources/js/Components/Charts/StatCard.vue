<script setup>
import { computed } from 'vue'
import * as mdiIcons from '@mdi/js'

const props = defineProps({
    title: String,
    value: [Number, String],
    icon: String
})

const iconPath = computed(() => mdiIcons[props.icon])
</script>

<template>
    <div class="p-4 bg-white dark:bg-dark-surface rounded-lg shadow">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-primary-50 dark:bg-primary-900 rounded-full">
                <svg class="w-6 h-6 text-primary-500" viewBox="0 0 24 24">
                    <path :d="iconPath" fill="currentColor" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm text-gray-600 dark:text-gray-400">{{ title }}</h4>
                <p class="text-xl font-semibold">{{ value }}</p>
            </div>
        </div>
    </div>
</template>