<template>
    <span :class="[baseClasses, statusClasses[status]]">
        <span :class="['w-2 h-2 rounded-full mr-2', dotClasses[status]]"></span>
        {{ statusText[status] }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: (value) => ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
})

const baseClasses = computed(() => {
    const sizes = {
        sm: 'px-2 py-1 text-xs',
        md: 'px-3 py-1.5 text-sm',
        lg: 'px-4 py-2 text-base'
    }
    return `inline-flex items-center font-medium rounded-full ${sizes[props.size]}`
})

const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    shipping: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
}

const dotClasses = {
    pending: 'bg-yellow-400',
    confirmed: 'bg-blue-400',
    shipping: 'bg-purple-400',
    completed: 'bg-green-400',
    cancelled: 'bg-red-400'
}

const statusText = {
    pending: 'Chờ xác nhận',
    confirmed: 'Đã xác nhận',
    shipping: 'Đang giao hàng',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
}
</script>