<template>
    <Dialog :open="true" @close="$emit('close')" class="relative z-50">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/30" aria-hidden="true" />

        <!-- Modal Container -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <!-- Modal Panel -->
            <DialogPanel class="w-full max-w-3xl rounded bg-white dark:bg-slate-800">
                <!-- Modal Header - Fixed -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <DialogTitle class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ isEditMode ? 'Chỉnh Sửa Cấu Hình' : 'Thêm Cấu Hình Mới' }}
                        </DialogTitle>
                        <button @click="$emit('close')"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            <BaseIcon :path="mdiClose" size="24" />
                        </button>
                    </div>
                </div>

                <!-- Modal Body - Scrollable -->
                <div class="max-h-[calc(100vh-16rem)] overflow-y-auto p-6">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <!-- Basic Fields - 2 columns -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Cột Trái -->
                            <div class="space-y-6">
                                <div v-for="field in basicLeftFields" :key="field.key" class="space-y-2">
                                    <label :for="field.key"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ field.label }}
                                    </label>

                                    <!-- Text Input -->
                                    <input v-if="field.type === 'text'" :id="field.key" v-model="formData[field.key]"
                                        type="text" class="form-input w-full rounded-md" />

                                    <!-- Select Input -->
                                    <select v-else-if="field.type === 'select'" :id="field.key"
                                        v-model="formData[field.key]" class="form-select w-full rounded-md">
                                        <option v-for="option in field.options" :key="option.value"
                                            :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>

                                    <p v-if="field.description" class="mt-1 text-sm text-gray-500">
                                        {{ field.description }}
                                    </p>
                                </div>
                            </div>

                            <!-- Cột Phải -->
                            <div class="space-y-6">
                                <div v-for="field in basicRightFields" :key="field.key" class="space-y-2">
                                    <!-- Number Input -->
                                    <label :for="field.key"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ field.label }}
                                    </label>
                                    <input v-if="field.type === 'number'" :id="field.key"
                                        v-model.number="formData[field.key]" type="number" :min="field.min"
                                        :max="field.max" :step="field.step" class="form-input w-full rounded-md" />

                                    <p v-if="field.description" class="mt-1 text-sm text-gray-500">
                                        {{ field.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- JSON Editor Fields - Full Width -->
                        <div class="space-y-6 mt-8">
                            <div v-for="field in jsonFields" :key="field.key" class="space-y-2">
                                <label :for="field.key"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ field.label }}
                                </label>
                                <JsonEditor v-model="formData[field.key]" :schema="field.schema"
                                    class="border rounded-md" @error="handleJsonError(field.key, $event)" />
                                <p v-if="jsonErrors[field.key]" class="mt-1 text-sm text-red-600">
                                    {{ jsonErrors[field.key] }}
                                </p>
                                <p v-if="field.description" class="mt-1 text-sm text-gray-500">
                                    {{ field.description }}
                                </p>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer - Fixed -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-end space-x-3">
                        <BaseButton type="button" label="Hủy" color="white" @click="$emit('close')" />
                        <BaseButton type="submit" :label="isEditMode ? 'Cập Nhật' : 'Tạo Mới'" color="info"
                            :loading="isSubmitting" @click="handleSubmit" />
                    </div>
                </div>
            </DialogPanel>
        </div>
    </Dialog>
</template>

<style scoped>
/* Custom scrollbar styles */
.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

/* Adjust JsonEditor height for full width fields */
:deep(.json-editor-container) {
    height: 200px;
    min-height: 200px;
    max-height: 200px;
}

:deep(.cm-editor) {
    height: 100%;
}

/* Add divider between basic fields and JSON editors */
.mt-8 {
    position: relative;
}

.mt-8::before {
    content: '';
    position: absolute;
    top: -1rem;
    left: 0;
    right: 0;
    height: 1px;
    background-color: theme('colors.gray.200');
}

/* Dark mode divider */
:deep(.dark .mt-8::before) {
    background-color: theme('colors.gray.700');
}
</style>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import { mdiClose } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import JsonEditor from '@/Components/JsonEditor.vue'
import { useConfigValidation } from '@/Composables/useConfigValidation'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    config: {
        type: Object,
        default: () => null
    },
    fields: {
        type: Array,
        required: true
    },
    configTypes: {
        type: Object,
        required: true
    },
    modelTypes: {
        type: Object,
        required: true
    },
    languages: {
        type: Object,
        required: true
    },
    responseFormats: {
        type: Object,
        required: true
    },
    defaultSafetySettings: {
        type: Array,
        required: true
    },
    defaultFunctionDeclarations: {
        type: Array,
        required: true
    },
    defaultToolConfig: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'submit'])

const { validateConfig } = useConfigValidation()
const isSubmitting = ref(false)
const errors = ref({})

// Tách fields thành 3 nhóm riêng biệt
const basicLeftFields = computed(() =>
    props.fields.filter(field =>
        field.column === 'left' && field.type !== 'json-editor'
    )
);

