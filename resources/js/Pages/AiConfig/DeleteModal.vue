<template>
    <Dialog :open="true" @close="$emit('close')" class="relative z-50">
        <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <DialogPanel class="w-full max-w-md rounded bg-white dark:bg-slate-800 p-6">
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <BaseIcon :path="mdiAlert" size="24" class="text-red-600" />
                    </div>
                    <div class="ml-4">
                        <DialogTitle class="text-lg font-medium">
                            Xác Nhận Xóa
                        </DialogTitle>
                        <p class="mt-1 text-sm text-gray-500">
                            Bạn có chắc chắn muốn xóa cấu hình "{{ config?.ai_name }}"?
                        </p>
                    </div>
                </div>

                <div class="mt-6 bg-gray-50 dark:bg-slate-700 rounded p-4">
                    <div class="text-sm">
                        <p class="font-medium text-gray-700 dark:text-gray-300">
                            Thông tin cấu hình:
                        </p>
                        <ul class="mt-2 list-disc list-inside text-gray-600 dark:text-gray-400">
                            <li>Tên: {{ config?.ai_name }}</li>
                            <li>Loại: {{ configTypes[config?.type]?.name }}</li>
                            <li>Ngôn ngữ: {{ config?.language }}</li>
                        </ul>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <BaseButton type="button" label="Hủy" color="white" @click="$emit('close')" />
                    <BaseButton type="button" label="Xóa" color="danger" :loading="isDeleting" @click="handleDelete" />
                </div>
            </DialogPanel>
        </div>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import { mdiAlert } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
    config: {
        type: Object,
        required: true
    },
    configTypes: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'confirm'])

const isDeleting = ref(false)

// Add toast
const toast = useToast()

const handleDelete = async () => {
    try {
        isDeleting.value = true;

        const response = await axios.delete(`/api/ai-configs/${props.config.id}`);

        toast.success('Xóa cấu hình thành công!');
        emit('close');
        router.reload();

    } catch (error) {
        console.error('Delete error:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi xóa');
    } finally {
        isDeleting.value = false;
    }
}
</script>