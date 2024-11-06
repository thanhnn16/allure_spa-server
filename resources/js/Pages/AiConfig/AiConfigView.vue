<script setup>
import { ref, reactive, watch, computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot } from '@headlessui/vue'
import {
    mdiRobot,
    mdiCog,
    mdiImageSearch,
    mdiClose,
    mdiDelete,
    mdiAlert,
    mdiPencil,
    mdiUpload,
    mdiPlus,
    mdiFile
} from '@mdi/js'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { Head } from '@inertiajs/vue3'
import NotificationBar from '@/Components/NotificationBar.vue'
import axios from 'axios'

const props = defineProps({
    configs: {
        type: Array,
        required: true
    },
    configTypes: {
        type: Object,
        required: true
    }
});

// Add reactive configs state
const configsList = ref(props.configs);

// Tabs configuration
const tabs = [
    { id: 'system', label: 'Prompt Hệ Thống', icon: mdiRobot },
    { id: 'vision', label: 'Phân Tích Hình Ảnh', icon: mdiImageSearch },
    { id: 'general', label: 'Cài Đặt Chung', icon: mdiCog }
]

const activeTab = ref('system')
const showEditModal = ref(false)
const showUploadModal = ref(false)
const notification = ref(null)
const editingConfig = ref(null)

const configForm = reactive({
    ai_name: '',
    type: 'system_prompt',
    context: '',
    language: 'vi',
    gemini_settings: null
});

// File upload state
const uploadForm = reactive({
    file: null,
    type: 'system_prompt'
})

// Thêm reactive state cho drag & drop
const dragActive = ref(false)
const filePreview = ref('')

// Thêm methods xử lý drag & drop
const handleDrag = (e) => {
    e.preventDefault()
    e.stopPropagation()
    if (e.type === "dragenter" || e.type === "dragover") {
        dragActive.value = true
    } else if (e.type === "dragleave") {
        dragActive.value = false
    }
}

const handleDrop = (e) => {
    e.preventDefault()
    e.stopPropagation()
    dragActive.value = false

    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        handleFiles(e.dataTransfer.files[0])
    }
}

const handleFiles = (file) => {
    uploadForm.file = file

    // Preview cho file text
    if (file.type === "text/plain" || file.type === "application/json") {
        const reader = new FileReader()
        reader.onload = (e) => {
            filePreview.value = e.target.result
        }
        reader.readAsText(file)
    }
}

// Thêm default values cho các loại config
const defaultValues = {
    system_prompt: {
        ai_name: 'System Assistant',
        type: 'system_prompt',
        context: 'Bạn là một trợ lý AI thông minh, nhiệm vụ của bạn là...',
        language: 'vi',
        model_type: 'gemini-1.5-pro',
        temperature: 0.9,
        max_tokens: 2048,
        top_p: 1,
        top_k: 40,
        priority: 0,
        is_active: true,
        api_key: '',
    },
    vision_config: {
        ai_name: 'Vision Assistant',
        type: 'vision_config',
        context: 'Hãy phân tích chi tiết hình ảnh này...',
        language: 'vi',
        model_type: 'gemini-vision-pro',
        temperature: 0.7,
        max_tokens: 1024,
        top_p: 0.9,
        top_k: 40,
        priority: 0,
        is_active: true,
        gemini_settings: {
            vision_parameters: {
                max_output_tokens: 1024,
                temperature: 0.7
            }
        },
        api_key: '',
    },
    general: {
        ai_name: 'General Assistant',
        type: 'general',
        context: 'Cấu hình chung cho model AI...',
        language: 'vi',
        model_type: 'gemini-1.5-pro',
        temperature: 0.8,
        max_tokens: 2048,
        top_p: 1,
        top_k: 40,
        priority: 0,
        is_active: true,
        api_key: '',
    }
};