const basicRightFields = computed(() =>
    props.fields.filter(field =>
        field.column === 'right' && field.type !== 'json-editor'
    )
);

const jsonFields = computed(() =>
    props.fields.filter(field => field.type === 'json-editor')
);

// Thêm computed để kiểm tra mode
const isEditMode = computed(() => {
    return props.config !== null && Object.keys(props.config).length > 0
})

// Thêm defaultFormData để khởi tạo giá trị mặc định
const defaultFormData = {
    ai_name: '',
    type: 'system_prompt',
    language: 'vi',
    model_type: 'gemini-pro',
    temperature: 0.7,
    max_tokens: 2048,
    top_p: 0.9,
    top_k: 40,
    safety_settings: props.defaultSafetySettings,
    function_declarations: props.defaultFunctionDeclarations,
    tool_config: props.defaultToolConfig
}

// Cập nhật khởi tạo formData
const formData = reactive(
    isEditMode.value ? { ...props.config } : { ...defaultFormData }
)

// Thêm method để reset form
const resetForm = () => {
    Object.assign(formData, defaultFormData)
    errors.value = {}
}

// Helper methods
const getFieldComponent = (field) => {
    switch (field.type) {
        case 'text': return 'input'
        case 'number': return 'input'
        case 'select': return 'select'
        case 'textarea': return 'textarea'
        case 'json-editor': return JsonEditor
        default: return 'input'
    }
}

const getFieldProps = (field) => {
    const props = {}

    switch (field.type) {
        case 'text':
        case 'number':
            props.type = field.type
            if (field.min !== undefined) props.min = field.min
            if (field.max !== undefined) props.max = field.max
            if (field.step !== undefined) props.step = field.step
            props.class = 'form-input rounded-md'
            break

        case 'select':
            props.class = 'form-select rounded-md'
            break

        case 'textarea':
            props.rows = field.rows || 4
            props.class = 'form-textarea rounded-md'
            break

        case 'json-editor':
            props.schema = field.schema
            break
    }

    return props
}

const jsonErrors = ref({});

function initializeFormData() {
    if (props.config) {
        // Nếu đang edit, chuyển đổi các trường JSON thành string nếu cần
        const data = { ...props.config };
        ['context', 'function_declarations', 'tool_config'].forEach(key => {
            if (typeof data[key] === 'object') {
                data[key] = JSON.stringify(data[key], null, 2);
            }
        });
        return data;
    }

    // Nếu tạo mới, sử dụng giá trị mặc định
    return {
        ai_name: '',
        type: 'system_prompt',
        language: 'vi',
        model_type: 'gemini-pro',
        temperature: 0.7,
        max_tokens: 2048,
        top_p: 0.9,
        top_k: 40,
        context: '',
        safety_settings: JSON.stringify(props.defaultSafetySettings, null, 2),
        function_declarations: JSON.stringify(props.defaultFunctionDeclarations, null, 2),
        tool_config: JSON.stringify(props.defaultToolConfig, null, 2)
    };
}

const handleJsonError = (field, error) => {
    if (error) {
        jsonErrors.value[field] = error;
    } else {
        delete jsonErrors.value[field];
    }
};

const validateJsonFields = () => {
    const jsonFields = ['context', 'function_declarations', 'tool_config'];
    let isValid = true;

    jsonFields.forEach(field => {
        try {
            if (formData[field]) {
                JSON.parse(formData[field]);
            }
            delete jsonErrors.value[field];
        } catch (e) {
            jsonErrors.value[field] = 'Invalid JSON format';
            isValid = false;
        }
    });

    return isValid;
};

// Cập nhật handleSubmit
const handleSubmit = async () => {
    try {
        isSubmitting.value = true
        errors.value = {}

        // Validate JSON fields
        if (!validateJsonFields()) {
            return;
        }

        // Convert JSON strings to objects for submission
        const submitData = { ...formData };
        ['context', 'function_declarations', 'tool_config'].forEach(key => {
            if (submitData[key]) {
                try {
                    submitData[key] = JSON.parse(submitData[key]);
                } catch (e) {
                    console.error(`Failed to parse ${key}: ${e}`);
                }
            }
        });

        // API endpoint
        const url = isEditMode.value
            ? `/api/ai-configs/${props.config.id}`
            : '/api/ai-configs';

        const response = await axios({
            method: isEditMode.value ? 'put' : 'post',
            url: url,
            data: submitData
        });

        toast.success(isEditMode.value ? 'Cập nhật thành công!' : 'Tạo mới thành công!');
        emit('close');
        router.reload();

    } catch (error) {
        console.error('Submit error:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra');
    } finally {
        isSubmitting.value = false;
    }
}

// Thêm watch để cập nhật form khi props.config thay đổi
watch(() => props.config, (newConfig) => {
    if (newConfig && Object.keys(newConfig).length > 0) {
        Object.assign(formData, newConfig)
    } else {
        resetForm()
    }
}, { immediate: true })
</script>