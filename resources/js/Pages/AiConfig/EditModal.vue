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
                            {{ modalTitle }}
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
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ field.label }}
                                </label>
                                <div :style="{ '--editor-height': field.height }">
                                    <JsonEditor
                                        v-model="formData[field.key]"
                                        :schema="field.schema || {}"
                                        :error="errors[field.key]"
                                        :height="field.height || '300px'"
                                    />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer - Fixed -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-end space-x-3">
                        <BaseButton type="button" label="Hủy" color="white" @click="$emit('close')" />
                        <BaseButton type="submit" :label="submitButtonText" color="info" :loading="isSubmitting"
                            @click="handleSubmit" />
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
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import JsonEditor from '@/Components/JsonEditor.vue'
import { useConfigValidation } from '@/Composables/useConfigValidation'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import { mdiClose } from '@mdi/js'
import { computed, reactive, ref, watch } from 'vue'
import { useToast } from 'vue-toastification'

const toast = useToast()

const props = defineProps({
    config: {
        type: Object,
        default: () => null
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
    }
})

const emit = defineEmits(['close', 'submit'])

const { validateConfig } = useConfigValidation()
const isSubmitting = ref(false)
const errors = ref({})

// Thêm defaultFormData
const defaultFormData = {
    ai_name: '',
    type: 'system_prompt',
    context: JSON.stringify({
        role: "assistant",
        description: "Mô tả về vai trò của AI",
        instructions: "Hãy trả lời một cách chuyên nghiệp và thân thiện",
        custom_instructions: {
            system_instructions: "Bạn là một trợ lý AI thông minh...",
            user_instructions: "Hãy trả lời một cách chuyên nghiệp và thân thiện"
        }
    }, null, 2),
    language: 'vi',
    model_type: 'gemini-1.5-pro',
    temperature: 0.7,
    max_tokens: 2048,
    top_p: 0.9,
    top_k: 40,
    safety_settings: [],
    function_declarations: [],
    tool_config: {}
}

// Thêm fields computed
const fields = computed(() => [
    // Basic fields - left column
    {
        key: 'ai_name',
        label: 'Tên Trợ Lý',
        type: 'text',
        column: 'left',
        required: true
    },
    {
        key: 'type',
        label: 'Loại Cấu Hình',
        type: 'select',
        column: 'left',
        options: Object.entries(props.configTypes).map(([value, config]) => ({
            value,
            label: config.name
        }))
    },
    {
        key: 'language',
        label: 'Ngôn Ngữ',
        type: 'select',
        column: 'left',
        options: Object.entries(props.languages).map(([value, label]) => ({
            value,
            label
        }))
    },
    {
        key: 'model_type',
        label: 'Model AI',
        type: 'select',
        column: 'left',
        options: Object.entries(props.modelTypes).map(([value, label]) => ({
            value,
            label
        }))
    },
    // Basic fields - right column
    {
        key: 'temperature',
        label: 'Temperature',
        type: 'number',
        column: 'right',
        min: 0,
        max: 1,
        step: 0.1
    },
    {
        key: 'max_tokens',
        label: 'Max Tokens',
        type: 'number',
        column: 'right',
        min: 1,
        max: 8192
    },
    {
        key: 'top_p',
        label: 'Top P',
        type: 'number',
        column: 'right',
        min: 0,
        max: 1,
        step: 0.1
    },
    {
        key: 'top_k',
        label: 'Top K',
        type: 'number',
        column: 'right',
        min: 1,
        max: 100
    },
    // JSON fields
    {
        key: 'context',
        label: 'Context',
        type: 'json-editor',
        height: '200px'
    },
    {
        key: 'safety_settings',
        label: 'Safety Settings',
        type: 'json-editor',
        height: '300px'
    },
    {
        key: 'function_declarations',
        label: 'Function Declarations',
        type: 'json-editor',
        height: '400px'
    },
    {
        key: 'tool_config',
        label: 'Tool Configuration',
        type: 'json-editor',
        height: '300px'
    }
])

// Cập nhật computed properties để sử dụng fields
const basicLeftFields = computed(() => 
    fields.value.filter(field => field.column === 'left')
)

const basicRightFields = computed(() => 
    fields.value.filter(field => field.column === 'right')
)

const jsonFields = computed(() => 
    fields.value.filter(field => field.type === 'json-editor')
)

// Thêm computed để kiểm tra mode
const isEditMode = computed(() => {
    return !!props.config?.id;
});

// Add modalTitle computed
const modalTitle = computed(() => {
    return isEditMode.value ? 'Chỉnh Sửa Cấu Hình' : 'Thêm Cấu Hình Mới';
});

// Update formData initialization with default values for new config
const getDefaultFormData = () => ({
    ai_name: '',
    type: 'system_prompt',
    context: JSON.stringify({
        role: "assistant",
        description: "Mô tả về vai trò của AI",
        instructions: "Hãy trả lời một cách chuyên nghiệp và thân thiện",
        custom_instructions: {
            system_instructions: "Bạn là một trợ lý AI thông minh...",
            user_instructions: "Hãy trả lời một cách chuyên nghiệp và thân thiện"
        }
    }, null, 2),
    language: 'vi',
    model_type: 'gemini-1.5-pro',
    temperature: 0.7,
    max_tokens: 2048,
    top_p: 0.9,
    top_k: 40,
    safety_settings: JSON.stringify(props.defaultSafetySettings, null, 2),
    function_declarations: JSON.stringify(props.defaultFunctionDeclarations, null, 2),
    tool_config: JSON.stringify(props.defaultToolConfig, null, 2)
});

