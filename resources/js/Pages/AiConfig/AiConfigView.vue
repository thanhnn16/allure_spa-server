<script setup>
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import CardBox from '@/Components/CardBox.vue'
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { Head, router } from '@inertiajs/vue3'
import {
    mdiCog,
    mdiDelete,
    mdiEye,
    mdiImageSearch,
    mdiPencil,
    mdiPlus,
    mdiRobot,
    mdiRobotExcited,
    mdiUpload,
    mdiKey,
    mdiCogBox
} from '@mdi/js'
import axios from 'axios'
import { computed, reactive, ref, watch } from 'vue'
import { useToast } from 'vue-toastification'

// Thêm imports cho Dialog components
import { Dialog, DialogPanel, DialogTitle, TransitionRoot } from '@headlessui/vue'

const toast = useToast()

// Add missing components
import DeleteModal from './DeleteModal.vue'
import EditModal from './EditModal.vue'
import PreviewModal from './PreviewModal.vue'
import UploadModal from './UploadModal.vue'

// Thêm định nghĩa tabs trước khi sử dụng
const tabs = [
    {
        id: 'general_assistant',
        label: 'Trợ Lý Tổng Hợp',
        icon: mdiRobotExcited,
        description: 'AI có khả năng xử lý cả text và hình ảnh'
    }
];

const props = defineProps({
    configs: {
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
    },
    configTemplates: {
        type: Object,
        required: true
    }
});

// Add reactive configs state
const configsList = ref(props.configs);
const activeConfig = ref(null);
const isLoading = ref(false);
const showDeleteModal = ref(false);
const showPreviewModal = ref(false);
const showEditModal = ref(false);
const showUploadModal = ref(false);
const notification = ref(null)
const editingConfig = ref(null)

// Thêm activeTab ref
const activeTab = ref('system');

// Config form với giá trị mặc định từ props.configTemplates
const configForm = reactive({
    ...props.configTemplates.system_prompt // Default template
});

// Computed properties cho form fields
const configFields = computed(() => [
    // Basic Information
    {
        key: 'ai_name',
        label: 'Tên Trợ Lý',
        type: 'text',
        group: 'basic',
        required: true,
        description: 'Tên định danh cho trợ lý AI'
    },
    {
        key: 'type',
        label: 'Loại Cấu Hình',
        type: 'select',
        group: 'basic',
        options: [
            { value: 'general_assistant', label: 'Trợ Lý Tổng Hợp' }
        ],
        description: 'Loại cấu hình AI'
    },
    {
        key: 'language',
        label: 'Ngôn Ngữ Mặc Định',
        type: 'select',
        group: 'basic',
        options: Object.entries(props.languages).map(([value, label]) => ({
            value,
            label
        })),
        description: 'Ngôn ngữ chính của AI'
    },
    {
        key: 'model_type',
        label: 'Model AI',
        type: 'select',
        group: 'basic',
        options: Object.entries(props.modelTypes).map(([value, label]) => ({
            value,
            label
        })),
        description: 'Model AI được sử dụng'
    },
    // AI Behavior
    {
        key: 'temperature',
        label: 'Temperature',
        type: 'slider',
        group: 'behavior',
        min: 0,
        max: 1,
        step: 0.1,
        description: 'Độ sáng tạo của câu trả lời (0-1)'
    },
    {
        key: 'max_tokens',
        label: 'Max Tokens',
        type: 'number',
        group: 'behavior',
        min: 1,
        max: 8192,
        description: 'Số tokens tối đa cho mỗi câu trả lời'
    },
    {
        key: 'top_p',
        label: 'Top P',
        type: 'slider',
        group: 'behavior',
        min: 0,
        max: 1,
        step: 0.1,
        description: 'Nucleus sampling probability'
    },
    {
        key: 'top_k',
        label: 'Top K',
        type: 'number',
        group: 'behavior',
        min: 1,
        max: 100,
        description: 'Top K sampling'
    },
    // Advanced Settings
    {
        key: 'context',
        label: 'Context & Instructions',
        type: 'markdown-editor',
        group: 'advanced',
        description: 'Hướng dẫn và ngữ cảnh cho AI',
        fullWidth: true
    },
    {
        key: 'function_declarations',
        label: 'Function Declarations',
        type: 'json-editor',
        group: 'advanced',
        description: 'Cấu hình các hàm có thể gọi',
        defaultValue: JSON.stringify(props.defaultFunctionDeclarations, null, 2)
    },
    {
        key: 'safety_settings',
        label: 'Safety Settings',
        type: 'json-editor',
        group: 'advanced',
        description: 'Cấu hình an toàn',
        defaultValue: JSON.stringify(props.defaultSafetySettings, null, 2)
    },
    {
        key: 'tool_config',
        label: 'Tool Configuration',
        type: 'json-editor',
        group: 'advanced',
        description: 'Cấu hình công cụ',
        defaultValue: JSON.stringify(props.defaultToolConfig, null, 2)
    }
]);

