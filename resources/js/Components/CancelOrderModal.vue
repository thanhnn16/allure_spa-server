<template>
    <CardBoxModal
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        title="Hủy đơn hàng #{{ order.id }}"
        :has-button="false"
    >
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">
                Hủy đơn hàng #{{ order.id }}
            </h3>

            <div class="space-y-4">
                <!-- Cảnh báo -->
                <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-lg">
                    <div class="flex">
                        <i class="mdi mdi-alert text-red-400 text-xl mr-3"></i>
                        <div>
                            <h4 class="font-medium text-red-800 dark:text-red-300">
                                Cảnh báo
                            </h4>
                            <p class="text-sm text-red-700 dark:text-red-400">
                                Bạn chắc chắn muốn hủy đơn hàng này? Hành động này không thể hoàn tác.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Thông tin đơn hàng -->
                <div class="bg-gray-50 dark:bg-dark-bg/50 p-4 rounded-lg">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">Tổng tiền:</span>
                        <span class="font-medium">{{ formatCurrency(order.total_amount) }}</span>
                    </div>
                    <div v-if="order.invoice" class="flex justify-between text-red-600">
                        <span>Đã thanh toán:</span>
                        <span>{{ formatCurrency(order.invoice.paid_amount) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        Lý do hủy đơn <span class="text-red-500">*</span>
                    </label>
                    <textarea v-model="reason" rows="3" class="form-textarea w-full"
                        :class="{ 'border-red-500': showError }"
                        placeholder="Vui lòng nhập lý do hủy đơn hàng"></textarea>
                    <p v-if="showError" class="mt-1 text-sm text-red-500">
                        Vui lòng nhập lý do hủy đơn hàng
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="$emit('update:modelValue', false)" class="btn-secondary">
                    Đóng
                </button>
                <button @click="handleSubmit" :disabled="loading || !canCancel" class="btn-danger">
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    Xác nhận hủy
                </button>
            </div>
        </div>
    </CardBoxModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import CardBoxModal from './CardBoxModal.vue'

const props = defineProps({
    modelValue: Boolean,
    order: Object
})

const emit = defineEmits(['update:modelValue', 'cancelled'])

const reason = ref('')
const loading = ref(false)
const showError = ref(false)

const canCancel = computed(() => {
    // Chỉ cho phép hủy đơn khi chưa thanh toán hoặc ở trạng thái cho phép
    return ['pending', 'confirmed'].includes(props.order.status) &&
        (!props.order.invoice || props.order.invoice.status !== 'paid')
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount)
}

const handleSubmit = async () => {
    if (!canCancel.value) return
    if (!reason.value.trim()) {
        showError.value = true
        return
    }

    loading.value = true
    try {
        emit('cancelled', { reason: reason.value })
    } finally {
        loading.value = false
    }
}

// Reset error khi người dùng nhập
watch(reason, () => {
    if (reason.value.trim()) {
        showError.value = false
    }
})
</script>