// Cập nhật configFields để thêm thông tin layout
const configFields = computed(() => {
    const baseFields = [
        // Cột trái - Thông tin cơ bản
        {
            column: 'left',
            fields: [
                {
                    key: 'ai_name',
                    label: 'Tên Cấu Hình',
                    type: 'text',
                    required: true,
                    placeholder: 'Nhập tên cấu hình...',
                    description: 'Tên định danh cho cấu hình AI'
                },
                {
                    key: 'type',
                    label: 'Loại Cấu Hình',
                    type: 'select',
                    required: true,
                    options: [
                        { value: 'system_prompt', label: 'Prompt Hệ Thống' },
                        { value: 'vision_config', label: 'Phân Tích Hình Ảnh' },
                        { value: 'general', label: 'Cài Đặt Chung' }
                    ],
                    description: 'Xác định loại và mục đích của cấu hình'
                },
                {
                    key: 'language',
                    label: 'Ngôn Ngữ',
                    type: 'select',
                    required: true,
                    options: [
                        { value: 'vi', label: 'Tiếng Việt' },
                        { value: 'en', label: 'Tiếng Anh' },
                        { value: 'ja', label: 'Tiếng Nhật' }
                    ]
                },
                {
                    key: 'model_type',
                    label: 'Model AI',
                    type: 'select',
                    required: true,
                    options: [
                        { value: 'gemini-1.5-pro', label: 'Gemini 1.5 Pro' },
                        { value: 'gemini-1.0-pro', label: 'Gemini 1.0 Pro' },
                        { value: 'gemini-vision-pro', label: 'Gemini Vision Pro' }
                    ]
                },
                {
                    key: 'api_key',
                    label: 'API Key',
                    type: 'password',
                    required: false,
                    placeholder: 'Nhập API key...',
                    description: 'API key để xác thực với service (để trống nếu dùng key mặc định)',
                    showValue: true
                },
            ]
        },
        // Cột phải - Cài đặt nâng cao
        {
            column: 'right',
            fields: [
                {
                    key: 'temperature',
                    label: 'Temperature',
                    type: 'number',
                    min: 0,
                    max: 1,
                    step: 0.1,
                    description: 'Độ sáng tạo của câu trả lời (0-1)'
                },
                {
                    key: 'max_tokens',
                    label: 'Max Tokens',
                    type: 'number',
                    min: 1,
                    max: 8192,
                    description: 'Độ dài tối đa của câu trả lời'
                },
                {
                    key: 'top_p',
                    label: 'Top P',
                    type: 'number',
                    min: 0,
                    max: 1,
                    step: 0.1,
                    description: 'Nucleus sampling probability'
                },
                {
                    key: 'top_k',
                    label: 'Top K',
                    type: 'number',
                    min: 1,
                    max: 100,
                    description: 'Số lượng token có xác suất cao nhất được chọn'
                },
                {
                    key: 'priority',
                    label: 'Độ Ưu Tiên',
                    type: 'number',
                    min: 0,
                    step: 1,
                    description: 'Thứ tự ưu tiên khi có nhiều cấu hình cùng loại'
                },
                {
                    key: 'is_active',
                    label: 'Kích Hoạt',
                    type: 'checkbox',
                    description: 'Trạng thái hoạt động của cấu hình'
                }
            ]
        }
    ];

    // Context field luôn ở dưới cùng, full width
    const contextField = {
        column: 'full',
        fields: [
            {
                key: 'context',
                label: 'Nội Dung',
                type: 'textarea',
                required: true,
                rows: 5,
                placeholder: 'Nhập nội dung cấu hình...',
                description: 'Nội dung chi tiết của cấu hình'
            }
        ]
    };

    return [...baseFields, contextField];
});

// Thêm reactive state để lưu trữ giá trị các trường
const fieldValues = reactive({});

// Watch cho configForm.type để reset fieldValues khi đổi loại
watch(() => configForm.type, (newType) => {
    if (!editingConfig.value) {
        // Reset về giá trị mặc định
        fieldValues.value = {};
        Object.assign(fieldValues, configTemplates[newType]);
    }
});

const openEditModal = (config = null) => {
    if (config) {
        editingConfig.value = config;
        const configCopy = { ...config };
        if (config.api_key) {
            configCopy.api_key = config.api_key;
        }
        Object.assign(fieldValues, configCopy);
    } else {
        editingConfig.value = null;
        const defaultConfig = defaultValues[configForm.type || 'system_prompt'];
        Object.assign(fieldValues, defaultConfig);
    }
    showEditModal.value = true;
};

const openUploadModal = () => {
    showUploadModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    resetForm()
}

const closeUploadModal = () => {
    showUploadModal.value = false
    uploadForm.file = null
    uploadForm.type = 'system_prompt'
}

const resetForm = () => {
    editingConfig.value = null;
    Object.assign(configForm, {
        ai_name: '',
        type: 'system_prompt',
        context: '',
        language: 'vi',
        gemini_settings: null
    });
    Object.assign(fieldValues, defaultValues['system_prompt']);
};

