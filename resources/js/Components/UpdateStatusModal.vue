<template>
    <CardBoxModal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
        title="Cập nhật trạng thái đơn hàng" :has-button="false">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">
                Cập nhật trạng thái đơn hàng
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Trạng thái mới</label>
                    <select v-model="selectedStatus" class="form-select w-full">
                        <option v-for="status in availableStatuses" :key="status" :value="status">
                            {{ getStatusText(status) }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Ghi chú (tùy chọn)</label>
                    <textarea v-model="note" rows="3" class="form-textarea w-full"
                        placeholder="Nhập ghi chú cho việc cập nhật trạng thái"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="$emit('update:modelValue', false)" class="btn-secondary">
                    Hủy
                </button>
                <button @click="handleSubmit" :disabled="loading" class="btn-primary">
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    Cập nhật
                </button>
            </div>
        </div>
    </CardBoxModal>
</template>

<script setup>
import { ref } from 'vue'
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
    loading.value = true
    try {
        emit('updated', {
            status: selectedStatus.value,
            note: note.value
        })
    } finally {
        loading.value = false
    }
}
</script>