// Thêm computed cho các field groups
const fieldGroups = computed(() => ({
    basic: {
        label: 'Thông Tin Cơ Bản',
        icon: mdiCog,
        fields: configFields.value.filter(f => f.group === 'basic')
    },
    behavior: {
        label: 'Hành Vi AI',
        icon: mdiRobot,
        fields: configFields.value.filter(f => f.group === 'behavior')
    },
    advanced: {
        label: 'Cài Đặt Nâng Cao',
        icon: mdiCogBox,
        fields: configFields.value.filter(f => f.group === 'advanced')
    }
}));

// Watch for type changes to update template
watch(() => configForm.type, (newType) => {
    if (props.configTemplates[newType]) {
        Object.assign(configForm, props.configTemplates[newType]);
    }
});

// Thêm reactive state để lưu trữ giá trị các trường
const fieldValues = reactive({});

// Watch cho configForm.type để reset fieldValues khi đổi loại
watch(() => configForm.type, (newType) => {
    if (!editingConfig.value) {
        // Reset về giá trị mặc định
        fieldValues.value = {};
        Object.assign(fieldValues, defaultValues[newType]);
    }
});

// Thêm các props thiếu cho các modal
const modalProps = computed(() => ({
    configTypes: props.configTypes,
    modelTypes: props.modelTypes,
    languages: props.languages,
    responseFormats: props.responseFormats,
    defaultSafetySettings: props.defaultSafetySettings,
    defaultFunctionDeclarations: props.defaultFunctionDeclarations,
    defaultToolConfig: props.defaultToolConfig
}));

// Cập nhật hàm openEditModal
const openEditModal = (config = null) => {
    editingConfig.value = config;
    showEditModal.value = true;
};

// Cập nhật hàm openPreviewModal
const openPreviewModal = (config) => {
    activeConfig.value = config;
    showPreviewModal.value = true;
};

const openUploadModal = () => {
    showUploadModal.value = true;
};

const closeEditModal = () => {
    editingConfig.value = null;
    showEditModal.value = false;
};

