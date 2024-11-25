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
import { mdiPlus, mdiMinus, mdiTableSearch, mdiFileExcel, mdiFilter, mdiFilterOff } from '@mdi/js'
import { useToast } from "vue-toastification";

// Định nghĩa props
const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    stockMovements: {
        type: Object,
        default: () => ({
            data: [],
            from: 0,
            to: 0,
            total: 0,
            current_page: 1,
            last_page: 1,
            prev_page_url: null,
            next_page_url: null
        })
    }
})

const form = ref({
    product_id: '',
    quantity: '',
    type: { value: 'in', label: 'Nhập kho' },
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

const toast = useToast();

// Format sản phẩm để hiển thị
const formattedProducts = computed(() => {
    return props.products.map(product => ({
        ...product,
        label: `${product.name} (${formatPrice(product.price || 0)} - Tồn: ${product.quantity})`,
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

const submitForm = async (formType) => {
    isLoading.value = true
    try {
        const currentForm = formType === 'in' ? inForm.value : outForm.value
        const formData = {
            product_id: currentForm.product_id.value || currentForm.product_id,
            quantity: currentForm.quantity,
            type: currentForm.type.value,
            reason: currentForm.reason,
            reference_number: currentForm.reference_number,
            note: currentForm.note
        }

        router.post(route('stock-movements.store'), formData, {
            onSuccess: () => {
                toast.success('Tạo phiếu kho thành công');
                // Reset form
                if (formType === 'in') {
                    inForm.value = {
                        product_id: '',
                        quantity: '',
                        type: { value: 'in', label: 'Nhập kho' },
                        reason: '',
                        reference_number: '',
                        note: ''
                    };
                } else {
                    outForm.value = {
                        product_id: '',
                        quantity: '',
                        type: { value: 'out', label: 'Xuất kho' },
                        reason: '',
                        reference_number: '',
                        note: ''
                    };
                }
                // Tải lại trang
                router.visit(route('stock-movements.index'), {
                    preserveScroll: true,
                    preserveState: false
                });
            },
            onError: (errors) => {
                toast.error(errors.message || 'Có lỗi xảy ra');
            }
        });
    } catch (e) {
        toast.error(e.message || 'Có lỗi xảy ra');
    } finally {
        isLoading.value = false
    }
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

// Thêm ref cho việc hiển thị form
const showInForm = ref(true)
const showOutForm = ref(true)

// Tách form thành 2 form riêng biệt
const inForm = ref({
    product_id: '',
    quantity: '',
    type: { value: 'in', label: 'Nhập kho' },
    reason: '',
    reference_number: '',
    note: ''
})

const outForm = ref({
    product_id: '',
    quantity: '',
    type: { value: 'out', label: 'Xuất kho' },
    reason: '',
    reference_number: '',
    note: ''
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

            <!-- Form Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Nhập kho form -->
                <CardBox class="hover:shadow-lg transition-all">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Nhập kho</h2>
                            <p class="text-gray-600 text-sm">Tạo phiếu nhập kho mới</p>
                        </div>
                        <BaseButton @click="showInForm = !showInForm" :icon="showInForm ? mdiMinus : mdiPlus"
                            color="info" small />
                    </div>

                    <form v-show="showInForm" @submit.prevent="submitForm('in')" class="space-y-4">
                        <FormField label="Sản phẩm" :error="errors.product_id">
                            <FormControl v-model="inForm.product_id" type="select" :options="formattedProducts"
                                placeholder="Chọn sản phẩm cần nhập kho" />
                        </FormField>

                        <FormField label="Số lượng" :error="errors.quantity">
                            <FormControl v-model="inForm.quantity" type="number" min="1"
                                placeholder="Nhập số lượng sản phẩm" />
                        </FormField>

                        <FormField label="Lý do" :error="errors.reason">
                            <FormControl v-model="inForm.reason"
                                placeholder="VD: Nhập hàng từ nhà cung cấp, Điều chỉnh tồn kho..." />
                        </FormField>

                        <FormField label="Số tham chiếu" :error="errors.reference_number">
                            <FormControl v-model="inForm.reference_number"
                                placeholder="VD: Số hóa đơn, số PO, số phiếu nhập kho..." />
                        </FormField>

                        <FormField label="Ghi chú" :error="errors.note">
                            <FormControl v-model="inForm.note" type="textarea" rows="3"
                                placeholder="Thông tin bổ sung về đợt nhập kho này..." />
                        </FormField>

                        <BaseButtons>
                            <BaseButton type="submit" color="info" :loading="isLoading" :icon="mdiPlus"
                                label="Nhập kho" />
                        </BaseButtons>
                    </form>
                </CardBox>

                <!-- Xuất kho form -->
                <CardBox class="hover:shadow-lg transition-all">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Xuất kho</h2>
                            <p class="text-gray-600 text-sm">Tạo phiếu xuất kho mới</p>
                        </div>
                        <BaseButton @click="showOutForm = !showOutForm" :icon="showOutForm ? mdiMinus : mdiPlus"
                            color="danger" small />
                    </div>

                    <form v-show="showOutForm" @submit.prevent="submitForm('out')" class="space-y-4">
                        <FormField label="Sản phẩm" :error="errors.product_id">
                            <FormControl v-model="outForm.product_id" type="select" :options="formattedProducts"
                                placeholder="Chọn sản phẩm cần xuất kho" />
                        </FormField>

                        <FormField label="Số lượng" :error="errors.quantity">
                            <FormControl v-model="outForm.quantity" type="number" min="1"
                                placeholder="Nhập số lượng cần xuất" />
                        </FormField>

                        <FormField label="Lý do" :error="errors.reason">
                            <FormControl v-model="outForm.reason"
                                placeholder="VD: Xuất cho đơn hàng, Hàng hỏng, Điều chỉnh tồn..." />
                        </FormField>

                        <FormField label="Số tham chiếu" :error="errors.reference_number">
                            <FormControl v-model="outForm.reference_number"
                                placeholder="VD: Mã đơn hàng, số phiếu xuất kho..." />
                        </FormField>

                        <FormField label="Ghi chú" :error="errors.note">
                            <FormControl v-model="outForm.note" type="textarea" rows="3"
                                placeholder="Thông tin bổ sung về đợt xuất kho này..." />
                        </FormField>

                        <BaseButtons>
                            <BaseButton type="submit" color="danger" :loading="isLoading" :icon="mdiMinus"
                                label="Xuất kho" />
                        </BaseButtons>
                    </form>
                </CardBox>
            </div>

            <!-- Table Card - Full width -->
            <CardBox class="hover:shadow-lg transition-all">
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

                <!-- Sửa lại phần pagination -->
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Hiển thị {{ stockMovements.from || 0 }}-{{ stockMovements.to || 0 }}
                        trên tổng số {{ stockMovements.total || 0 }} bản ghi
                    </div>
                    <nav class="flex space-x-2">
                        <!-- Nút Trước -->
                        <BaseButton 
                            :disabled="!stockMovements.prev_page_url"
                            @click="router.get(stockMovements.prev_page_url)" 
                            color="white"
                            class="hover:shadow-md transition-all"
                        >
                            Trước
                        </BaseButton>

                        <!-- Các nút số trang -->
                        <template v-for="n in stockMovements.last_page" :key="n">
                            <BaseButton
                                v-if="n === 1 || n === stockMovements.last_page || 
                                     (n >= stockMovements.current_page - 1 && n <= stockMovements.current_page + 1)"
                                :color="n === stockMovements.current_page ? 'info' : 'white'"
                                @click="router.get(`${route('stock-movements.index')}?page=${n}`)"
                                class="hover:shadow-md transition-all"
                            >
                                {{ n }}
                            </BaseButton>
                            
                            <!-- Hiển thị dấu ... -->
                            <span v-else-if="n === stockMovements.current_page - 2 || n === stockMovements.current_page + 2"
                                  class="px-2 py-1">...</span>
                        </template>

                        <!-- Nút Sau -->
                        <BaseButton 
                            :disabled="!stockMovements.next_page_url"
                            @click="router.get(stockMovements.next_page_url)" 
                            color="white"
                            class="hover:shadow-md transition-all"
                        >
                            Sau
                        </BaseButton>
                    </nav>
                </div>
            </CardBox>
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