<template>
    <div>
        <!-- Main content with layout -->
        <LayoutAuthenticated>
            <Head title="Chi tiết hóa đơn" />
            <SectionMain>
                <div class="container mx-auto px-4 py-8">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-semibold">Chi tiết hóa đơn #{{ invoice.id }}</h1>
                        <div class="flex space-x-4">
                            <button @click="printInvoice" 
                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded flex items-center">
                                <i class="fas fa-print mr-2"></i> In hóa đơn
                            </button>
                            <!-- Thêm nút hủy hóa đơn -->
                            <button v-if="canCancel"
                                @click="confirmCancelInvoice"
                                class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded flex items-center">
                                <i class="fas fa-times mr-2"></i> Hủy hóa đơn
                            </button>
                            <Link :href="route('invoices.index')" 
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                                Quay lại
                            </Link>
                        </div>
                    </div>

                    <!-- Status and Payment Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Status Card -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-4">Trạng thái hóa đơn</h2>
                            <div class="flex items-center space-x-2">
                                <span :class="getStatusClass(invoice.status)" class="px-3 py-1 rounded-full text-sm">
                                    {{ getStatusText(invoice.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Payment Summary -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-4">Thông tin thanh toán</h2>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Tổng tiền hàng:</span>
                                    <span class="font-medium">{{ formatCurrency(subtotalAmount) }}</span>
                                </div>
                                <!-- Update Voucher section -->
                                <div v-if="invoice.order?.voucher_id" class="flex flex-col space-y-1">
                                    <div class="flex justify-between text-green-600">
                                        <span class="flex items-center">
                                            <i class="fas fa-ticket-alt mr-2"></i>
                                            Voucher áp dụng:
                                        </span>
                                        <span class="font-medium">{{ getVoucherInfo }}</span>
                                    </div>
                                    <div class="flex justify-between text-green-600">
                                        <span>Giảm giá:</span>
                                        <span class="font-medium">-{{ formatCurrency(invoice.order.discount_amount) }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between font-bold border-t border-gray-200 pt-2">
                                    <span>Tổng tiền sau giảm giá:</span>
                                    <span>{{ formatCurrency(invoice.total_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Đã thanh toán:</span>
                                    <span class="text-green-600 font-medium">{{ formatCurrency(invoice.paid_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Còn lại:</span>
                                    <span class="text-red-600 font-medium">{{ formatCurrency(invoice.remaining_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thêm phần Invoice Details sau Status and Payment Info -->
                    <div class="bg-white p-6 rounded-lg shadow mb-8">
                        <h2 class="text-lg font-semibold mb-4">Thông tin chi tiết</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Thông tin hóa đơn -->
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Mã hóa đơn:</span>
                                    <span class="font-medium">#{{ invoice.id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ngày tạo:</span>
                                    <span class="font-medium">{{ formatDateTime(invoice.created_at) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Người tạo:</span>
                                    <span class="font-medium">{{ invoice.created_by?.full_name || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ghi chú:</span>
                                    <span class="font-medium">{{ invoice.note || '-' }}</span>
                                </div>
                            </div>

                            <!-- Thông tin khách hàng -->
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Khách hàng:</span>
                                    <span class="font-medium">{{ invoice.user?.full_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Số điện thoại:</span>
                                    <span class="font-medium">{{ invoice.user?.phone_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium">{{ invoice.user?.email || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Địa chỉ:</span>
                                    <span class="font-medium">{{ invoice.user?.address || '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thêm phần Order Items -->
                    <div class="bg-white p-6 rounded-lg shadow mb-8">
                        <h2 class="text-lg font-semibold mb-4">Chi tiết đơn hàng</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            STT
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sản phẩm/Dịch vụ
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Đơn giá
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Số lượng
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thành tiền
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ghi chú
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in invoice.order?.items" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ getItemName(item) }}
                                            </div>
                                            <div v-if="item.description" class="text-sm text-gray-500">
                                                {{ item.description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                            {{ formatCurrency(Number(item.price) || 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                            {{ Number(item.quantity) || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                            {{ formatCurrency((Number(item.price) || 0) * (Number(item.quantity) || 0)) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="item.service_type" 
                                                  :class="getServiceTypeClass(item.service_type)">
                                                {{ formatServiceType(item.service_type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    
                                    <!-- Thêm dòng tổng cộng -->
                                    <tr class="bg-gray-50">
                                        <td colspan="4" class="px-6 py-4 text-right font-medium">
                                            Tổng tiền hàng:
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium">
                                            {{ formatCurrency(subtotalAmount) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                    <!-- Hiển thị voucher nếu có -->
                                    <tr v-if="invoice.order?.discount_amount" class="bg-gray-50 text-green-600">
                                        <td colspan="4" class="px-6 py-4 text-right font-medium">
                                            Giảm giá:
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium">
                                            -{{ formatCurrency(invoice.order.discount_amount) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                    <!-- Tổng tiền sau giảm giá -->
                                    <tr class="bg-gray-100 font-bold">
                                        <td colspan="4" class="px-6 py-4 text-right">
                                            Tổng tiền thanh toán:
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            {{ formatCurrency(invoice.total_amount) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div v-if="showPaymentForm" class="bg-white p-6 rounded-lg shadow mb-8">
                        <h2 class="text-lg font-semibold mb-4">Thanh toán</h2>
                        <form @submit.prevent="processPayment" class="space-y-4">
                            <!-- Payment Type Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hình thức thanh toán</label>
                                <div class="flex space-x-4">
                                    <button 
                                        type="button"
                                        @click="selectPaymentType('full')"
                                        :class="[
                                            'px-4 py-2 rounded-md',
                                            paymentType === 'full' 
                                                ? 'bg-blue-500 text-white' 
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        ]"
                                    >
                                        Thanh toán tất cả ({{ formatCurrency(invoice.remaining_amount) }})
                                    </button>
                                    <button 
                                        type="button"
                                        @click="selectPaymentType('partial')"
                                        :class="[
                                            'px-4 py-2 rounded-md',
                                            paymentType === 'partial' 
                                                ? 'bg-blue-500 text-white' 
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        ]"
                                    >
                                        Thanh toán một phần
                                    </button>
                                </div>
                            </div>

                            <!-- Amount Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Số tiền thanh toán</label>
                                <input 
                                    type="number" 
                                    v-model="paymentAmount"
                                    :max="invoice.remaining_amount"
                                    :readonly="paymentType === 'full'"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                <p class="mt-1 text-sm text-gray-500">
                                    Số tiền còn lại cần thanh toán: {{ formatCurrency(invoice.remaining_amount) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phương thức thanh toán</label>
                                <select 
                                    v-model="paymentMethod"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="cash">Tiền mặt</option>
                                    <option value="bank_transfer">Chuyển khoản</option>
                                    <option value="card">Thẻ</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                                <textarea 
                                    v-model="paymentNote"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    rows="2"
                                ></textarea>
                            </div>
                            <button 
                                type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded"
                                :disabled="processing"
                            >
                                {{ processing ? 'Đang xử lý...' : 'Xác nhận thanh toán' }}
                            </button>
                        </form>
                    </div>

                    <!-- Payment History -->
                    <div v-if="invoice.payment_histories?.length > 0" class="bg-white p-6 rounded-lg shadow mb-8">
                        <h2 class="text-lg font-semibold mb-4">Lịch sử thanh toán</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thời gian
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Số tiền
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Phương thức
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Trạng thái cũ
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Trạng thái mới
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Người thực hiện
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ghi chú
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="history in invoice.payment_histories" :key="history.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDateTime(history.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ formatCurrency(history.payment_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ getPaymentMethodText(history.payment_method) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(history.old_payment_status)">
                                                {{ getStatusText(history.old_payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(history.new_payment_status)">
                                                {{ getStatusText(history.new_payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ history.created_by?.full_name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ history.note || '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </SectionMain>
        </LayoutAuthenticated>

        <!-- Print Template -->
        <div class="print-only" ref="printSection">
            <PrintInvoiceTemplate 
                :invoice="invoice"
            />
        </div>

        <!-- Thêm Toast component -->
        <Toast ref="toast" />

        <!-- Thêm modal xác nhận hủy hóa đơn -->
        <div v-if="showCancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                <h3 class="text-lg font-semibold mb-4">Xác nhận hủy hóa đơn</h3>
                <p class="mb-4">Bạn có chắc chắn muốn hủy hóa đơn này? Hành động này không thể hoàn tác.</p>
                <div class="flex justify-end space-x-4">
                    <button @click="showCancelModal = false" 
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">
                        Đóng
                    </button>
                    <button @click="cancelInvoice" 
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">
                        Xác nhận hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import PrintInvoiceTemplate from '@/Components/PrintInvoiceTemplate.vue'
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import { useToast } from "vue-toastification" // Import useToast

export default {
    components: {
        Head,
        Link,
        LayoutAuthenticated,
        SectionMain,
        PrintInvoiceTemplate,
    },
    
    props: {
        invoice: {
            type: Object,
            required: true
        }
    },

    setup(props) {
        const paymentAmount = ref(0)
        const paymentMethod = ref('cash')
        const paymentNote = ref('')
        const processing = ref(false)
        const paymentType = ref('partial')
        const toast = useToast()

        // Thêm console.log để kiểm tra toàn bộ invoice object
        console.log('Full Invoice Object:', props.invoice);
        
        // Kiểm tra cụ thể phần voucher
        console.log('Voucher Data:', props.invoice.voucher);
        
        // Kiểm tra số tiền giảm giá
        console.log('Discount Amount:', props.invoice.discount_amount);

        // Watch for payment type changes
        watch(paymentType, (newType) => {
            if (newType === 'full') {
                paymentAmount.value = props.invoice.remaining_amount
            } else {
                paymentAmount.value = 0
            }
        })

        // Watch for payment amount to ensure it doesn't exceed remaining amount
        watch(paymentAmount, (newAmount) => {
            if (newAmount > props.invoice.remaining_amount) {
                paymentAmount.value = props.invoice.remaining_amount
            }
        })

        const selectPaymentType = (type) => {
            paymentType.value = type
            if (type === 'full') {
                paymentAmount.value = props.invoice.remaining_amount
            } else {
                paymentAmount.value = 0
            }
        }

        const getPaymentMethodText = (method) => {
            const methods = {
                'cash': 'Tiền mặt',
                'bank_transfer': 'Chuyển khoản',
                'card': 'Thẻ'
            }
            return methods[method] || method
        }

        const printInvoice = () => {
            // Thêm một chút delay để đảm bảo template được render
            setTimeout(() => {
                window.print()
            }, 100)
        }

        const processPayment = async () => {
            processing.value = true
            try {
                const response = await axios.post(`/invoices/${props.invoice.id}/process-payment`, {
                    payment_amount: paymentAmount.value,
                    payment_method: paymentMethod.value,
                    note: paymentNote.value,
                    payment_proof: null
                })
                
                // Hiển thị toast thành công
                toast.success("Đã cập nhật thanh toán cho hóa đơn")

                // Kiểm tra nếu đã thanh toán đủ
                if (response.data.data.status === 'paid') {
                    toast.success("Hóa đơn đã được thanh toán đầy đủ")
                }

                // Sử dụng router của Inertia để reload trang
                router.reload({ only: ['invoice'] })

                // Reset form
                paymentAmount.value = 0
                paymentNote.value = ''
                
            } catch (error) {
                console.error('Payment processing error:', error)
                toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi xử lý thanh toán')
            } finally {
                processing.value = false
            }
        }

        const calculateSubtotal = () => {
            if (!props.invoice?.order?.items?.length) return 0;
            
            return props.invoice.order.items.reduce((sum, item) => {
                const price = Number(item.price) || 0;
                const quantity = Number(item.quantity) || 0;
                return sum + (price * quantity);
            }, 0);
        }

        // Computed property để theo dõi thay đổi của invoice
        const subtotalAmount = computed(() => {
            if (props.invoice?.order?.total_amount && props.invoice?.order?.discount_amount) {
                return Number(props.invoice.order.total_amount) + Number(props.invoice.order.discount_amount);
            }
            
            // Nếu không có thông tin giảm giá, tính từ items
            return calculateSubtotal();
        });

        const formatCurrency = (amount) => {
            // Đảm bảo amount là số
            const numAmount = Number(amount);
            if (isNaN(numAmount)) return '0 ₫';
            
            return new Intl.NumberFormat('vi-VN', { 
                style: 'currency', 
                currency: 'VND' 
            }).format(numAmount);
        }

        const formatDateTime = (datetime) => {
            return new Date(datetime).toLocaleString('vi-VN');
        }

        const getStatusClass = (status) => {
            switch (status) {
                case 'pending':
                    return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
                case 'partial':
                    return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800';
                case 'paid':
                    return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
                case 'cancelled':
                    return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
                default:
                    return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
            }
        }

        const getStatusText = (status) => {
            switch (status) {
                case 'pending':
                    return 'Chờ thanh toán';
                case 'partial':
                    return 'Thanh toán một phần';
                case 'paid':
                    return 'Đã thanh toán';
                case 'cancelled':
                    return 'Đã hủy';
                default:
                    return 'Không xác định';
            }
        }

        // Add computed property for showing payment form
        const showPaymentForm = computed(() => {
            return props.invoice.status !== 'paid' && props.invoice.status !== 'cancelled';
        });

        // Update watch for payment amount
        watch(paymentAmount, (newAmount) => {
            if (newAmount > props.invoice.remaining_amount) {
                paymentAmount.value = props.invoice.remaining_amount;
                toast.warning('Số tiền không thể lớn hơn số tiền cần thanh toán');
            }
        });

        const getItemName = (item) => {
            if (item.item_type === 'service') {
                return item.service?.service_name || 'Dịch vụ không xác định';
            } else if (item.item_type === 'product') {
                return item.product?.name || 'Sản phẩm không xác định';
            }
            return 'Không xác định';
        }

        const formatServiceType = (type) => {
            const types = {
                'single': 'Đơn lẻ',
                'combo_5': 'Combo 5 lần',
                'combo_10': 'Combo 10 lần'
            };
            return types[type] || type;
        }

        const getServiceTypeClass = (type) => {
            const classes = {
                'single': 'px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800',
                'combo_5': 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800',
                'combo_10': 'px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800'
            };
            return classes[type] || '';
        }

        const getVoucherDescription = (voucher) => {
            if (!voucher) return '';
            
            if (voucher.discount_type === 'percentage') {
                return `Giảm ${voucher.discount_value}%`;
            } else if (voucher.discount_type === 'fixed') {
                return `Giảm ${formatCurrency(voucher.discount_value)}`;
            }
            
            return voucher.description || '';
        }

        const getDetailedVoucherDescription = (voucher) => {
            // Log chi tiết khi hàm được gọi
            console.log('Voucher in getDetailedVoucherDescription:', voucher);
            
            if (!voucher) {
                console.log('No voucher provided');
                return '';
            }
            
            let description = '';
            
            // Log các giá trị được sử dụng
            console.log('Discount Type:', voucher.discount_type);
            console.log('Discount Value:', voucher.discount_value);
            console.log('Min Order Amount:', voucher.min_order_amount);
            console.log('Start Date:', voucher.start_date);
            console.log('End Date:', voucher.end_date);
            
            if (voucher.discount_type === 'percentage') {
                description = `Giảm ${voucher.discount_value}% `;
            } else if (voucher.discount_type === 'fixed') {
                description = `Giảm ${formatCurrency(voucher.discount_value)} `;
            }
            
            if (voucher.min_order_amount) {
                description += `cho đơn hàng từ ${formatCurrency(voucher.min_order_amount)}`;
            }
            
            if (voucher.start_date && voucher.end_date) {
                description += ` (Có hiệu lực: ${formatDateTime(voucher.start_date)} - ${formatDateTime(voucher.end_date)})`;
            }
            
            // Log kết quả cuối cùng
            console.log('Final Description:', description);
            
            return description || voucher.description || '';
        }

        // Add new computed property for voucher info
        const getVoucherInfo = computed(() => {
            const order = props.invoice.order;
            if (!order?.voucher_id) return '';

            const discountAmount = Number(order.discount_amount);
            const totalAmount = Number(subtotalAmount.value);
            
            if (discountAmount && totalAmount) {
                const percentage = ((discountAmount / totalAmount) * 100).toFixed(1);
                return `Giảm ${percentage}% (${formatCurrency(discountAmount)})`;
            }
            
            return `Giảm ${formatCurrency(order.discount_amount)}`;
        });

        // Trong phần setup
        const showCancelModal = ref(false)

        // Thêm computed property để kiểm tra có thể hủy hay không
        const canCancel = computed(() => {
            return props.invoice.status === 'pending' || props.invoice.status === 'partial';
        })

        // Thêm methods
        const confirmCancelInvoice = () => {
            showCancelModal.value = true
        }

        const cancelInvoice = async () => {
            try {
                const response = await axios.post(`/invoices/${props.invoice.id}/cancel`)
                
                // Hiển thị thông báo thành công
                toast.success("Hóa đơn đã được hủy thành công")
                
                // Reload trang để cập nhật dữ liệu
                router.reload({ only: ['invoice'] })
                
                // Đóng modal
                showCancelModal.value = false
            } catch (error) {
                console.error('Cancel invoice error:', error)
                toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi hủy hóa đơn')
            }
        }

        return {
            paymentAmount,
            paymentMethod,
            paymentNote,
            processing,
            paymentType,
            selectPaymentType,
            getPaymentMethodText,
            toast,
            formatCurrency,
            formatDateTime,
            getStatusClass,
            getStatusText,
            processPayment,
            printInvoice,
            showPaymentForm,
            getItemName,
            formatServiceType,
            getServiceTypeClass,
            getVoucherDescription,
            subtotalAmount,
            getDetailedVoucherDescription,
            getVoucherInfo,
            showCancelModal,
            canCancel,
            confirmCancelInvoice,
            cancelInvoice,
        }
    }
}
</script>

<style>
/* Screen styles */
@media screen {
    .print-only {
        display: none;
    }
}

/* Print styles */
@media print {
    /* Hide everything except print template */
    body * {
        visibility: hidden;
    }
    
    .print-only,
    .print-only * {
        visibility: visible !important;
        display: block !important;
    }

    .print-only {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    /* Reset page margins */
    @page {
        margin: 0;
        size: A4;
    }
}
</style>

