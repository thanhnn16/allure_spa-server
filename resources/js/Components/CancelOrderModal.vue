<template>
    <CardBoxModal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" :title="title"
        :has-button="false">
        <div class="p-6">
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
                        <span class="font-medium">{{ formatPrice(order.total_amount) }}</span>
                    </div>
                    <div v-if="order.invoice" class="flex justify-between text-red-600">
                        <span>Đã thanh toán:</span>
                        <span>{{ formatPrice(order.invoice.paid_amount) }}</span>
                    </div>
                </div>

                <div>
                    <label for="cancel-reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Lý do hủy đơn
                    </label>
                    <textarea id="cancel-reason" v-model="note" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 
                               bg-white dark:bg-dark-surface 
                               text-gray-900 dark:text-gray-100
                               focus:border-primary-500 focus:ring-primary-500
                               dark:focus:border-primary-400 dark:focus:ring-primary-400
                               shadow-sm" placeholder="Nhập lý do hủy đơn hàng..."></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="$emit('update:modelValue', false)" class="btn-secondary">
                    Đóng
                </button>
                <button @click="handleSubmit" :disabled="loading" class="btn-danger">
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    Xác nhận hủy
                </button>
            </div>
        </div>
    </CardBoxModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import CardBoxModal from './CardBoxModal.vue'
import axios from 'axios'
import { useToast } from "vue-toastification"

const props = defineProps({
    modelValue: Boolean,
    order: {
        type: Object,
        required: true
    },
    title: String
})

const emit = defineEmits(['update:modelValue', 'cancelled'])

const note = ref('')
const loading = ref(false)
const toast = useToast()

// Format price helper function
const formatPrice = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount)
}

const handleSubmit = async () => {
    if (!note.value.trim()) {
        toast.error('Vui lòng nhập lý do hủy đơn hàng')
        return
    }

    loading.value = true
    try {
        const response = await axios.patch(`/api/orders/${props.order.id}/cancel`, {
            note: note.value
        })

        if (response.data.success) {
            toast.success('Hủy đơn hàng thành công')
            emit('cancelled', { reason: note.value })
            emit('update:modelValue', false)
        }
    } catch (error) {
        console.error('Error canceling order:', error)
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi hủy đơn hàng')
    } finally {
        loading.value = false
    }
}

// Watch để reset form khi đóng modal
watch(() => props.modelValue, (val) => {
    if (!val) {
        note.value = ''
        loading.value = false
    }
})
</script>