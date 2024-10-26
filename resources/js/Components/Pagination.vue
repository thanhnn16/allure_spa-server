<template>
    <div v-if="links.length > 3" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <!-- Mobile view -->
        <div class="flex flex-1 justify-between sm:hidden">
            <Link v-if="previousUrl" 
                :href="previousUrl" 
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Previous
            </Link>
            <Link v-if="nextUrl" 
                :href="nextUrl" 
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Next
            </Link>
        </div>

        <!-- Desktop view -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <template v-for="(link, index) in links" :key="index">
                        <Link v-if="link.url"
                            :href="link.url"
                            :class="[
                                link.active ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0',
                                'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                                index === 0 ? 'rounded-l-md' : '',
                                index === links.length - 1 ? 'rounded-r-md' : '',
                            ]"
                            v-html="link.label"
                        />
                        <span v-else
                            :class="[
                                'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 ring-1 ring-inset ring-gray-300',
                                index === 0 ? 'rounded-l-md' : '',
                                index === links.length - 1 ? 'rounded-r-md' : '',
                            ]"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

export default {
    components: {
        Link
    },
    props: {
        links: {
            type: Array,
            required: true
        }
    },
    setup(props) {
        const previousUrl = computed(() => {
            const previousLink = props.links.find(link => link.label.includes('Previous'))
            return previousLink ? previousLink.url : null
        })

        const nextUrl = computed(() => {
            const nextLink = props.links.find(link => link.label.includes('Next'))
            return nextLink ? nextLink.url : null
        })

        return {
            previousUrl,
            nextUrl
        }
    }
}
</script>