// Cập nhật hàm submitConfig
const submitConfig = async () => {
    try {
        // Create payload from fieldValues
        const payload = {
            ai_name: fieldValues.ai_name,
            type: fieldValues.type,
            context: fieldValues.context,
            api_key: fieldValues.api_key,
            language: fieldValues.language,
            model_type: fieldValues.model_type,
            temperature: parseFloat(fieldValues.temperature),
            max_tokens: parseInt(fieldValues.max_tokens),
            top_p: parseFloat(fieldValues.top_p),
            top_k: parseInt(fieldValues.top_k),
            priority: parseInt(fieldValues.priority),
            is_active: fieldValues.is_active || false,
            gemini_settings: fieldValues.gemini_settings || null
        };

        let response;
        if (editingConfig.value) {
            response = await axios.put(`/api/ai-config/${editingConfig.value.id}`, payload);
        } else {
            response = await axios.post('/api/ai-config', payload);
        }

        // Update local configs list with new data
        configsList.value = response.data.configs;

        notification.value = {
            type: 'success',
            message: response.data.message
        };
        closeEditModal();
    } catch (error) {
        console.error('Submit error:', error);
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'An error occurred'
        };
    }
};

// Thêm validation cho form
const validateForm = () => {
    if (!configForm.ai_name?.trim()) {
        notification.value = {
            type: 'danger',
            message: 'Name is required'
        };
        return false;
    }

    // Validate required fields based on type
    const requiredFields = configFields.value.filter(field => !field.optional);
    for (const field of requiredFields) {
        if (!fieldValues[field.key]) {
            notification.value = {
                type: 'danger',
                message: `${field.label} is required`
            };
            return false;
        }
    }

    return true;
};

const handleFileUpload = async () => {
    if (!uploadForm.file) return

    const formData = new FormData()
    formData.append('file', uploadForm.file)
    formData.append('type', uploadForm.type)

    try {
        const response = await axios.post('/api/ai-config/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        notification.value = {
            type: 'success',
            message: response.data.message
        }
        closeUploadModal()
        // Refresh configs
        const newConfigs = await axios.get('/api/ai-config')
        props.configs = newConfigs.data.data
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Upload failed'
        }
    }
}

const deleteConfig = async (configId) => {
    if (!confirm('Are you sure you want to delete this configuration?')) return;

    try {
        const response = await axios.delete(`/api/ai-config/${configId}`);

        // Update local configs list
        configsList.value = response.data.configs;

        notification.value = {
            type: 'success',
            message: response.data.message
        };
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Delete failed'
        };
    }
};

const getConfigsByType = (type) => {
    const typeMapping = {
        'system': 'system_prompt',
        'vision': 'vision_config',
        'general': 'general'
    };
    
    return configsList.value?.filter(config => 
        config.type === typeMapping[type]
    ) || [];
};

const formatConfigValue = (value) => {
    try {
        const parsed = JSON.parse(value)
        return JSON.stringify(parsed, null, 2)
    } catch {
        return value
    }
}

const configTemplates = {
    system_prompt: {
        ai_name: '',
        type: 'system_prompt',
        context: 'Bạn là một trợ lý AI thông minh...',
        language: 'vi',
        model_type: 'gemini-1.5-pro',
        temperature: 0.9,
        max_tokens: 2048,
        top_p: 1,
        top_k: 40,
        priority: 0,
        is_active: true
    },
    vision_config: {
        ai_name: '',
        type: 'vision_config',
        context: 'Hãy phân tích chi tiết hình ảnh...',
        language: 'vi',
        model_type: 'gemini-vision-pro',
        temperature: 0.7,
        max_tokens: 1024,
        top_p: 1,
        top_k: 40,
        priority: 0,
        is_active: true,
        gemini_settings: {
            vision_parameters: {
                max_output_tokens: 1024,
                temperature: 0.7
            }
        }
    },
    general: {
        ai_name: '',
        type: 'general',
        context: 'Cấu hình chung cho model AI',
        language: 'vi',
        model_type: 'gemini-1.5-pro',
        temperature: 0.9,
        max_tokens: 2048,
        top_p: 1,
        top_k: 40,
        priority: 0,
        is_active: true
    }
};

const validateConfigValue = (value) => {
    try {
        JSON.parse(value);
        return true;
    } catch (e) {
        return false;
    }
};

const handleSubmit = () => {
    if (!validateConfigValue(configForm.value)) {
        notification.value = {
            type: 'error',
            message: 'Invalid JSON format'
        };
        return;
    }
    // Submit form...
};

