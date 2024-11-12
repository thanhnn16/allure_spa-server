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
        label: `${product.name} (${formatPrice(product.price)} - Tồn: ${product.quantity})`,
        value: product.id
    }))
})

const formatPrice = (price) => {
    if (!price || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const submitForm = async () => {
    isLoading.value = true
    try {
        const formData = {
            product_id: form.value.product_id,
            quantity: form.value.quantity,
            type: form.value.type,
            reason: form.value.reason,
            reference_number: form.value.reference_number,
            note: form.value.note
        }
        await router.post(route('stock-movements.store'), formData)
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

// Thêm state cho search
const searchQuery = ref('')

// Thêm computed cho filtered data
const filteredMovements = computed(() => {
    if (!props.stockMovements.data) return []

    return props.stockMovements.data.filter(movement => {
        const searchLower = searchQuery.value.toLowerCase()
        return movement.product.name.toLowerCase().includes(searchLower) ||
            movement.note?.toLowerCase().includes(searchLower) ||
            movement.reference_number?.toLowerCase().includes(searchLower)
    })
})
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Quản lý kho" />

        <SectionMain>
            <!-- Header Card -->
            <CardBox class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Quản lý nhập xuất kho</h1>
                        <p class="text-gray-600 mt-1">Theo dõi và quản lý các hoạt động nhập xuất kho</p>
                    </div>
                    <BaseButtons>
                        <BaseButton color="info" :icon="showFilters ? mdiFilterOff : mdiFilter"
                            @click="showFilters = !showFilters" class="hover:shadow-lg transition-all" label="Bộ lọc" />
                        <BaseButton color="success" :icon="mdiFileExcel" @click="exportData"
                            class="hover:shadow-lg transition-all" label="Xuất Excel" />
                        <BaseButton color="info" :icon="mdiTableSearch" @click="router.get(route('reports.stock'))"
                            class="hover:shadow-lg transition-all" label="Báo cáo" />
                    </BaseButtons>
                </div>

                <!-- Bộ lọc nâng cao -->
                <div v-show="showFilters" class="mt-6 p-4 bg-white rounded-lg border border-gray-100 shadow-sm">
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
                <!-- Form Card -->
                <CardBox class="hover:shadow-lg transition-all">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Thêm giao dịch mới</h2>
                        <p class="text-gray-600 text-sm">Nhập thông tin để tạo giao dịch nhập/xuất kho</p>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <FormField label="Sản phẩm" :error="errors.product_id">
                            <FormControl v-model="form.product_id" type="select" :options="formattedProducts"
                                value-prop="id" placeholder="Chọn sản phẩm" />
                        </FormField>

                        <div class="grid grid-cols-2 gap-4">
                            <FormField label="Loại" :error="errors.type">
                                <FormControl v-model="form.type" type="select" :options="stockTypes"
                                    value-prop="value" />
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

                <!-- Table Card -->
                <CardBox class="lg:col-span-2 hover:shadow-lg transition-all">
                    <div class="mb-4">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">Lịch sử giao dịch</h2>
                            <!-- Search input -->
                            <div class="relative">
                                <input v-model="searchQuery" type="text" placeholder="Tìm kiếm..."
                                    class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <span class="absolute left-3 top-2.5 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

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
                                <template v-if="filteredMovements.length">
                                    <tr v-for="movement in filteredMovements" :key="movement.id"
                                        class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
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
                                </template>
                                <tr v-else>
                                    <td colspan="6" class="py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p class="font-medium">Không có dữ liệu</p>
                                            <p class="text-sm">Chưa có giao dịch nào được thực hiện</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Hiển thị {{ stockMovements.from || 0 }}-{{ stockMovements.to || 0 }}
                            trên tổng số {{ stockMovements.total || 0 }} bản ghi
                        </div>
                        <div class="flex space-x-2">
                            <BaseButton v-for="link in stockMovements.links" :key="link.label"
                                :color="link.active ? 'info' : 'white'" :disabled="!link.url"
                                @click="router.get(link.url)" :label="link.label"
                                class="hover:shadow-md transition-all" />
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

.hover-shadow {
    @apply transition-shadow duration-300 hover:shadow-lg;
}
</style>