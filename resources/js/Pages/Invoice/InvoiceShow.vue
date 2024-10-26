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
                                    <span>Tổng tiền:</span>
                                    <span class="font-medium">{{ formatCurrency(invoice.total_amount) }}</span>
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
                                            Trạng thái cũ
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Trạng thái mới
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="history in invoice.payment_histories" :key="history.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDateTime(history.updated_at) }}
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
            window.print()
        }

        const processPayment = async () => {
            processing.value = true
            try {
                const response = await axios.post(`/invoices/${props.invoice.id}/process-payment`, {
                    payment_amount: paymentAmount.value,
                    payment_method: paymentMethod.value,
                    payment_note: paymentNote.value
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

        // Add the missing utility functions
        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('vi-VN', { 
                style: 'currency', 
                currency: 'VND' 
            }).format(amount);
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
            showPaymentForm
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
    .screen-only {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
        width: 100%;
        background: white;
    }

    /* Reset page margins */
    @page {
        margin: 15mm;
        size: A4;
    }

    /* Ensure all content is visible */
    body {
        visibility: hidden;
    }
    
    .print-only {
        visibility: visible;
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>