// Watch for type changes to update default values
watch(() => configForm.type, (newType) => {
    if (!editingConfig.value) {
        const defaultConfig = defaultValues[newType];
        Object.assign(fieldValues, defaultConfig);
    }
});
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Cấu Hình AI" />
        <SectionMain>
            <!-- Header Section -->
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <BaseIcon :path="mdiRobot" class="w-8 h-8 text-blue-500" />
                        <div>
                            <h1 class="text-2xl font-bold dark:text-white">Cấu Hình AI</h1>
                            <p class="text-gray-600 dark:text-gray-400">Quản lý cài đặt và prompt hệ thống AI</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <BaseButton label="Tải Lên" color="success" @click="openUploadModal" :icon="mdiUpload" />
                        <BaseButton label="Thêm Mới" color="info" @click="openEditModal()" :icon="mdiPlus" />
                    </div>
                </div>
            </div>

            <!-- Notification -->
            <NotificationBar v-if="notification" :color="notification.type" :icon="mdiAlert">
                {{ notification.message }}
            </NotificationBar>

            <!-- Tabs -->
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-4 mb-6">
                <div class="flex space-x-2">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        class="px-4 py-2 rounded-md transition-all duration-200" :class="[activeTab === tab.id
                            ? 'bg-blue-500 text-white shadow-md'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                        ]">
                        <div class="flex items-center space-x-2">
                            <BaseIcon :path="tab.icon" class="w-5 h-5" />
                            <span>{{ tab.label }}</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Content for each tab -->
            <div class="grid gap-6">
                <!-- System Prompts -->
                <div v-if="activeTab === 'system'" class="grid gap-6">
                    <CardBox v-for="config in getConfigsByType('system')" :key="config.id"
                        class="!p-6 dark:bg-slate-900">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold dark:text-white">{{ config.ai_name }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ config.context }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <BaseButton color="info" :icon="mdiPencil" small @click="openEditModal(config)" />
                                <BaseButton color="danger" :icon="mdiDelete" small @click="deleteConfig(config.id)" />
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Model: {{ config.model_type }}</p>
                                <p class="text-gray-600 dark:text-gray-400">Language: {{ config.language }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Temperature: {{ config.temperature }}</p>
                                <p class="text-gray-600 dark:text-gray-400">Max Tokens: {{ config.max_tokens }}</p>
                            </div>
                        </div>
                    </CardBox>
                </div>

                <div v-if="activeTab === 'vision'" class="grid gap-6">
                    <!-- Vision analysis configurations -->
                </div>

                <div v-if="activeTab === 'general'" class="grid gap-6">
                    <!-- General settings -->
                </div>
            </div>

            <!-- Edit Modal -->
            <TransitionRoot appear :show="showEditModal" as="template">
                <Dialog as="div" @close="closeEditModal" class="relative z-50">
                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-2xl 
                                bg-white dark:bg-slate-900 p-6 shadow-xl transition-all">
                                <DialogTitle as="h3"
                                    class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                                    {{ editingConfig ? 'Chỉnh Sửa Cấu Hình' : 'Thêm Cấu Hình Mới' }}
                                </DialogTitle>

                                <form @submit.prevent="submitConfig" class="space-y-6">
                                    <!-- Two-column layout -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Left Column -->
                                        <div class="space-y-4">
                                            <template v-for="group in configFields" :key="group.column">
                                                <template v-if="group.column === 'left'">
                                                    <div v-for="field in group.fields" :key="field.key"
                                                        class="space-y-2">
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            {{ field.label }}
                                                            <span v-if="field.required" class="text-red-500">*</span>
                                                        </label>

                                                        <!-- Xử lý đặc biệt cho trường api_key -->
                                                        <div v-if="field.key === 'api_key'">
                                                            <input
                                                                v-model="fieldValues[field.key]"
                                                                :type="field.type"
                                                                :placeholder="field.placeholder"
                                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                                                autocomplete="new-password"
                                                            >
                                                            <p v-if="field.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                                {{ field.description }}
                                                            </p>
                                                        </div>
                                                        <!-- Other fields -->
                                                        <div v-else>
                                                            <!-- Select Input -->
                                                            <select v-if="field.type === 'select'"
                                                                v-model="fieldValues[field.key]"
                                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                                  dark:bg-slate-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                                                                <option v-for="option in field.options" :key="option.value"
                                                                    :value="option.value">
                                                                    {{ option.label }}
                                                                </option>
                                                            </select>

                                                            <!-- Text Input -->
                                                            <input v-else-if="field.type === 'text'"
                                                                v-model="fieldValues[field.key]" :type="field.type"
                                                                :placeholder="field.placeholder"
                                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                                  dark:bg-slate-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">

                                                            <p v-if="field.description"
                                                                class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                                {{ field.description }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>

                                        <!-- Right Column -->
                                        <div class="space-y-4">
                                            <template v-for="group in configFields" :key="group.column">
                                                <template v-if="group.column === 'right'">
                                                    <div v-for="field in group.fields" :key="field.key"
                                                        class="space-y-2">
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            {{ field.label }}
                                                            <span v-if="field.required" class="text-red-500">*</span>
                                                        </label>

                                                        <!-- Number Input -->
                                                        <input v-if="field.type === 'number'"
                                                            v-model.number="fieldValues[field.key]" type="number"
                                                            :min="field.min" :max="field.max" :step="field.step"
                                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                              dark:bg-slate-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">

                                                        <!-- Checkbox Input -->
                                                        <div v-else-if="field.type === 'checkbox'"
                                                            class="flex items-center">
                                                            <input v-model="fieldValues[field.key]" type="checkbox"
                                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                                                {{ field.description }}
                                                            </span>
                                                        </div>

                                                        <p v-if="field.description && field.type !== 'checkbox'"
                                                            class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                            {{ field.description }}
                                                        </p>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Full Width Context Field -->
                                    <template v-for="group in configFields" :key="group.column">
                                        <template v-if="group.column === 'full'">
                                            <div v-for="field in group.fields" :key="field.key" class="space-y-2">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ field.label }}
                                                    <span v-if="field.required" class="text-red-500">*</span>
                                                </label>

                                                <textarea v-model="fieldValues[field.key]" :rows="field.rows"
                                                    :placeholder="field.placeholder"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                      dark:bg-slate-800 dark:text-white focus:border-blue-500 focus:ring-blue-500"></textarea>

                                                <p v-if="field.description"
                                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ field.description }}
                                                </p>
                                            </div>
                                        </template>
                                    </template>

                                    <!-- Form Actions -->
                                    <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                                        <BaseButton type="button" label="Hủy" color="white" @click="closeEditModal" />
                                        <BaseButton type="submit" :label="editingConfig ? 'Cập Nhật' : 'Tạo Mới'"
                                            color="info" />
                                    </div>
                                </form>
                            </DialogPanel>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>

            <!-- Upload Modal -->
            <TransitionRoot appear :show="showUploadModal" as="template">
                <Dialog as="div" @close="closeUploadModal" class="relative z-50">
                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl 
                                bg-white dark:bg-slate-900 p-6 shadow-xl transition-all">
                                <DialogTitle as="h3"
                                    class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                                    Tải Lên File Cấu Hình
                                </DialogTitle>

                                <form @submit.prevent="handleFileUpload" class="space-y-4">
                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm mb-2 font-medium text-gray-700 dark:text-gray-300">
                                                Loại Cấu Hình
                                            </label>
                                            <select v-model="uploadForm.type"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                                                <option v-for="(label, value) in configTypes" :key="value"
                                                    :value="value">
                                                    {{ label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                File Cấu Hình
                                            </label>
                                            <div @dragenter="handleDrag" @dragover="handleDrag" @dragleave="handleDrag"
                                                @drop="handleDrop"
                                                class="relative border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-all"
                                                :class="[
                                                    dragActive ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600',
                                                    uploadForm.file ? 'bg-green-50 dark:bg-green-900/20' : 'hover:bg-gray-50 dark:hover:bg-slate-800'
                                                ]">
                                                <input type="file" @change="e => handleFiles(e.target.files[0])"
                                                    accept=".json,.txt"
                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                                                <div v-if="!uploadForm.file">
                                                    <BaseIcon :path="mdiUpload" class="w-8 h-8 mx-auto text-gray-400" />
                                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                        Kéo và thả file vào đây hoặc click để chọn
                                                    </p>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                                        File JSON hoặc TXT, dung lượng tối đa 2MB
                                                    </p>
                                                </div>

                                                <div v-else class="space-y-2">
                                                    <BaseIcon :path="mdiFile" class="w-8 h-8 mx-auto text-green-500" />
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ uploadForm.file.name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ (uploadForm.file.size / 1024).toFixed(1) }} KB
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- File Preview -->
                                            <div v-if="filePreview" class="mt-4">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Xem trước file
                                                </label>
                                                <pre
                                                    class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg text-sm overflow-auto max-h-40">
                                        {{ filePreview }}
                                    </pre>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                                        <BaseButton type="button" label="Hủy" color="white" @click="closeUploadModal" />
                                        <BaseButton type="submit" label="Tải Lên" color="success" />
                                    </div>
                                </form>
                            </DialogPanel>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>
        </SectionMain>
    </LayoutAuthenticated>
</template>