const closeUploadModal = () => {
    showUploadModal.value = false;
};

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
const submitConfig = async (configData) => {
    try {
        isLoading.value = true;
        const url = configData.id ?
            `/api/ai-configs/${configData.id}` :
            '/api/ai-configs';
        const method = configData.id ? 'put' : 'post';

        const response = await axios[method](url, configData);

        if (response.data.configs) {
            configsList.value = response.data.configs;
            toast.success(configData.id ? 'Cập nhật thành công!' : 'Tạo mới thành công!');
            closeEditModal();
            router.reload();
        }
    } catch (error) {
        console.error('Submit error:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra');
    } finally {
        isLoading.value = false;
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

const handleFileUpload = async (formData) => {
    try {
        isLoading.value = true;
        const response = await axios.post('/api/ai-configs/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.configs) {
            configsList.value = response.data.configs;
            toast.success('Tải lên thành công!');
            router.reload();
        }

        closeUploadModal();
    } catch (error) {
        console.error('Upload error:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi tải lên');
    } finally {
        isLoading.value = false;
    }
};

const deleteConfig = async () => {
    if (!activeConfig.value?.id) return;

    try {
        isLoading.value = true;
        const response = await axios.delete(`/api/ai-configs/${activeConfig.value.id}`);

        if (response.data.configs) {
            configsList.value = response.data.configs;
            toast.success('Xóa cấu hình thành công!');
            showDeleteModal.value = false;
            router.reload();
        }
    } catch (error) {
        console.error('Delete error:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi xóa');
    } finally {
        isLoading.value = false;
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

// Thêm computed cho filtered configs
const filteredConfigs = computed(() => {
    const typeMapping = {
        'system': 'system_prompt',
        'vision': 'vision_config',
        'general': 'general'
    };
    return configsList.value?.filter(config =>
        config.type === typeMapping[activeTab.value]
    ) || [];
});

// Add missing methods
const getTabClasses = (tabId) => {
    return {
        'bg-blue-500 text-white': activeTab.value === tabId,
        'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800': activeTab.value !== tabId
    };
};

const getCounterClasses = (tabId) => {
    return {
        'bg-blue-200 text-blue-800': activeTab.value === tabId,
        'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300': activeTab.value !== tabId
    };
};

const getConfigCount = (tabId) => {
    const typeMapping = {
        'system': 'system_prompt',
        'vision': 'vision_config',
        'general': 'general'
    };
    return configsList.value.filter(config => config.type === typeMapping[tabId]).length;
};

const confirmDelete = (config) => {
    activeConfig.value = config
    showDeleteModal.value = true
}

// Thêm watch cho configsList
watch(() => props.configs, (newConfigs) => {
    configsList.value = newConfigs;
}, { immediate: true });

// Thêm ref cho API key modal
const showApiKeyModal = ref(false)
const globalApiKey = ref('')

// Thêm methods
const openApiKeyModal = async () => {
    try {
        const response = await axios.get('/api/ai-configs/global-api-key')
        globalApiKey.value = response.data.api_key || ''
        showApiKeyModal.value = true
    } catch (error) {
        toast.error('Không thể lấy API key')
    }
}

const updateGlobalApiKey = async () => {
    try {
        await axios.post('/api/ai-configs/global-api-key', {
            api_key: globalApiKey.value
        })
        toast.success('Cập nhật API key thành công!')
        showApiKeyModal.value = false
    } catch (error) {
        toast.error('Không thể cập nhật API key')
    }
}
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Cấu Hình AI" />
        <SectionMain>
            <!-- Header Section -->
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <BaseIcon :path="mdiRobotExcited" class="w-12 h-12 text-blue-500 animate-pulse" />
                        <div>
                            <h1 class="text-2xl font-bold dark:text-white">
                                Cấu Hình Trợ Lý AI
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Quản lý và tùy chỉnh trợ lý AI thông minh
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <BaseButton label="API Key" color="warning" @click="openApiKeyModal" :icon="mdiKey"
                            class="hover:scale-105 transition-transform" />
                        <BaseButton label="Thêm Mới" color="info" @click="openEditModal()" :icon="mdiPlus"
                            class="hover:scale-105 transition-transform" />
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid gap-6">
                <TransitionGroup name="list" tag="div" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <CardBox v-for="config in configsList" :key="config.id"
                        class="transform hover:scale-102 transition-all duration-300"
                        :class="{ 'ring-2 ring-blue-500': config.is_active }">
                        <!-- Config Card Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold">
                                        {{ config.ai_name }}
                                    </h3>
                                    <span v-if="config.is_active"
                                        class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ config.model_type }}
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button @click="openPreviewModal(config)"
                                    class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <BaseIcon :path="mdiEye" class="w-5 h-5" />
                                </button>
                                <button @click="openEditModal(config)"
                                    class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <BaseIcon :path="mdiPencil" class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(config)"
                                    class="p-2 rounded-full hover:bg-red-100 dark:hover:bg-red-900 transition-colors">
                                    <BaseIcon :path="mdiDelete" class="w-5 h-5 text-red-500" />
                                </button>
                            </div>
                        </div>

                        <!-- Config Card Content -->
                        <div class="space-y-4">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Language</span>
                                    <p>{{ props.languages[config.language] }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Temperature</span>
                                    <p>{{ config.temperature }}</p>
                                </div>
                            </div>

                            <!-- Context Preview -->
                            <div class="mt-4">
                                <span class="text-gray-500 text-sm">Context</span>
                                <p class="text-sm line-clamp-3">
                                    {{ config.context }}
                                </p>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-2 pt-4 border-t dark:border-gray-700">
                                <div class="text-center">
                                    <span class="text-sm text-gray-500">Max Tokens</span>
                                    <p class="font-semibold">{{ config.max_tokens }}</p>
                                </div>
                                <div class="text-center">
                                    <span class="text-sm text-gray-500">Top P</span>
                                    <p class="font-semibold">{{ config.top_p }}</p>
                                </div>
                                <div class="text-center">
                                    <span class="text-sm text-gray-500">Top K</span>
                                    <p class="font-semibold">{{ config.top_k }}</p>
                                </div>
                            </div>
                        </div>
                    </CardBox>
                </TransitionGroup>
            </div>

            <!-- Modals -->
            <EditModal v-if="showEditModal" :config="editingConfig" :field-groups="fieldGroups" v-bind="modalProps"
                @close="closeEditModal" @submit="submitConfig" />

            <DeleteModal v-if="showDeleteModal" :config="activeConfig" :config-types="configTypes" @close="showDeleteModal = false"
                @confirm="deleteConfig" />

            <PreviewModal v-if="showPreviewModal" :config="activeConfig" v-bind="modalProps"
                @close="showPreviewModal = false" />

            <!-- API Key Modal -->
            <TransitionRoot appear :show="showApiKeyModal" as="template">
                <Dialog as="div" @close="showApiKeyModal = false" class="relative z-50">
                    <div class="fixed inset-0 bg-black/30" />
                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <DialogPanel
                                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-slate-900">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6">
                                    Cập Nhật API Key Chung
                                </DialogTitle>
                                <div class="mt-4">
                                    <input v-model="globalApiKey" type="password"
                                        class="w-full rounded-lg border px-4 py-2 dark:bg-slate-800 dark:border-slate-700"
                                        placeholder="Nhập API key..." />
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <BaseButton label="Hủy" color="white" @click="showApiKeyModal = false" />
                                    <BaseButton label="Lưu" color="success" @click="updateGlobalApiKey" />
                                </div>
                            </DialogPanel>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

.list-leave-active {
    position: absolute;
}

/* Thêm hiệu ứng hover cho cards */
.hover\:scale-102:hover {
    transform: scale(1.02);
}

/* Thêm animation cho icon */
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.7;
    }
}
</style>
