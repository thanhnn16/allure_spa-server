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
import { mdiPlus, mdiMinus, mdiTableSearch } from '@mdi/js'

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

const errors = ref({})
const isLoading = ref(false)

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

const stockTypes = [
    { value: 'in', label: 'Nhập kho' },
    { value: 'out', label: 'Xuất kho' }
]

const formattedDate = (date) => {
    return new Date(date).toLocaleString('vi-VN')
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
                        <BaseButton color="info" :icon="mdiTableSearch" label="Xem báo cáo" 
                            @click="router.get(route('reports.stock'))" />
                    </BaseButtons>
                </div>
            </CardBox>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Form nhập/xuất kho -->
                <CardBox>
                    <form @submit.prevent="submitForm">
                        <FormField label="Sản phẩm" :error="errors.product_id">
                            <FormControl v-model="form.product_id" type="select" 
                                :options="products" option-label="name" option-value="id"
                                placeholder="Chọn sản phẩm" />
                        </FormField>

                        <FormField label="Loại" :error="errors.type">
                            <FormControl v-model="form.type" type="select" 
                                :options="stockTypes" />
                        </FormField>

                        <FormField label="Số lượng" :error="errors.quantity">
                            <FormControl v-model="form.quantity" type="number" min="1" />
                        </FormField>

                        <FormField label="Lý do" :error="errors.reason">
                            <FormControl v-model="form.reason" />
                        </FormField>

                        <FormField label="Số tham chiếu" :error="errors.reference_number">
                            <FormControl v-model="form.reference_number" />
                        </FormField>

                        <FormField label="Ghi chú" :error="errors.note">
                            <FormControl v-model="form.note" type="textarea" />
                        </FormField>

                        <BaseButtons>
                            <BaseButton type="submit" color="info" :loading="isLoading"
                                :icon="form.type === 'in' ? mdiPlus : mdiMinus"
                                :label="form.type === 'in' ? 'Nhập kho' : 'Xuất kho'" />
                        </BaseButtons>
                    </form>
                </CardBox>

                <!-- Lịch sử nhập xuất -->
                <CardBox>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">Thời gian</th>
                                    <th class="text-left">Sản phẩm</th>
                                    <th class="text-left">Loại</th>
                                    <th class="text-right">Số lượng</th>
                                    <th class="text-right">Tồn kho</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="movement in stockMovements.data" :key="movement.id">
                                    <td>{{ formattedDate(movement.created_at) }}</td>
                                    <td>{{ movement.product.name }}</td>
                                    <td>
                                        <span :class="{
                                            'text-green-600': movement.type === 'in',
                                            'text-red-600': movement.type === 'out'
                                        }">
                                            {{ movement.type === 'in' ? 'Nhập' : 'Xuất' }}
                                        </span>
                                    </td>
                                    <td class="text-right">{{ movement.quantity }}</td>
                                    <td class="text-right">{{ movement.stock_after_movement }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
table th,
table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

table tbody tr:hover {
    background-color: #f8fafc;
}
</style> 