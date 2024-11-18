<template>
    <CardBoxModal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
        title="Cập nhật trạng thái đơn hàng" :has-button="false">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Trạng thái mới</label>
                <select v-model="selectedStatus"
                    class="form-select w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface">
                    <option v-for="status in availableStatuses" :key="status" :value="status">
                        {{ getStatusText(status) }}
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Ghi chú (tùy chọn)</label>
                <textarea v-model="note" rows="3"
                    class="form-textarea w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface"
                    placeholder="Nhập ghi chú cho việc cập nhật trạng thái"></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button @click="$emit('update:modelValue', false)"
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-border hover:bg-gray-50 dark:hover:bg-dark-bg/50 transition-colors duration-200">
                    Hủy
                </button>
                <button @click="handleSubmit" :disabled="loading"
                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    Cập nhật
                </button>
            </div>
        </div>
    </CardBoxModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import CardBoxModal from './CardBoxModal.vue'

const props = defineProps({
    modelValue: Boolean,
    order: Object,
    availableStatuses: Array
})

const emit = defineEmits(['update:modelValue', 'updated'])

const selectedStatus = ref(props.order.status)
const note = ref('')
const loading = ref(false)

// Reset form khi modal đóng
watch(() => props.modelValue, (newVal) => {
    if (!newVal) {
        selectedStatus.value = props.order.status
        note.value = ''
        loading.value = false
    }
})

const getStatusText = (status) => {
    const statusTexts = {
        pending: 'Chờ xác nhận',
        confirmed: 'Đã xác nhận',
        shipping: 'Đang giao hàng',
        completed: 'Hoàn thành',
        cancelled: 'Đã hủy'
    }
    return statusTexts[status]
}

const handleSubmit = async () => {
    if (loading.value) return;

    loading.value = true
    try {
        emit('updated', {
            status: selectedStatus.value,
            note: note.value
        })
    } catch (error) {
        console.error('Error in handleSubmit:', error)
    }
}
</script>