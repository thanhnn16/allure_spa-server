<template>
    <CardBoxModal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
        title="Tạo hóa đơn cho đơn hàng #{{ order.id }}" :has-button="false">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">
                Tạo hóa đơn cho đơn hàng #{{ order.id }}
            </h3>

            <div class="space-y-4">
                <div class="bg-gray-50 dark:bg-dark-bg/50 p-4 rounded-lg">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">Tổng tiền:</span>
                        <span class="font-medium">{{ formatCurrency(order.total_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Giảm giá:</span>
                        <span class="text-green-600">-{{ formatCurrency(order.discount_amount) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t mt-2">
                        <span class="font-medium">Thành tiền:</span>
                        <span class="font-medium">
                            {{ formatCurrency(order.total_amount - order.discount_amount) }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Ghi chú (tùy chọn)</label>
                    <textarea v-model="note" rows="3" class="form-textarea w-full"
                        placeholder="Nhập ghi chú cho hóa đơn"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="$emit('update:modelValue', false)" class="btn-secondary">
                    Hủy
                </button>
                <button @click="handleSubmit" :disabled="loading" class="btn-primary">
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    Tạo hóa đơn
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
    order: Object
})

const emit = defineEmits(['update:modelValue', 'created'])

const note = ref('')
const loading = ref(false)

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount)
}

const handleSubmit = async () => {
    loading.value = true
    try {
        emit('created', { note: note.value })
    } finally {
        loading.value = false
    }
}
</script>