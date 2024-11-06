<template>
    <Dialog :open="true" @close="$emit('close')" class="relative z-50">
        <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <DialogPanel class="w-full max-w-4xl max-h-[90vh] rounded bg-white dark:bg-slate-800 p-6 flex flex-col">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6 flex-shrink-0">
                    <DialogTitle class="text-lg font-medium dark:text-white">
                        Xem Trước Cấu Hình: {{ config.ai_name }}
                    </DialogTitle>
                    <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                        <BaseIcon :path="mdiClose" size="24" />
                    </button>
                </div>

                <!-- Content -->
                <div class="space-y-6 overflow-y-auto scrollbar-hide flex-grow">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Loại Cấu Hình</h3>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ getConfigType(config.type) }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Ngôn Ngữ</h3>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ getLanguage(config.language) }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Model AI</h3>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ config.model_type }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Temperature</h3>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ config.temperature }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Max Tokens</h3>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ config.max_tokens }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Trạng Thái</h3>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1"
                                    :class="config.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ config.is_active ? 'Đang hoạt động' : 'Tạm dừng' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Context -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Context</h3>
                        <div class="bg-gray-50 dark:bg-slate-700 rounded-lg p-4 overflow-auto max-h-60">
                            <pre
                                class="text-sm text-gray-900 dark:text-gray-200 whitespace-pre-wrap">{{ config.context }}</pre>
                        </div>
                    </div>

                    <!-- Function Declarations -->
                    <div v-if="config.function_declarations">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Function Declarations</h3>
                        <div class="bg-gray-50 dark:bg-slate-700 rounded-lg p-4 overflow-auto max-h-60">
                            <pre
                                class="text-sm text-gray-900 dark:text-gray-200">{{ formatJson(config.function_declarations) }}</pre>
                        </div>
                    </div>

                    <!-- Tool Config -->
                    <div v-if="config.tool_config">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Tool Configuration</h3>
                        <div class="bg-gray-50 dark:bg-slate-700 rounded-lg p-4 overflow-auto max-h-60">
                            <pre
                                class="text-sm text-gray-900 dark:text-gray-200">{{ formatJson(config.tool_config) }}</pre>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end flex-shrink-0">
                    <BaseButton type="button" label="Đóng" color="white" @click="$emit('close')" />
                </div>
            </DialogPanel>
        </div>
    </Dialog>
</template>

<style scoped>
/* Ẩn thanh cuộn nhưng vẫn cho phép cuộn */
.scrollbar-hide {
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
    /* Chrome, Safari and Opera */
}
</style>

<script setup>
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import { mdiClose } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
    config: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

// Helper functions
const getConfigType = (type) => {
    const types = {
        'system_prompt': 'Prompt Hệ Thống',
        'vision_config': 'Phân Tích Hình Ảnh',
        'general': 'Cài Đặt Chung'
    }
    return types[type] || type
}

const getLanguage = (lang) => {
    const languages = {
        'vi': 'Tiếng Việt',
        'en': 'Tiếng Anh',
        'ja': 'Tiếng Nhật'
    }
    return languages[lang] || lang
}

const formatJson = (value) => {
    try {
        if (typeof value === 'string') {
            value = JSON.parse(value)
        }
        return JSON.stringify(value, null, 2)
    } catch (e) {
        return value
    }
}
</script>