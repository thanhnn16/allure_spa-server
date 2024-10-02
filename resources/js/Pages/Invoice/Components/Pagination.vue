<template>
    <nav v-if="links && links.length > 3"
        class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
        <div class="-mt-px flex w-0 flex-1">
            <a v-if="!isFirstPage" :href="links[0].url"
                class="inline-flex items-center border-t-2 border-transparent pt-4 pr-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                <v-icon name="mdi-arrow-left" class="mr-3 h-5 w-5 text-gray-400" aria-hidden="true" />
                Trang trước
            </a>
        </div>
        <div class="hidden md:-mt-px md:flex">
            <template v-for="(link, index) in paginationLinks" :key="index">
                <div v-if="link.url === null"
                    class="border-transparent text-gray-500 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">
                    ...
                </div>
                <a v-else :href="link.url" :class="[
                    link.active
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium'
                ]">
                    {{ link.label }}
                </a>
            </template>
        </div>
        <div class="-mt-px flex w-0 flex-1 justify-end">
            <a v-if="!isLastPage" :href="links[links.length - 1].url"
                class="inline-flex items-center border-t-2 border-transparent pt-4 pl-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                Trang sau
                <v-icon name="mdi-arrow-right" class="ml-3 h-5 w-5 text-gray-400" aria-hidden="true" />
            </a>
        </div>
    </nav>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    links: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const isFirstPage = computed(() => props.links && props.links.length > 0 && props.links[0].url === null);
const isLastPage = computed(() => props.links && props.links.length > 0 && props.links[props.links.length - 1].url === null);

const paginationLinks = computed(() => {
    return props.links && props.links.length > 2 ? props.links.slice(1, -1) : [];
});
</script>