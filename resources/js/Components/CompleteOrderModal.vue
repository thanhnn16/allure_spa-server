<template>
    <CardBoxModal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
        title="Hoàn thành đơn hàng" :has-button="false">
        <div class="space-y-6">
            <!-- Header section -->
            <div class="text-center">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                    <i class="mdi mdi-check-circle text-4xl text-green-600 dark:text-green-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Hoàn thành đơn hàng #{{ order.id }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Xác nhận hoàn thành và tạo gói dịch vụ cho khách hàng
                </p>
            </div>

            <!-- Service package info -->
            <div v-if="hasServiceCombo" class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="mdi mdi-information text-blue-500 text-xl mt-0.5 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-blue-900 dark:text-blue-300">
                            Thông tin gói dịch vụ
                        </h4>
                        <ul class="mt-2 space-y-2 text-sm text-blue-700 dark:text-blue-400">
                            <li class="flex items-center">
                                <i class="mdi mdi-check-circle-outline mr-2"></i>
                                Hệ thống sẽ tự động tạo gói liệu trình cho khách hàng
                            </li>
                            <li class="flex items-center">
                                <i class="mdi mdi-check-circle-outline mr-2"></i>
                                Khách hàng có thể bắt đầu đặt lịch sau khi gói được tạo
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Note input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Ghi chú hoàn thành
                </label>
                <textarea v-model="note" rows="3"
                    class="form-textarea w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
                    placeholder="Nhập ghi chú khi hoàn thành đơn hàng (tùy chọn)">
                </textarea>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end space-x-3">
                <button @click="$emit('update:modelValue', false)"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Hủy
                </button>
                <button @click="handleSubmit" :disabled="loading"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    <i v-else class="mdi mdi-check-circle mr-2"></i>
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
    order: {
        type: Object,
        required: true
    },
    hasServiceCombo: {
        type: Boolean,
        default: false
    }
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