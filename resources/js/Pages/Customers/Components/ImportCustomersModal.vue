<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiFileUpload, mdiClose, mdiCheckBold, mdiCancel } from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
    modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'imported', 'close'])

const form = useForm({
    file: null,
})

const fileInput = ref(null)
const fileName = ref('')
const dragOver = ref(false)
const importStatus = ref('')
const importResults = ref(null)

const handleFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        setFile(file)
    }
}

const setFile = (file) => {
    form.file = file
    fileName.value = file.name
    importStatus.value = ''
    importResults.value = null
}

const handleDragOver = (event) => {
    event.preventDefault()
    dragOver.value = true
}

const handleDragLeave = () => {
    dragOver.value = false
}

const handleDrop = (event) => {
    event.preventDefault()
    dragOver.value = false
    const file = event.dataTransfer.files[0]
    if (file) {
        setFile(file)
    }
}

const removeFile = () => {
    form.file = null
    fileName.value = ''
    importStatus.value = ''
    importResults.value = null
}

const confirmImport = () => {
    if (!form.file) {
        alert('Vui lòng chọn file trước khi xác nhận.')
        return
    }

    importStatus.value = 'Đang xử lý...'
    form.post(route('users.import'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (response) => {
            emit('update:modelValue', false)
            emit('imported', response)
        },
        onError: (errors) => {
            console.error('Import failed', errors)
            importStatus.value = 'Có lỗi xảy ra. Vui lòng kiểm tra thông báo lỗi.'
            importResults.value = errors
        }
    })
}

const cancelImport = () => {
    removeFile()
    emit('update:modelValue', false)
}
</script>

<template>
    <CardBoxModal :modelValue="modelValue" @update:modelValue="emit('update:modelValue', $event)"
        title="Nhập khách hàng từ file Excel" :hasCancel="false" :hasButton="false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Nhập khách hàng từ file Excel
            </h2>

            <div class="mb-6">
                <div class="flex items-center justify-center w-full">
                    <label for="file-upload"
                        class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100"
                        :class="{ 'border-blue-500 bg-blue-50': dragOver }" @dragover="handleDragOver"
                        @dragleave="handleDragLeave" @drop="handleDrop">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6" v-if="!form.file">
                            <BaseIcon :path="mdiFileUpload" class="w-10 h-10 mb-3 text-gray-400" />
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-semibold">Nhấp để tải lên</span> hoặc kéo và thả
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                XLSX, XLS, hoặc CSV (Tối đa 10MB)
                            </p>
                        </div>
                        <div class="flex items-center bg-gray-100 p-2 rounded" v-else>
                            <BaseIcon :path="mdiFileUpload" class="w-6 h-6 mr-2 text-gray-600" />
                            <span class="text-sm font-medium text-gray-700">{{ fileName }}</span>
                            <button type="button" @click.prevent="removeFile"
                                class="ml-2 text-sm text-red-600 hover:text-red-800">
                                <BaseIcon :path="mdiClose" class="w-5 h-5" />
                            </button>
                        </div>
                        <input id="file-upload" ref="fileInput" type="file" class="hidden" @change="handleFileChange"
                            accept=".xlsx,.xls,.csv" />
                    </label>
                </div>
                <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>
            </div>

            <div v-if="importStatus" class="mb-4">
                <p :class="{'text-blue-600': importStatus === 'Đang xử lý...', 'text-green-600': importStatus === 'Hoàn thành', 'text-red-600': importStatus === 'Có lỗi xảy ra. Vui lòng kiểm tra thông báo lỗi.'}">
                    {{ importStatus }}
                </p>
            </div>

            <div v-if="importResults" class="mb-4">
                <p v-for="(error, index) in importResults" :key="index" class="text-red-600">
                    {{ error }}
                </p>
            </div>

            <div class="flex justify-end space-x-2">
                <BaseButton type="button" color="info" :icon="mdiCheckBold" label="Xác nhận" 
                    @click="confirmImport" :disabled="form.processing || !form.file" />
                <BaseButton type="button" color="danger" :icon="mdiCancel" label="Hủy" 
                    @click="cancelImport" :disabled="form.processing" />
            </div>
        </div>
    </CardBoxModal>
</template>
