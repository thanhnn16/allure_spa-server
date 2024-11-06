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
    mdiKey
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
        id: 'system',
        label: 'Mô Tả Hệ Thống',
        icon: mdiRobotExcited
    },
    {
        id: 'vision',
        label: 'Phân Tích Hình Ảnh',
        icon: mdiImageSearch
    },
    {
        id: 'general',
        label: 'Cài Đặt Chung',
        icon: mdiCog
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

// Add logging to check props
console.log('AiConfigView Props:', {
    configs: props.configs,
    configTypes: props.configTypes,
    modelTypes: props.modelTypes,
    languages: props.languages,
    responseFormats: props.responseFormats,
    defaultSafetySettings: props.defaultSafetySettings,
    defaultFunctionDeclarations: props.defaultFunctionDeclarations,
    defaultToolConfig: props.defaultToolConfig,
    configTemplates: props.configTemplates
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
    // Left column fields
    {
        key: 'ai_name',
        label: 'Tên Cấu Hình',
        type: 'text',
        column: 'left',
        required: true,
        description: 'Tên định danh cho cấu hình AI'
    },
    {
        key: 'type',
        label: 'Loại Cấu Hình',
        type: 'select',
        column: 'left',
        options: Object.entries(props.configTypes).map(([value, label]) => ({
            value,
            label: label.name
        })),
        description: 'Loại cấu hình AI'
    },
    {
        key: 'language',
        label: 'Ngôn Ngữ',
        type: 'select',
        column: 'left',
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
        column: 'left',
        options: Object.entries(props.modelTypes).map(([value, label]) => ({
            value,
            label
        })),
        description: 'Model AI được sử dụng'
    },
    // Right column fields
    {
        key: 'temperature',
        label: 'Temperature',
        type: 'number',
        column: 'right',
        min: 0,
        max: 1,
        step: 0.1,
        description: 'Độ sáng tạo của câu trả lời (0-1)'
    },
    {
        key: 'max_tokens',
        label: 'Max Tokens',
        type: 'number',
        column: 'right',
        min: 1,
        max: 8192,
        description: 'Số tokens tối đa cho mỗi câu trả lời'
    },
    {
        key: 'top_p',
        label: 'Top P',
        type: 'number',
        column: 'right',
        min: 0,
        max: 1,
        step: 0.1,
        description: 'Nucleus sampling probability'
    },
    {
        key: 'top_k',
        label: 'Top K',
        type: 'number',
        column: 'right',
        min: 1,
        max: 100,
        description: 'Top K sampling'
    },
    // Advanced fields
    {
        key: 'safety_settings',
        label: 'Safety Settings',
        type: 'json-editor',
        description: 'Cấu hình an toàn',
        defaultValue: JSON.stringify(props.defaultSafetySettings, null, 2),
        group: 'advanced'
    },
    {
        key: 'function_declarations',
        label: 'Function Declarations',
        type: 'json-editor',
        description: 'Cấu hình các hàm có thể gọi',
        defaultValue: JSON.stringify(props.defaultFunctionDeclarations, null, 2),
        group: 'advanced'
    },
    {
        key: 'tool_config',
        label: 'Tool Configuration',
        type: 'json-editor',
        description: 'Cấu hình cho function calling',
        defaultValue: JSON.stringify(props.defaultToolConfig, null, 2),
        group: 'advanced'
    },
    // Thêm trường context
    {
        key: 'context',
        label: 'Context',
        type: 'json-editor',
        column: 'right',
        description: 'Ngữ cảnh và hướng dẫn cho AI',
        defaultValue: '',
        schema: null
    },
    {
        key: 'function_declarations',
        label: 'Function Declarations',
        type: 'json-editor',
        column: 'right',
        description: 'Cấu hình các hàm có thể gọi',
        defaultValue: JSON.stringify(props.defaultFunctionDeclarations, null, 2),
        schema: null
    },
    {
        key: 'tool_config',
        label: 'Tool Configuration',
        type: 'json-editor',
        column: 'right',
        description: 'Cấu hình cho function calling',
        defaultValue: JSON.stringify(props.defaultToolConfig, null, 2),
        schema: null
    }
]);

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
            <!-- Header với search và filters -->
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <BaseIcon :path="mdiRobot" class="w-8 h-8 text-blue-500" />
                        <div>
                            <h1 class="text-2xl font-bold dark:text-white">Cấu Hình AI</h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ filteredConfigs.length }} cấu hình
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <BaseButton label="Tải Lên" color="success" @click="openUploadModal" :icon="mdiUpload"
                            :loading="isLoading" />
                        <BaseButton label="Thêm Mới" color="info" @click="openEditModal()" :icon="mdiPlus" />
                        <BaseButton label="API Key" color="warning" @click="openApiKeyModal" :icon="mdiKey" />
                    </div>
                </div>
            </div>

            <!-- Tabs với animation -->
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-4 mb-6">
                <div class="flex space-x-2 overflow-x-auto">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        class="px-4 py-2 rounded-md transition-all duration-200 whitespace-nowrap"
                        :class="getTabClasses(tab.id)">
                        <div class="flex items-center space-x-2">
                            <BaseIcon :path="tab.icon" class="w-5 h-5" />
                            <span>{{ tab.label }}</span>
                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full" :class="getCounterClasses(tab.id)">
                                {{ getConfigCount(tab.id) }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Grid layout cho configs -->
            <TransitionGroup name="config-list" tag="div" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <CardBox v-for="config in filteredConfigs" :key="config.id"
                    class="!p-6 hover:shadow-lg transition-shadow duration-200"
                    :class="{ 'border-2 border-blue-500': config.is_active }">
                    <!-- Config card content -->
                    <div class="flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-lg font-semibold dark:text-white truncate">
                                        {{ config.ai_name }}
                                    </h3>
                                    <span v-if="config.is_active"
                                        class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 whitespace-nowrap">
                                        Active
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                    {{ config.context }}
                                </p>
                            </div>
                            <div class="flex flex-shrink-0 space-x-2 ml-4">
                                <BaseButton color="info" :icon="mdiEye" small @click="openPreviewModal(config)" />
                                <BaseButton color="info" :icon="mdiPencil" small @click="openEditModal(config)" />
                                <BaseButton color="danger" :icon="mdiDelete" small @click="confirmDelete(config)" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm mt-auto">
                            <div class="space-y-1">
                                <p class="text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Model:</span>
                                    {{ config.model_type }}
                                </p>
                                <p class="text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Language:</span>
                                    {{ config.language }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Temp:</span>
                                    {{ config.temperature }}
                                </p>
                                <p class="text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Tokens:</span>
                                    {{ config.max_tokens }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardBox>
            </TransitionGroup>

            <!-- Modals -->
            <EditModal v-if="showEditModal" :config="editingConfig" :fields="configFields" :config-types="configTypes"
                :model-types="modelTypes" :languages="languages" :response-formats="responseFormats"
                :default-safety-settings="defaultSafetySettings"
                :default-function-declarations="defaultFunctionDeclarations" :default-tool-config="defaultToolConfig"
                @close="closeEditModal" @submit="submitConfig" />

            <UploadModal v-if="showUploadModal" :config-types="configTypes" @close="closeUploadModal"
                @upload="handleFileUpload" />

            <DeleteModal v-if="showDeleteModal" :config="activeConfig" :config-types="configTypes"
                @close="showDeleteModal = false" @confirm="deleteConfig" />

            <PreviewModal v-if="showPreviewModal" :config="activeConfig" v-bind="modalProps"
                @close="showPreviewModal = false" />

            <!-- Thêm modal API key -->
            <TransitionRoot appear :show="showApiKeyModal" as="template">
                <Dialog as="div" @close="showApiKeyModal = false" class="relative z-50">
                    <div class="fixed inset-0 bg-black/30" />
                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <DialogPanel
                                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-slate-900">
                                <DialogTitle as="h3"
                                    class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                    Cập Nhật API Key Chung
                                </DialogTitle>
                                <div class="mt-4">
                                    <input v-model="globalApiKey" type="password"
                                        class="w-full rounded-lg border px-4 py-2 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
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
.config-list-move,
.config-list-enter-active,
.config-list-leave-active {
    transition: all 0.5s ease;
}

.config-list-enter-from,
.config-list-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

.config-list-leave-active {
    position: absolute;
}
</style>
