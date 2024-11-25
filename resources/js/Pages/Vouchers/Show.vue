<script setup>
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiArrowLeft } from '@mdi/js'

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
                <h2 class="text-xl font-semibold mb-4">Danh sách người dùng được cấp voucher</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left table-auto">
                        <thead>
                            <tr class="border-b">
                                <th class="p-4">Người dùng</th>
                                <th class="p-4">Số lần còn lại</th>
                                <th class="p-4">Tổng số lần</th>
                                <th class="p-4">Ngày cấp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="userVoucher in userVouchers" :key="userVoucher.id" class="border-b">
                                <td class="p-4">
                                    {{ userVoucher.user.name }}
                                    <div class="text-sm text-gray-500">
                                        {{ userVoucher.user.email }}
                                    </div>
                                </td>
                                <td class="p-4">{{ userVoucher.remaining_uses }}</td>
                                <td class="p-4">{{ userVoucher.total_uses }}</td>
                                <td class="p-4">{{ userVoucher.created_at_formatted }}</td>
                            </tr>
                            <tr v-if="userVouchers.length === 0">
                                <td colspan="4" class="p-4 text-center text-gray-500">
                                    Chưa có người dùng nào được cấp voucher này
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>

            <!-- Lịch sử sử dụng trong đơn hàng -->
            <CardBox>
                <h2 class="text-xl font-semibold mb-4">Lịch sử sử dụng trong đơn hàng</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left table-auto">
                        <thead>
                            <tr class="border-b">
                                <th class="p-4">Người dùng</th>
                                <th class="p-4">Giá trị đơn hàng</th>
                                <th class="p-4">Số tiền giảm</th>
                                <th class="p-4">Trạng thái</th>
                                <th class="p-4">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders" :key="order.id" class="border-b">
                                <td class="p-4">
                                    {{ order.user.name }}
                                    <div class="text-sm text-gray-500">
                                        {{ order.user.email }}
                                    </div>
                                </td>
                                <td class="p-4">{{ order.total_amount_formatted }}</td>
                                <td class="p-4">{{ order.discount_amount_formatted }}</td>
                                <td class="p-4">
                                    <span :class="{
                                        'px-2 py-1 rounded text-sm': true,
                                        'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                        'bg-blue-100 text-blue-800': order.status === 'confirmed',
                                        'bg-purple-100 text-purple-800': order.status === 'shipping',
                                        'bg-green-100 text-green-800': order.status === 'completed',
                                        'bg-red-100 text-red-800': order.status === 'cancelled'
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
                                <td class="p-4">{{ order.created_at_formatted }}</td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td colspan="5" class="p-4 text-center text-gray-500">
                                    Chưa có đơn hàng nào sử dụng voucher này
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>