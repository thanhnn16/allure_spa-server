<script setup>
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiArrowLeft, mdiPencil, mdiDelete, mdiEye } from '@mdi/js'
import axios from 'axios'

const props = defineProps({
    voucher: {
        type: Object,
        required: true
    },
    userVouchers: {
        type: Array,
        required: true
    },
    orders: {
        type: Array,
        required: true
    }
})

const toggleStatus = async (voucher) => {
    try {
        await axios.patch(route('vouchers.toggle-status', voucher.id))
        // Refresh data or update locally
    } catch (error) {
        console.error('Error toggling status:', error)
    }
}

const viewDetails = (id) => {
    // Navigate to details page
}

const editVoucher = (id) => {
    // Navigate to edit page
}
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Chi tiết Voucher" />

        <SectionMain
            :breadcrumbs="[{ label: 'Vouchers', href: route('vouchers.index') }, { label: 'Chi tiết Voucher', href: route('vouchers.show', voucher.id) }]">

            <!-- Thông tin cơ bản của voucher -->
            <CardBox class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Thông tin cơ bản</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="font-medium">Mã voucher:</p>
                        <p>{{ voucher.code }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Mô tả:</p>
                        <p>{{ voucher.description }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Loại giảm giá:</p>
                        <p>{{ voucher.discount_type === 'percentage' ? 'Phần trăm' : 'Số tiền cố định' }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Giá trị giảm:</p>
                        <p>{{ voucher.formatted_discount }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Đơn hàng tối thiểu:</p>
                        <p>{{ voucher.min_order_value_formatted }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Giảm tối đa:</p>
                        <p>{{ voucher.max_discount_amount_formatted }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Thời gian bắt đầu:</p>
                        <p>{{ voucher.start_date_formatted }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Thời gian kết thúc:</p>
                        <p>{{ voucher.end_date_formatted }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Trạng thái:</p>
                        <p>{{ voucher.status === 'active' ? 'Đang hoạt động' : 'Không hoạt động' }}</p>
                    </div>
                </div>
            </CardBox>

            <!-- Danh sách người dùng được cấp voucher -->
            <CardBox class="mb-6">
                <h2 class="text-xl font-semibold mb-4 dark:text-dark-text">Danh sách người dùng được cấp voucher</h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-dark-border">
                    <table class="w-full text-left table-auto text-sm">
                        <thead>
                            <tr
                                class="bg-gray-50 dark:bg-dark-surface border-b border-gray-200 dark:border-dark-border">
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Người dùng</th>
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Số lần còn lại</th>
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Tổng số lần</th>
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Ngày cấp</th>
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium w-24">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-dark-border">
                            <tr v-for="userVoucher in userVouchers" :key="userVoucher.id"
                                class="hover:bg-gray-50 dark:hover:bg-dark-hover transition-colors">
                                <td class="p-4 dark:text-dark-text">
                                    <div class="font-medium">{{ userVoucher.user.name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-dark-muted">
                                        {{ userVoucher.user.email }}
                                    </div>
                                </td>
                                <td class="p-4 dark:text-dark-text">
                                    <span class="px-2 py-1 rounded-full text-sm" :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': userVoucher.remaining_uses > 0,
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': userVoucher.remaining_uses === 0
                                    }">
                                        {{ userVoucher.remaining_uses }}
                                    </span>
                                </td>
                                <td class="p-4 dark:text-dark-text">{{ userVoucher.total_uses }}</td>
                                <td class="p-4 dark:text-dark-text">{{ userVoucher.created_at_formatted }}</td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <!-- Nút xem chi tiết -->
                                        <BaseButton :icon="mdiEye" color="info" :small="true"
                                            class="hover:bg-blue-100 dark:hover:bg-blue-900"
                                            @click="viewDetails(userVoucher.id)" />

                                        <!-- Nút chỉnh sửa -->
                                        <BaseButton :icon="mdiPencil" color="warning" :small="true"
                                            class="hover:bg-yellow-100 dark:hover:bg-yellow-900"
                                            @click="editVoucher(userVoucher.id)" />

                                        <!-- Toggle trạng thái -->
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                            <input type="checkbox" :id="`toggle-${userVoucher.id}`"
                                                :checked="userVoucher.status === 'active'"
                                                @change="toggleStatus(userVoucher)"
                                                class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out" />
                                            <label :for="`toggle-${userVoucher.id}`"
                                                class="toggle-label block overflow-hidden h-5 rounded-full cursor-pointer bg-gray-300 dark:bg-gray-700"></label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="userVouchers.length === 0">
                                <td colspan="4" class="p-8 text-center text-gray-500 dark:text-dark-muted">
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg mb-2">Chưa có dữ liệu</span>
                                        <span class="text-sm">Chưa có người dùng nào được cấp voucher này</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>

            <!-- Lịch sử sử dụng trong đơn hàng -->
            <CardBox>
                <h2 class="text-xl font-semibold mb-4 dark:text-dark-text">Lịch sử sử dụng trong đơn hàng</h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-dark-border">
                    <table class="w-full text-left table-auto">
                        <thead>
                            <tr
                                class="bg-gray-50 dark:bg-dark-surface border-b border-gray-200 dark:border-dark-border">
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Người dùng</th>
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Giá trị đơn hàng</th>
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Số tiền giảm</th>
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Trạng thái</th>
                                <th class="p-4 text-gray-700 dark:text-dark-text font-medium">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-dark-border">
                            <tr v-for="order in orders" :key="order.id"
                                class="hover:bg-gray-50 dark:hover:bg-dark-hover transition-colors">
                                <td class="p-4 dark:text-dark-text">
                                    <div class="font-medium">{{ order.user.name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-dark-muted">
                                        {{ order.user.email }}
                                    </div>
                                </td>
                                <td class="p-4 dark:text-dark-text font-medium">{{ order.total_amount_formatted }}</td>
                                <td class="p-4 dark:text-dark-text text-green-600 dark:text-green-400">
                                    -{{ order.discount_amount_formatted }}
                                </td>
                                <td class="p-4">
                                    <span :class="{
                                        'px-3 py-1 rounded-full text-sm': true,
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': order.status === 'pending',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': order.status === 'confirmed',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': order.status === 'shipping',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': order.status === 'completed',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': order.status === 'cancelled'
                                    }">
                                        {{ {
                                            'pending': 'Chờ xử lý',
                                            'confirmed': 'Đã xác nhận',
                                            'shipping': 'Đang giao hàng',
                                            'completed': 'Hoàn thành',
                                            'cancelled': 'Đã hủy'
                                        }[order.status] }}
                                    </span>
                                </td>
                                <td class="p-4 dark:text-dark-text">{{ order.created_at_formatted }}</td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td colspan="5" class="p-8 text-center text-gray-500 dark:text-dark-muted">
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg mb-2">Chưa có dữ liệu</span>
                                        <span class="text-sm">Chưa có đơn hàng nào sử dụng voucher này</span>
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

<style scoped>
.toggle-checkbox:checked {
    @apply right-0 border-green-500 dark:border-green-400;
}

.toggle-checkbox:checked+.toggle-label {
    @apply bg-green-500 dark:bg-green-400;
}

.toggle-checkbox {
    @apply right-5;
}

.toggle-label {
    @apply transition-colors duration-200 ease-in-out;
}
</style>