<template>
    <nav class="text-gray-600 dark:text-gray-300 mb-6">
        <ol class="list-none p-0 inline-flex">
            <template v-for="(item, index) in items" :key="index">
                <li class="flex items-center">
                    <template v-if="index < items.length - 1">
                        <Link :href="item.href"
                            class="transition-colors duration-200 hover:text-primary-600 dark:hover:text-primary-400">
                        {{ item.label }}
                        </Link>
                        <svg class="h-5 w-5 mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template v-else>
                        <span class="text-gray-800 dark:text-gray-100">{{ item.label }}</span>
                    </template>
                </li>
            </template>
        </ol>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    items: {
        type: Array,
        required: true,
        // Each item should have: { label: string, href?: string}
    }
})
</script>