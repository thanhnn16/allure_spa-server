<template>
    <Dialog :open="true" @close="$emit('close')" class="relative z-50">
        <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <DialogPanel class="w-full max-w-md rounded bg-white dark:bg-slate-800 p-6">
                <div class="flex justify-between items-center mb-6">
                    <DialogTitle class="text-lg font-medium">
                        Tải Lên Cấu Hình
                    </DialogTitle>
                    <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                        <BaseIcon :path="mdiClose" size="24" />
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Config Type Selection -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Loại Cấu Hình
                        </label>
                        <select v-model="formData.type" class="form-select w-full rounded-md">
                            <option v-for="(type, key) in configTypes" :key="key" :value="key">
                                {{ type.name }}
                            </option>
                        </select>
                    </div>

                    <!-- File Upload Area -->
                    <div class="border-2 border-dashed rounded-lg p-6 text-center"
                        :class="{ 'border-blue-500 bg-blue-50': dragActive }" @dragenter.prevent="handleDrag"
                        @dragover.prevent="handleDrag" @dragleave.prevent="handleDrag" @drop.prevent="handleDrop">
                        <div v-if="!formData.file">
                            <BaseIcon :path="mdiUpload" size="48" class="mx-auto mb-4 text-gray-400" />
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Kéo thả file hoặc
                                <button type="button" class="text-blue-500 hover:text-blue-600"
                                    @click="triggerFileInput">
                                    chọn file
                                </button>
                            </p>
                            <p class="mt-2 text-xs text-gray-500">
                                Hỗ trợ file .txt và .json
                            </p>
                        </div>

                        <div v-else class="text-left">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <BaseIcon :path="mdiFile" size="24" class="text-gray-400" />
                                    <span class="text-sm">{{ formData.file.name }}</span>
                                </div>
                                <button type="button" class="text-red-500 hover:text-red-600" @click="removeFile">
                                    <BaseIcon :path="mdiClose" size="20" />
                                </button>
                            </div>

                            <div v-if="filePreview" class="mt-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Xem Trước:
                                </p>
                                <pre class="text-xs bg-gray-50 dark:bg-slate-700 p-3 rounded max-h-40 overflow-auto">
                  {{ filePreview }}
                </pre>
                            </div>
                        </div>
                    </div>

                    <input ref="fileInput" type="file" accept=".txt,.json" class="hidden" @change="handleFileChange" />

                    <div class="flex justify-end space-x-3">
                        <BaseButton type="button" label="Hủy" color="white" @click="$emit('close')" />
                        <BaseButton type="submit" label="Tải Lên" color="info" :loading="isUploading"
                            :disabled="!formData.file" />
                    </div>
                </form>
            </DialogPanel>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import { mdiClose, mdiUpload, mdiFile } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
    configTypes: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'upload'])

const fileInput = ref(null)
const dragActive = ref(false)
const filePreview = ref('')
const isUploading = ref(false)

const formData = reactive({
    type: 'system_prompt',
    file: null
})

const triggerFileInput = () => {
    fileInput.value.click()
}

const handleFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        handleFiles(file)
    }
}

const handleDrag = (e) => {
    e.preventDefault()
    e.stopPropagation()
    dragActive.value = e.type === 'dragenter' || e.type === 'dragover'
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
    // Validate file type
    if (!['text/plain', 'application/json'].includes(file.type)) {
        alert('Chỉ hỗ trợ file .txt và .json')
        return
    }

    formData.file = file

    // Generate preview
    const reader = new FileReader()
    reader.onload = (e) => {
        filePreview.value = e.target.result
    }
    reader.readAsText(file)
}

const removeFile = () => {
    formData.file = null
    filePreview.value = ''
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const handleSubmit = async () => {
    try {
        isUploading.value = true
        emit('upload', formData)
    } finally {
        isUploading.value = false
    }
}
</script>