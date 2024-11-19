<template>
    <CardBoxModal v-model="isModalActive" :title="'Quản lý bản dịch'" :has-cancel="false" :has-button="false"
        max-width="2xl">
        <div class="space-y-6 max-h-[70vh] overflow-y-auto px-2 custom-scrollbar">
            <!-- Language Selector with Flags -->
            <div
                class="flex items-center space-x-4 sticky top-0 bg-white dark:bg-dark-bg py-4 z-10 border-b dark:border-dark-border">
                <label class="w-32 font-medium text-gray-700 dark:text-gray-300">Ngôn ngữ:</label>
                <div class="flex space-x-2">
                    <button v-for="lang in availableLanguages" :key="lang.code" @click="selectedLanguage = lang.code"
                        class="px-4 py-2 rounded-lg transition-all duration-200 flex items-center space-x-2" :class="[
                            selectedLanguage === lang.code
                                ? 'bg-primary-500 text-white'
                                : 'bg-gray-100 dark:bg-dark-bg hover:bg-gray-200 dark:hover:bg-dark-surface'
                        ]">
                        <span>{{ lang.name }}</span>
                    </button>
                </div>
            </div>

            <!-- Side by Side Translation View -->
            <div v-if="translations[selectedLanguage]" class="space-y-6">
                <div v-for="field in translatableFields" :key="field.key" class="space-y-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Original Content (Vietnamese) -->
                        <div class="space-y-2">
                            <label class="font-medium text-gray-500 dark:text-gray-400 flex items-center space-x-2">
                                <span>{{ field.label }}</span>
                            </label>
                            <div class="bg-gray-50 dark:bg-dark-bg rounded-lg p-3">
                                <p class="whitespace-pre-line">{{ product[field.key] || 'Chưa có nội dung' }}</p>
                            </div>
                        </div>

                        <!-- Translation Content -->
                        <div class="space-y-2">
                            <label
                                class="font-medium text-gray-700 dark:text-gray-300 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span>{{ field.label }}</span>
                                </div>
                                <span class="text-sm text-gray-500">
                                    {{ translations[selectedLanguage][field.key]?.length || 0 }}/{{ field.maxLength }}
                                </span>
                            </label>
                            <div class="relative">
                                <textarea v-if="field.type === 'textarea'"
                                    v-model="translations[selectedLanguage][field.key]"
                                    class="w-full form-textarea rounded-lg dark:bg-dark-bg min-h-[100px]"
                                    :maxlength="field.maxLength"
                                    :placeholder="`Nhập bản dịch cho ${field.label.toLowerCase()}...`"></textarea>
                                <input v-else v-model="translations[selectedLanguage][field.key]"
                                    class="w-full form-input rounded-lg dark:bg-dark-bg" :type="field.type"
                                    :maxlength="field.maxLength"
                                    :placeholder="`Nhập bản dịch cho ${field.label.toLowerCase()}...`">
                                <button v-if="!translations[selectedLanguage][field.key] && product[field.key]"
                                    @click="translateField(field.key)"
                                    class="absolute right-2 top-2 text-primary-500 hover:text-primary-600 dark:text-primary-400"
                                    title="Dịch tự động">
                                    <i class="mdi mdi-translate text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end space-x-2">
                <BaseButton label="Hủy" color="white" @click="isModalActive = false" />
                <BaseButton label="Lưu" color="success" @click="saveTranslations" :loading="loading" />
            </div>
        </template>
    </CardBoxModal>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { useToast } from 'vue-toastification';
import { mdiTranslate } from '@mdi/js'
const toast = useToast()

const props = defineProps({
    modelValue: Boolean,
    product: {
        type: Object,
        required: true
    },
})

const emit = defineEmits(['update:modelValue', 'translations-updated'])
const loading = ref(false)
const selectedLanguage = ref('en')
const translations = ref({})

// Computed property để kiểm soát hiển thị modal
const isModalActive = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const availableLanguages = [
    { code: 'en', name: 'English' },
    { code: 'ja', name: '日本語' },
]

