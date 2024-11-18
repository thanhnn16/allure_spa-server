<template>
    <nav class="text-gray-500 dark:text-gray-400 mb-6">
        <ol class="list-none p-0 inline-flex">
            <template v-for="(item, index) in items" :key="index">
                <li class="flex items-center">
                    <template v-if="index < items.length - 1">
                        <Link :href="item.href" class="hover:text-primary-500">
                        {{ item.label }}
                        </Link>
                        <svg class="h-5 w-5 mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template v-else>
                        <span>{{ item.label }}</span>
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