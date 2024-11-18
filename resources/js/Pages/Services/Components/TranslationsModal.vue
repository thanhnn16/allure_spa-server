<template>
    <CardBoxModal v-model="isModalActive" :title="'Quản lý bản dịch'" :has-cancel="false" :has-button="false"
        max-width="2xl">
        <div class="space-y-6 max-h-[70vh] overflow-y-auto px-2 custom-scrollbar">
            <!-- Language Selector with Flags -->
            <div class="flex items-center space-x-4 sticky top-0 bg-white dark:bg-dark-bg py-4 z-10 border-b dark:border-dark-border">
                <label class="w-32 font-medium text-gray-700 dark:text-gray-300">Ngôn ngữ:</label>
                <div class="flex space-x-2">
                    <button v-for="lang in availableLanguages" :key="lang.code" 
                        @click="selectedLanguage = lang.code"
                        class="px-4 py-2 rounded-lg transition-all duration-200 flex items-center space-x-2"
                        :class="[
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
                                <p class="whitespace-pre-line">{{ service[field.key] || 'Chưa có nội dung' }}</p>
                            </div>
                        </div>

                        <!-- Translation Content -->
                        <div class="space-y-2">
                            <label class="font-medium text-gray-700 dark:text-gray-300 flex items-center justify-between">
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
                                <input v-else 
                                    v-model="translations[selectedLanguage][field.key]"
                                    class="w-full form-input rounded-lg dark:bg-dark-bg"
                                    :type="field.type"
                                    :maxlength="field.maxLength"
                                    :placeholder="`Nhập bản dịch cho ${field.label.toLowerCase()}...`">
                                <button v-if="!translations[selectedLanguage][field.key] && service[field.key]"
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
import { ref, onMounted, computed } from 'vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'

const props = defineProps({
    modelValue: Boolean,
    service: {
        type: Object,
        required: true
    },
})

const emit = defineEmits(['update:modelValue', 'translations-updated'])
const loading = ref(false)
const selectedLanguage = ref('en')
const translations = ref({})

const isModalActive = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const availableLanguages = [
    { code: 'en', name: 'English' },
    { code: 'ja', name: '日本語' },
]

const translatableFields = [
    { key: 'service_name', label: 'Tên dịch vụ', type: 'text', maxLength: 255 },
    { key: 'description', label: 'Mô tả', type: 'textarea', maxLength: 1000 },
]

onMounted(async () => {
    // Initialize translations object
    availableLanguages.forEach(lang => {
        translations.value[lang.code] = {}
        translatableFields.forEach(field => {
            translations.value[lang.code][field.key] = ''
        })
    })

    // Load existing translations
    try {
        const response = await fetch(`/api/services/${props.service.id}/translations`)
        const data = await response.json()
        if (data.data) {
            translations.value = { ...translations.value, ...data.data }
        }
    } catch (error) {
        console.error('Error loading translations:', error)
    }
})

const saveTranslations = async () => {
    loading.value = true
    try {
        const response = await fetch(`/api/services/${props.service.id}/translations`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ translations: translations.value }),
        })

        if (!response.ok) throw new Error('Failed to save translations')

        emit('translations-updated')
        isModalActive.value = false
    } catch (error) {
        console.error('Error saving translations:', error)
        // Handle error (show notification, etc.)
    } finally {
        loading.value = false
    }
}
</script>