const translatableFields = [
    { key: 'name', label: 'Tên sản phẩm', type: 'text', maxLength: 255 },
    { key: 'brand_description', label: 'Mô tả thương hiệu', type: 'textarea', maxLength: 1000 },
    { key: 'usage', label: 'Cách sử dụng', type: 'textarea', maxLength: 1000 },
    { key: 'benefits', label: 'Lợi ích', type: 'textarea', maxLength: 1000 },
    { key: 'key_ingredients', label: 'Thành phần chính', type: 'textarea', maxLength: 1000 },
    { key: 'ingredients', label: 'Thành phần đầy đủ', type: 'textarea', maxLength: 1000 },
    { key: 'directions', label: 'Hướng dẫn sử dụng', type: 'textarea', maxLength: 1000 },
    { key: 'storage_instructions', label: 'Hướng dẫn bảo quản', type: 'textarea', maxLength: 1000 },
    { key: 'product_notes', label: 'Ghi chú sản phẩm', type: 'textarea', maxLength: 1000 },
]

// Khởi tạo translations ngay khi component được tạo
const initTranslations = () => {
    availableLanguages.forEach(lang => {
        translations.value[lang.code] = {}
        translatableFields.forEach(field => {
            translations.value[lang.code][field.key] = ''
        })
    })
}

// Load translations hiện có của sản phẩm
const loadExistingTranslations = async () => {
    if (!props.product?.id) return

    try {
        const response = await axios.get(`/api/products/${props.product.id}/translations`)
        const existingTranslations = response.data.data

        // Khởi tạo lại translations cho mỗi ngôn ngữ
        availableLanguages.forEach(lang => {
            translations.value[lang.code] = translations.value[lang.code] || {}

            // Khởi tạo các trường có thể dịch với giá trị mặc định
            translatableFields.forEach(field => {
                // Kiểm tra xem có bản dịch cho trường này không
                if (existingTranslations?.[lang.code]?.[field.key]) {
                    translations.value[lang.code][field.key] = existingTranslations[lang.code][field.key]
                } else {
                    translations.value[lang.code][field.key] = ''
                }
            })
        })
    } catch (error) {
        console.error('Error loading translations:', error)
        toast.error('Không thể tải bản dịch')
    }
}

const saveTranslations = async () => {
    loading.value = true
    try {
        await axios.post(`/api/products/${props.product.id}/translations`, {
            translations: translations.value
        })

        toast.success('Đã lưu bản dịch thành công')
        emit('translations-updated')
        isModalActive.value = false
    } catch (error) {
        console.error('Error saving translations:', error)
        toast.error('Không thể lưu bản dịch')
    } finally {
        loading.value = false
    }
}

const translateField = async (fieldKey) => {
    // Implement auto-translate functionality here
    // This is just a placeholder - you'll need to integrate with a translation service
    toast.info('Tính năng dịch tự động sẽ được phát triển sau')
}

onMounted(() => {
    initTranslations()
    if (props.product?.id) {
        loadExistingTranslations()
    }
})

watch(() => props.modelValue, (newVal) => {
    if (newVal && props.product?.id) {
        loadExistingTranslations()
    }
})

// Thêm watcher để theo dõi khi product thay đổi
watch(() => props.product, (newProduct) => {
    if (newProduct?.id) {
        loadExistingTranslations()
    }
}, { deep: true })
</script>

<style scoped>
.form-select,
.form-input,
.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.form-select:focus,
.form-input:focus,
.form-textarea:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dark .form-select,
.dark .form-input,
.dark .form-textarea {
    background-color: #1a1a1a;
    border-color: #4a5568;
    color: #e2e8f0;
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

/* Thêm style cho custom scrollbar */
.custom-scrollbar {
    scrollbar-width: thin;
    /* For Firefox */
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
    /* For Firefox */
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 20px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}

/* Dark mode */
.dark .custom-scrollbar {
    scrollbar-color: rgba(75, 85, 99, 0.5) transparent;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(75, 85, 99, 0.5);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(75, 85, 99, 0.7);
}
</style>