<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import { mdiPlus, mdiPencil, mdiDelete, mdiCheck, mdiClose } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CardBox from '@/Components/CardBox.vue'
import axios from 'axios'

const vouchers = ref([])
const loading = ref(true)

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
        const response = await axios.patch(`/api/vouchers/${voucher.id}/toggle-status`)
        const index = vouchers.value.findIndex(v => v.id === voucher.id)
        vouchers.value[index] = response.data.data
    } catch (error) {
        console.error('Error toggling voucher status:', error)
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

const breadcrumbs = [
    { label: 'Trang chủ', route: 'dashboard' },
    { label: 'Quản lý Voucher' }
]
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Quản lý Voucher" />

        <SectionMain :breadcrumbs="breadcrumbs">
            <CardBox class="mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Quản lý Voucher</h1>
                    <BaseButton :icon="mdiPlus" color="info" label="Thêm Voucher"
                        @click="$inertia.visit(route('vouchers.create'))" />
                </div>
            </CardBox>

            <CardBox>
                <div v-if="loading" class="text-center py-4">
                    Đang tải...
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left table-auto">
                        <thead>
                            <tr class="border-b">
                                <th class="p-4">Mã</th>
                                <th class="p-4">Loại giảm giá</th>
                                <th class="p-4">Giá trị</th>
                                <th class="p-4">Đơn tối thiểu</th>
                                <th class="p-4">Giảm tối đa</th>
                                <th class="p-4">Thời gian</th>
                                <th class="p-4">Trạng thái</th>
                                <th class="p-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="voucher in vouchers" :key="voucher.id" class="border-b hover:bg-gray-50">
                                <td class="p-4">
                                    {{ voucher.code }}
                                    <div class="text-sm text-gray-500">{{ voucher.description }}</div>
                                </td>
                                <td class="p-4">
                                    {{ voucher.discount_type === 'percentage' ? 'Phần trăm' : 'Số tiền cố định' }}
                                </td>
                                <td class="p-4">{{ voucher.formatted_discount }}</td>
                                <td class="p-4">{{ voucher.min_order_value_formatted }}</td>
                                <td class="p-4">{{ voucher.max_discount_amount_formatted }}</td>
                                <td class="p-4">
                                    <div>Từ: {{ voucher.start_date_formatted }}</div>
                                    <div>Đến: {{ voucher.end_date_formatted }}</div>
                                </td>
                                <td class="p-4">
                                    <BaseButton :color="voucher.status === 'active' ? 'success' : 'danger'"
                                        :icon="voucher.status === 'active' ? mdiCheck : mdiClose"
                                        :label="voucher.status === 'active' ? 'Hoạt động' : 'Không hoạt động'" small
                                        @click="toggleVoucherStatus(voucher)" />
                                </td>
                                <td class="p-4">
                                    <BaseButtons>
                                        <BaseButton :icon="mdiPencil" color="info" small
                                            @click="$inertia.visit(route('vouchers.edit', voucher.id))" />
                                        <BaseButton :icon="mdiDelete" color="danger" small
                                            @click="deleteVoucher(voucher.id)" />
                                    </BaseButtons>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>