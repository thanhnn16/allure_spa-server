<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import { mdiPlus, mdiMinus, mdiTableSearch, mdiFileExcel, mdiFilter, mdiFilterOff } from '@mdi/js'

const props = defineProps({
    stockMovements: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    products: {
        type: Array,
        default: () => []
    }
})

const form = ref({
    product_id: '',
    quantity: '',
    type: 'in',
    reason: '',
    reference_number: '',
    note: ''
})

const filterForm = ref({
    start_date: '',
    end_date: '',
    product_id: '',
    type: ''
})

const showFilters = ref(false)
const errors = ref({})
const isLoading = ref(false)

// Format sản phẩm để hiển thị
const formattedProducts = computed(() => {
    return props.products.map(product => ({
        ...product,
        label: `${product.name} - SL: ${product.quantity} - ${formatPrice(product.price)}`,
        value: product.id
    }))
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const submitForm = async () => {
    isLoading.value = true
    try {
        await router.post(route('stock-movements.store'), form.value)
        form.value = {
            product_id: '',
            quantity: '',
            type: 'in',
            reason: '',
            reference_number: '',
            note: ''
        }
        errors.value = {}
    } catch (e) {
        errors.value = e.response?.data?.errors || {}
    }
    isLoading.value = false
}

const applyFilters = () => {
    router.get(route('stock-movements.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true
    })
}

const resetFilters = () => {
    filterForm.value = {
        start_date: '',
        end_date: '',
        product_id: '',
        type: ''
    }
    applyFilters()
}

const exportData = () => {
    window.open(route('stock-movements.export', filterForm.value))
}

const stockTypes = [
    { value: 'in', label: 'Nhập kho' },
    { value: 'out', label: 'Xuất kho' }
]

const formattedDate = (date) => {
    return new Date(date).toLocaleString('vi-VN')
}

const formatNote = (noteJson) => {
    try {
        const note = JSON.parse(noteJson)
        return `${note.reason || ''} ${note.comment || ''}`
    } catch (e) {
        return noteJson
    }
}
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Quản lý kho" />

        <SectionMain>
            <CardBox class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold">Quản lý nhập xuất kho</h1>
                    <BaseButtons>
                        <BaseButton color="info" :icon="showFilters ? mdiFilterOff : mdiFilter"
                            @click="showFilters = !showFilters" label="Bộ lọc" />
                        <BaseButton color="success" :icon="mdiFileExcel" @click="exportData" label="Xuất Excel" />
                        <BaseButton color="info" :icon="mdiTableSearch" @click="router.get(route('reports.stock'))"
                            label="Báo cáo" />
                    </BaseButtons>
                </div>

                <!-- Bộ lọc -->
                <div v-if="showFilters" class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <FormField label="Từ ngày">
                            <FormControl v-model="filterForm.start_date" type="date" />
                        </FormField>
                        <FormField label="Đến ngày">
                            <FormControl v-model="filterForm.end_date" type="date" />
                        </FormField>
                        <FormField label="Sản phẩm">
                            <FormControl v-model="filterForm.product_id" type="select" :options="formattedProducts"
                                placeholder="Tất cả sản phẩm" />
                        </FormField>
                        <FormField label="Loại">
                            <FormControl v-model="filterForm.type" type="select" :options="stockTypes"
                                placeholder="Tất cả" />
                        </FormField>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <BaseButton color="danger" @click="resetFilters" label="Xóa lọc" />
                        <BaseButton color="info" @click="applyFilters" label="Áp dụng" />
                    </div>
                </div>
            </CardBox>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form nhập/xuất kho -->
                <CardBox>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <FormField label="Sản phẩm" :error="errors.product_id">
                            <FormControl v-model="form.product_id" type="select" :options="formattedProducts"
                                placeholder="Chọn sản phẩm" />
                        </FormField>

                        <div class="grid grid-cols-2 gap-4">
                            <FormField label="Loại" :error="errors.type">
                                <FormControl v-model="form.type" type="select" :options="stockTypes" />
                            </FormField>

                            <FormField label="Số lượng" :error="errors.quantity">
                                <FormControl v-model="form.quantity" type="number" min="1" />
                            </FormField>
                        </div>

                        <FormField label="Lý do" :error="errors.reason">
                            <FormControl v-model="form.reason" />
                        </FormField>

                        <FormField label="Số tham chiếu" :error="errors.reference_number">
                            <FormControl v-model="form.reference_number" />
                        </FormField>

                        <FormField label="Ghi chú" :error="errors.note">
                            <FormControl v-model="form.note" type="textarea" rows="3" />
                        </FormField>

                        <BaseButtons>
                            <BaseButton type="submit" color="info" :loading="isLoading"
                                :icon="form.type === 'in' ? mdiPlus : mdiMinus"
                                :label="form.type === 'in' ? 'Nhập kho' : 'Xuất kho'" />
                        </BaseButtons>
                    </form>
                </CardBox>

                <!-- Lịch sử nhập xuất -->
                <CardBox class="lg:col-span-2">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">Thời gian</th>
                                    <th class="text-left">Sản phẩm</th>
                                    <th class="text-left">Loại</th>
                                    <th class="text-right">Số lượng</th>
                                    <th class="text-right">Tồn kho</th>
                                    <th class="text-left">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="movement in stockMovements.data" :key="movement.id"
                                    class="border-b border-gray-100 hover:bg-gray-50">
                                    <td>{{ formattedDate(movement.created_at) }}</td>
                                    <td>{{ movement.product.name }}</td>
                                    <td>
                                        <span class="px-2 py-1 rounded-full text-sm" :class="{
                                            'bg-green-100 text-green-800': movement.type === 'in',
                                            'bg-red-100 text-red-800': movement.type === 'out'
                                        }">
                                            {{ movement.type === 'in' ? 'Nhập' : 'Xuất' }}
                                        </span>
                                    </td>
                                    <td class="text-right">{{ movement.quantity }}</td>
                                    <td class="text-right">{{ movement.stock_after_movement }}</td>
                                    <td class="text-sm text-gray-600">{{ formatNote(movement.note) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Hiển thị {{ stockMovements.from }}-{{ stockMovements.to }}
                            trên tổng số {{ stockMovements.total }} bản ghi
                        </div>
                        <div class="flex space-x-2">
                            <BaseButton v-for="link in stockMovements.links" :key="link.label"
                                :color="link.active ? 'info' : 'white'" :disabled="!link.url"
                                @click="router.get(link.url)" :label="link.label" />
                        </div>
                    </div>
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
table th {
    @apply px-4 py-3 text-sm font-medium text-gray-600 bg-gray-50;
}

table td {
    @apply px-4 py-3;
}
</style>