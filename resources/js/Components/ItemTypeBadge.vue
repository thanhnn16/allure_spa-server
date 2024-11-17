<template>
    <span :class="[baseClasses, typeClasses[type]]">
        <i :class="['mdi mr-1', typeIcons[type]]"></i>
        {{ typeText[type] }}
    </span>
</template>

<script setup>
const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) => ['product', 'service'].includes(value)
    }
})

const baseClasses = 'inline-flex items-center px-2 py-1 text-xs font-medium rounded-full'

const typeClasses = {
    product: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    service: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300'
}

const typeIcons = {
    product: 'mdi-package-variant',
    service: 'mdi-spa'
}

const typeText = {
    product: 'Sản phẩm',
    service: 'Dịch vụ'
}
</script>