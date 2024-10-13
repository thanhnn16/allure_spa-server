<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiFileUpload } from '@mdi/js'
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
            form.reset()
            fileName.value = ''
            emit('imported')
            emit('close')
        },
    })
}
</script>

<template>
    <CardBoxModal :show="show" @close="$emit('close')" title="Nhập khách hàng từ file Excel">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Nhập khách hàng từ file Excel
            </h2>

            <form @submit.prevent="submit" class="mt-6">
                <div>
                    <label for="file-upload" class="block text-sm font-medium text-gray-700">File Excel</label>
                    <div class="mt-1 flex items-center">
                        <label for="file-upload"
                            class="flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                            <BaseIcon :path="mdiFileUpload" class="mr-2" />
                            Chọn file
                        </label>
                        <input id="file-upload" ref="fileInput" type="file" class="hidden" @change="handleFileChange"
                            accept=".xlsx,.xls,.csv" />
                        <span class="ml-3 text-sm text-gray-500">{{ fileName || 'Chưa chọn file' }}</span>
                    </div>
                    <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <BaseButton type="submit" :disabled="form.processing || !form.file" :class="{ 'opacity-25': form.processing }">
                        Nhập khách hàng
                    </BaseButton>
                </div>
            </form>
        </div>
    </CardBoxModal>
</template>
