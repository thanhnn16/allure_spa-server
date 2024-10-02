<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { mdiClose, mdiFileUpload } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
    show: Boolean,
})

const emit = defineEmits(['close', 'imported'])

const form = useForm({
    file: null,
})

const fileInput = ref(null)
const fileName = ref('')

const handleFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.file = file
        fileName.value = file.name
    }
}

const submit = () => {
    form.post(route('users.import'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('imported')
            emit('close')
            form.reset()
            fileName.value = ''
        },
    })
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                @click="$emit('close')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Nhập khách hàng từ file Excel
                            </h3>
                            <div class="mt-2">
                                <form @submit.prevent="submit" class="mt-6">
                                    <div class="flex items-center justify-center w-full">
                                        <label for="file-upload"
                                            class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <BaseIcon :path="mdiFileUpload" size="48" class="mb-3 text-gray-400" />
                                                <p class="mb-2 text-sm text-gray-500">
                                                    <span class="font-semibold">Nhấp để tải lên</span> hoặc kéo và thả
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    XLSX, XLS, CSV (Tối đa 10MB)
                                                </p>
                                            </div>
                                            <input id="file-upload" ref="fileInput" type="file" class="hidden"
                                                @change="handleFileChange" accept=".xlsx,.xls,.csv" />
                                        </label>
                                    </div>
                                    <div v-if="fileName" class="mt-4 text-sm text-gray-500">
                                        Tệp đã chọn: {{ fileName }}
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <BaseButton type="submit" color="info" label="Nhập khách hàng"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing || !form.file"
                        @click="submit" />
                    <BaseButton type="button" color="white" label="Hủy" @click="$emit('close')" class="mr-3" />
                </div>
            </div>
        </div>
    </div>
</template>