// Initialize formData
const formData = reactive(
    isEditMode.value ? formatExistingConfig(props.config) : getDefaultFormData()
);

// Helper function to format existing config
function formatExistingConfig(config) {
    const data = { ...config };

    // Ensure context is string
    if (typeof data.context === 'object') {
        data.context = JSON.stringify(data.context, null, 2);
    }

    // Format JSON fields
    ['safety_settings', 'function_declarations', 'tool_config'].forEach(field => {
        if (data[field]) {
            data[field] = typeof data[field] === 'string'
                ? data[field]
                : JSON.stringify(data[field], null, 2);
        }
    });

    return data;
}

// Watch for config changes
watch(() => props.config, (newConfig) => {
    if (newConfig?.id) {
        // Edit mode - use existing config
        Object.assign(formData, formatExistingConfig(newConfig));
    } else {
        // Create mode - reset to defaults
        Object.assign(formData, getDefaultFormData());
    }
}, { immediate: true });

// Update submit button text
const submitButtonText = computed(() => {
    return isEditMode.value ? 'Cập Nhật' : 'Tạo Mới';
});

// Thêm method để reset form
const resetForm = () => {
    Object.assign(formData, getDefaultFormData())
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

// Thêm hàm kiểm tra JSON string
const isJsonString = (str) => {
    try {
        JSON.parse(str);
        return true;
    } catch (e) {
        return false;
    }
};

// Sửa lại hàm initializeFormData
function initializeFormData() {
    if (props.config) {
        const data = { ...props.config };

        // Xử lý các trường JSON
        const jsonFields = ['context', 'safety_settings', 'function_declarations', 'tool_config'];
        jsonFields.forEach(key => {
            try {
                if (data[key]) {
                    if (typeof data[key] === 'string' && !isJsonString(data[key]) && key === 'context') {
                        // Nếu context là text thường, wrap vào object
                        data[key] = JSON.stringify({
                            role: "assistant",
                            description: "AI Assistant",
                            instructions: data[key],
                            custom_instructions: {
                                system_instructions: data[key],
                                user_instructions: ""
                            }
                        }, null, 2);
                    } else if (typeof data[key] === 'object') {
                        data[key] = JSON.stringify(data[key], null, 2);
                    }
                }
            } catch (e) {
                console.error(`Error initializing ${key}:`, e);
                // Set default value if error
                data[key] = JSON.stringify(defaultFormData[key], null, 2);
            }
        });
        return data;
    }
    return { ...defaultFormData };
}

const handleJsonError = (field, error) => {
    if (error) {
        jsonErrors.value[field] = error;
    } else {
        delete jsonErrors.value[field];
    }
};

// Sửa lại hàm validateJsonFields
const validateJsonFields = () => {
    const jsonFields = ['safety_settings', 'function_declarations', 'tool_config'];
    let isValid = true;

    jsonFields.forEach(field => {
        try {
            if (formData[field]) {
                let value = formData[field];
                if (typeof value === 'string') {
                    JSON.parse(value);
                }
                delete jsonErrors.value[field];
            }
        } catch (e) {
            console.error(`Error validating ${field}:`, e);
            jsonErrors.value[field] = `Invalid JSON format in ${field}`;
            isValid = false;
        }
    });

    return isValid;
};

// Thêm hàm validateForm
const validateForm = () => {
    const { isValid, errors: validationErrors } = validateConfig(formData);

    // Kiểm tra lỗi JSON
    const isJsonValid = validateJsonFields();

    if (!isValid || !isJsonValid) {
        errors.value = validationErrors;
        return false;
    }

    errors.value = {};
    return true;
};

// Sửa lại hàm handleSubmit
const handleSubmit = async () => {
    try {
        if (!validateForm()) return;

        const submitData = { ...formData };

        // Ensure context is valid JSON string if it's an object
        if (typeof submitData.context === 'object') {
            submitData.context = JSON.stringify(submitData.context);
        }

        // Parse JSON fields before submitting
        ['safety_settings', 'function_declarations', 'tool_config'].forEach(field => {
            if (submitData[field]) {
                try {
                    submitData[field] = JSON.parse(submitData[field]);
                } catch (e) {
                    throw new Error(`Invalid JSON in ${field}`);
                }
            }
        });

        isSubmitting.value = true;

        await emit('submit', submitData);
        emit('close');

    } catch (error) {
        toast.error(error.message || 'Có lỗi xảy ra');
    } finally {
        isSubmitting.value = false;
    }
};

// Thêm watch để theo dõi các trường JSON
watch(formData, (newData) => {
    ['safety_settings', 'function_declarations', 'tool_config'].forEach(field => {
        if (newData[field]) {
            try {
                if (typeof newData[field] === 'string') {
                    JSON.parse(newData[field]);
                    delete jsonErrors.value[field];
                }
            } catch (e) {
                jsonErrors.value[field] = `Invalid JSON format in ${field}`;
            }
        }
    });
}, { deep: true });
</script>