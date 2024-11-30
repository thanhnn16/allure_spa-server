<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import { mdiPlus, mdiPencil, mdiDelete, mdiCheck, mdiClose, mdiEye } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseToggleButton from '@/Components/BaseToggleButton.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification';

const vouchers = ref([])
const loading = ref(true)
const toast = useToast()

const fetchVouchers = async () => {
    try {
        const response = await axios.get('/api/vouchers')
        vouchers.value = response.data.data
    } catch (error) {
        console.error('Error fetching vouchers:', error)
    } finally {
        loading.value = false
    }
}

const toggleVoucherStatus = async (voucher) => {
    try {
        await axios.patch(`/api/vouchers/${voucher.id}/toggle-status`)
        toast.success('Cập nhật trạng thái voucher thành công')
        await fetchVouchers() // Refresh data directly instead of full page reload
    } catch (error) {
        console.error('Error toggling voucher status:', error)
        toast.error('Có lỗi xảy ra khi cập nhật trạng thái voucher')
    }
}

const deleteVoucher = async (id) => {
    if (!confirm('Bạn có chắc chắn muốn xóa voucher này?')) {
        return
    }

    try {
        await axios.delete(`/api/vouchers/${id}`)
        await fetchVouchers() // Refresh list
    } catch (error) {
        console.error('Error deleting voucher:', error)
    }
}

onMounted(() => {
    fetchVouchers()
})

</script>

<template>
    <LayoutAuthenticated>

        <Head title="Quản lý Voucher" />

        <SectionMain>
            <CardBox class="mb-6 dark:bg-dark-surface">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold dark:text-dark-text">Quản lý Voucher</h1>
                    <BaseButton :icon="mdiPlus" color="info" label="Thêm Voucher"
                        @click="$inertia.visit(route('vouchers.create'))" />
                </div>
            </CardBox>

            <CardBox class="dark:bg-dark-surface">
                <div v-if="loading" class="text-center py-4 dark:text-dark-text">
                    Đang tải...
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left table-auto">
                        <thead>
                            <tr class="border-b dark:border-dark-border">
                                <th class="p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap">Mã</th>
                                <th class="p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap">Loại giảm giá
                                </th>
                                <th class="p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap">Giá trị</th>
                                <th class="p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap">Đơn tối thiểu
                                </th>
                                <th class="p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap">Giảm tối đa
                                </th>
                                <th class="p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap">Thời gian</th>
                                <th class="p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap">Trạng thái
                                </th>
                                <th class="p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="voucher in vouchers" :key="voucher.id"
                                class="border-b hover:bg-gray-50 dark:border-dark-border dark:hover:bg-dark-hover transition-colors duration-150 ease-in-out">
                                <td class="p-4 dark:text-dark-text">
                                    <div class="font-medium">{{ voucher.code }}</div>
                                    <div class="text-sm text-gray-500 dark:text-dark-muted mt-1">{{ voucher.description
                                        }}</div>
                                </td>
                                <td class="p-4 dark:text-dark-text whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="voucher.discount_type === 'percentage' ?
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'">
                                        {{ voucher.discount_type === 'percentage' ? 'Phần trăm' : 'Số tiền cố định' }}
                                    </span>
                                </td>
                                <td class="p-4 dark:text-dark-text font-medium">{{ voucher.formatted_discount }}</td>
                                <td class="p-4 dark:text-dark-text">{{ voucher.min_order_value_formatted }}</td>
                                <td class="p-4 dark:text-dark-text">{{ voucher.max_discount_amount_formatted }}</td>
                                <td class="p-4 dark:text-dark-text whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-500 dark:text-dark-muted">Từ:</span>
                                            <span>{{ voucher.start_date_formatted }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-500 dark:text-dark-muted">Đến:</span>
                                            <span>{{ voucher.end_date_formatted }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <BaseToggleButton :model-value="voucher.status === 'active'"
                                        @update:model-value="toggleVoucherStatus(voucher)" />
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <BaseButton :icon="mdiPencil" color="info" small class="!p-2"
                                            @click="$inertia.visit(route('vouchers.edit', voucher.id))" />
                                        <BaseButton :icon="mdiEye" color="success" small class="!p-2"
                                            @click="$inertia.visit(route('vouchers.show', voucher.id))" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>