<template>
    <CardBoxModal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
        title="Hoàn thành đơn hàng #{{ order.id }}" :has-button="false">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">
                Hoàn thành đơn hàng #{{ order.id }}
            </h3>

            <div class="space-y-4">
                <!-- Thông báo về gói dịch vụ -->
                <div v-if="hasServiceCombo" class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg">
                    <div class="flex">
                        <i class="mdi mdi-information text-blue-400 text-xl mr-3"></i>
                        <div>
                            <h4 class="font-medium text-blue-800 dark:text-blue-300">
                                Thông tin quan trọng
                            </h4>
                            <p class="text-sm text-blue-700 dark:text-blue-400">
                                Đơn hàng này có gói dịch vụ combo. Khi hoàn thành, hệ thống sẽ tự động tạo gói liệu
                                trình cho khách hàng.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Kiểm tra điều kiện -->
                <div class="bg-gray-50 dark:bg-dark-bg/50 p-4 rounded-lg space-y-2">
                    <div class="flex items-center">
                        <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                        <span>Đơn hàng đã được xác nhận</span>
                    </div>
                    <div class="flex items-center">
                        <i :class="[
                            'mdi mr-2',
                            order.invoice?.status === 'paid'
                                ? 'mdi-check-circle text-green-500'
                                : 'mdi-alert-circle text-red-500'
                        ]"></i>
                        <span>Hóa đơn đã thanh toán đầy đủ</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Ghi chú hoàn thành (tùy chọn)</label>
                    <textarea v-model="note" rows="3" class="form-textarea w-full"
                        placeholder="Nhập ghi chú khi hoàn thành đơn hàng"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="$emit('update:modelValue', false)" class="btn-secondary">
                    Hủy
                </button>
                <button @click="handleSubmit" :disabled="loading || !canComplete" class="btn-success">
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    Hoàn thành đơn hàng
                </button>
            </div>
        </div>
    </CardBoxModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import CardBoxModal from './CardBoxModal.vue'
import axios from 'axios'

const props = defineProps({
    modelValue: Boolean,
    order: Object,
    hasServiceCombo: Boolean
})

const emit = defineEmits(['update:modelValue', 'completed'])

const note = ref('')
const loading = ref(false)

const canComplete = computed(() => {
    return props.order.invoice?.status === 'paid'
})

const handleSubmit = async () => {
    if (!canComplete.value) return

    loading.value = true
    try {
        const response = await axios.post(`/api/orders/${props.order.id}/complete`, {
            note: note.value
        })

        if (response.data.success) {
            emit('completed')
            emit('update:modelValue', false)
            note.value = ''
        }
    } catch (error) {
        console.error('Error completing order:', error)
    } finally {
        loading.value = false
    }
}
</script>