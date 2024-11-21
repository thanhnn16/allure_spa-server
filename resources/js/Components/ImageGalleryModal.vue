<script setup>
import { ref, watch } from 'vue'
import { mdiChevronLeft, mdiChevronRight, mdiClose } from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
    images: {
        type: Array,
        required: true
    },
    initialIndex: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['close'])

const currentIndex = ref(props.initialIndex)

const next = () => {
    currentIndex.value = (currentIndex.value + 1) % props.images.length
}

const prev = () => {
    currentIndex.value = (currentIndex.value - 1 + props.images.length) % props.images.length
}

// Handle keyboard navigation
const handleKeydown = (e) => {
    if (e.key === 'ArrowRight') next()
    if (e.key === 'ArrowLeft') prev()
    if (e.key === 'Escape') emit('close')
}

// Add and remove event listener
onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
        @click.self="$emit('close')">
        <div class="relative max-w-4xl w-full mx-4">
            <!-- Close button -->
            <button class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors"
                @click="$emit('close')">
                <BaseIcon :path="mdiClose" class="w-8 h-8" />
            </button>

            <!-- Main image -->
            <div class="relative bg-black rounded-lg overflow-hidden">
                <img :src="images[currentIndex].url" class="max-h-[80vh] w-full object-contain"
                    :alt="`Image ${currentIndex + 1} of ${images.length}`" />

                <!-- Navigation buttons -->
                <button v-if="images.length > 1" class="absolute left-0 top-1/2 -translate-y-1/2 p-2 text-white hover:text-gray-300
                               bg-black bg-opacity-50 rounded-r transition-colors" @click="prev">
                    <BaseIcon :path="mdiChevronLeft" class="w-8 h-8" />
                </button>

                <button v-if="images.length > 1" class="absolute right-0 top-1/2 -translate-y-1/2 p-2 text-white hover:text-gray-300
                               bg-black bg-opacity-50 rounded-l transition-colors" @click="next">
                    <BaseIcon :path="mdiChevronRight" class="w-8 h-8" />
                </button>

                <!-- Image counter -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white bg-black bg-opacity-50
                          px-3 py-1 rounded-full text-sm">
                    {{ currentIndex + 1 }} / {{ images.length }}
                </div>
            </div>

            <!-- Thumbnails -->
            <div v-if="images.length > 1" class="flex justify-center mt-4 gap-2 overflow-x-auto pb-2">
                <button v-for="(image, index) in images" :key="index" class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden transition-transform
                               hover:scale-105 focus:outline-none"
                    :class="{ 'ring-2 ring-blue-500': index === currentIndex }" @click="currentIndex = index">
                    <img :src="image.url" class="w-full h-full object-cover" :alt="`Thumbnail ${index + 1}`" />
                </button>
            </div>
        </div>
    </div>
</template>