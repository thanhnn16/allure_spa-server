<template>
    <LayoutAuthenticated>

        <Head title="Tạo đơn hàng mới" />
        <SectionMain>
            <div class="container mx-auto px-4 py-8">
                <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-dark-text">Tạo đơn hàng mới</h1>
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- User Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-dark-text-secondary mb-1">
                            Khách hàng
                        </label>
                        <div class="relative">
                            <input v-model="userSearch" @input="searchUsers" type="text"
                                placeholder="Nhập tên hoặc số điện thoại khách hàng..."
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border bg-white dark:bg-dark-surface text-gray-900 dark:text-dark-text shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            <!-- Dropdown kết quả tìm kiếm -->
                            <div v-if="userResults.length > 0"
                                class="absolute z-50 w-full mt-1 bg-white dark:bg-dark-surface rounded-md shadow-lg border border-gray-200 dark:border-dark-border">
                                <div v-for="user in userResults" :key="user.id" @click="selectUser(user)"
                                    class="p-3 hover:bg-gray-50 dark:hover:bg-dark-surface-hover cursor-pointer border-b border-gray-100 dark:border-dark-border last:border-0">
                                    <div class="font-medium text-gray-900 dark:text-dark-text">{{ user.full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-dark-text-muted">
                                        SĐT: {{ user.phone_number }}
                                        <span v-if="user.email" class="ml-2">Email: {{ user.email }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-dark-text mb-2">Sản phẩm/Dịch vụ</h3>
                        <button type="button" @click="addOrderItem"
                            class="mb-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-dark-bg">
                            Thêm sản phẩm/dịch vụ
                        </button>
                        <div v-for="(item, index) in form.order_items" :key="index"
                            class="mb-4 p-4 border rounded-md border-gray-200 dark:border-dark-border bg-white dark:bg-dark-surface">
                            <!-- Item Type Selection -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Loại:</label>
                                    <select v-model="item.item_type" @change="searchItems(index)"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300">
                                        <option value="product">Sản phẩm</option>
                                        <option value="service">Dịch vụ</option>
                                    </select>
                                </div>
                                <div v-if="item.item_type === 'service'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Loại dịch
                                        vụ:</label>
                                    <select v-model="item.service_type"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300">
                                        <option value="single">Đơn lẻ</option>
                                        <option value="combo_5">Combo 5</option>
                                        <option value="combo_10">Combo 10</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Item Search -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tìm
                                    kiếm:</label>
                                <input type="text" v-model="item.search" @input="searchItems(index)"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"
                                    :placeholder="item.item_type === 'product' ? 'Tìm sản phẩm' : 'Tìm liệu trình'" />
                                <ul v-if="item.searchResults.length > 0"
                                    class="mt-1 bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border shadow-lg max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                    <li v-for="result in item.searchResults" :key="result.id"
                                        @click="selectItem(index, result)"
                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-primary-50 dark:hover:bg-dark-surface-hover">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900 dark:text-dark-text">
                                                {{ result.name || result.service_name }}
                                            </span>
                                            <span v-if="result.item_type === 'service'"
                                                class="text-sm text-gray-600 dark:text-dark-text-muted">
                                                Đơn lẻ: {{ formatCurrency(result.single_price) }}
                                                <br>
                                                Combo 5: {{ formatCurrency(result.combo_5_price) }}
                                                <br>
                                                Combo 10: {{ formatCurrency(result.combo_10_price) }}
                                            </span>
                                            <span v-else class="text-sm text-gray-600 dark:text-dark-text-muted">
                                                {{ formatCurrency(result.price) }}
                                            </span>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <!-- Quantity and Price -->
                            <div class="mt-4 grid grid-cols-3 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-dark-text-secondary">Số
                                        lượng:</label>
                                    <input v-model.number="item.quantity" type="number" min="1"
                                        class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-dark-border rounded-md dark:bg-dark-surface dark:text-dark-text" />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-dark-text-secondary">Đơn
                                        giá:</label>
                                    <input :value="formatCurrency(item.price)" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border shadow-sm sm:text-sm 
                                        bg-white dark:bg-dark-surface
                                        disabled:bg-gray-50 dark:disabled:bg-dark-surface 
                                        disabled:text-gray-700 dark:disabled:text-dark-text
                                        disabled:border-gray-300 dark:disabled:border-dark-border
                                        disabled:cursor-not-allowed" readonly />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-dark-text-secondary">Thành
                                        tiền:</label>
                                    <input :value="formatCurrency(item.quantity * item.price)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border shadow-sm sm:text-sm 
                                        bg-white dark:bg-dark-surface
                                        disabled:bg-gray-50 dark:disabled:bg-dark-surface 
                                        disabled:text-gray-700 dark:disabled:text-dark-text
                                        disabled:border-gray-300 dark:disabled:border-dark-border
                                        disabled:cursor-not-allowed" readonly />
                                </div>
                            </div>

                            <button type="button" @click="removeOrderItem(index)"
                                class="mt-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Xóa
                            </button>
                        </div>
                    </div>

                    <!-- Voucher Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Voucher:</label>
                        <select v-model="form.voucher_id"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300">
                            <option value="">Không áp dụng</option>
                            <option v-for="voucher in vouchers" :key="voucher.id" :value="voucher.id">
                                {{ voucher.code }} - {{ voucher.discount_type === 'percentage' ?
                                    `${voucher.discount_value}%` :
                                    formatCurrency(voucher.discount_value) }}
                            </option>
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phương thức thanh
                            toán:</label>
                        <select v-model="form.payment_method_id" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300">
                            <option v-for="method in paymentMethods" :key="method.id" :value="method.id">
                                {{ method.method_name }}
                            </option>
                        </select>
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ghi chú:</label>
                        <textarea v-model="form.note" rows="3"
                            class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center">
                        <button type="submit"
                            :disabled="isProcessing"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white 
                            bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                            disabled:opacity-50 disabled:cursor-not-allowed transition-opacity duration-200">
                            <template v-if="isProcessing">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.payment_method_id === 3 ? 'Đang tạo link thanh toán...' : 'Đang xử lý...' }}
                            </template>
                            <template v-else>
                                {{ form.payment_method_id === 3 ? 'Tạo đơn và thanh toán PayOS' : 'Tạo đơn hàng và hóa đơn' }}
                            </template>
                        </button>
                        <div class="text-right">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tổng tiền: {{
                                formatCurrency(calculateTotal()) }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400">Giảm giá: -{{
                                formatCurrency(calculateDiscount()) }}</p>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">Thành tiền: {{
                                formatCurrency(calculateFinalTotal()) }}</p>
                        </div>
                    </div>
                </form>
            </div>
        </SectionMain>

        <!-- Thêm overlay loading khi đang xử lý -->
        <div v-if="isProcessing" 
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-dark-surface p-6 rounded-lg shadow-xl text-center">
                <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-900 dark:text-dark-text font-medium">
                    {{ form.payment_method_id === 3 ? 'Đang tạo link thanh toán...' : 'Đang xử lý đơn hàng...' }}
                </p>
                <p class="text-sm text-gray-500 dark:text-dark-text-muted mt-2">
                    Vui lòng đợi trong giây lát
                </p>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<script>
import { Head, router } from "@inertiajs/vue3";
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { debounce } from 'lodash'
import { useToast } from 'vue-toastification'

export default {
    components: {
        Head,
        SectionMain,
        LayoutAuthenticated
    },

    props: {
        vouchers: Array,
        paymentMethods: Array,
    },

    setup(props) {
        const toast = useToast()

        // Form data
        const form = ref({
            user_id: '',
            voucher_id: null,
            payment_method_id: '',
            order_items: [],
            note: '',
            total_amount: 0,
            discount_amount: 0,
        })

        // User search
        const userSearch = ref('')
        const userResults = ref([])
        const selectedUser = ref(null)

        // Methods
        const searchUsers = debounce(async () => {
            if (userSearch.value.length < 2) {
                userResults.value = []
                return
            }
            try {
                const response = await axios.get('/api/users/search', {
                    params: { query: userSearch.value }
                })
                userResults.value = response.data.data
            } catch (error) {
                console.error('Search error:', error)
                userResults.value = []
            }
        }, 300)

        const selectUser = (user) => {
            form.value.user_id = user.id
            selectedUser.value = user
            userSearch.value = user.full_name
            userResults.value = []
        }

        // Order items management
        const addOrderItem = () => {
            form.value.order_items.push({
                item_type: 'product',
                item_id: null,
                service_type: 'single',
                quantity: 1,
                price: 0,
                search: '',
                searchResults: [],
                selectedItem: null
            })
        }

        const removeOrderItem = (index) => {
            form.value.order_items.splice(index, 1)
        }

        const searchItems = async (index) => {
            const item = form.value.order_items[index]
            if (item.search.length < 2) {
                item.searchResults = []
                return
            }
            try {
                const endpoint = item.item_type === 'product' ? '/api/products/search' : '/api/services/search'
                const response = await axios.get(endpoint, {
                    params: { query: item.search }
                })
                item.searchResults = response.data.data.map(result => ({
                    ...result,
                    item_type: item.item_type
                }))
            } catch (error) {
                console.error('Error searching items:', error)
                item.searchResults = []
            }
        }

        const selectItem = (index, selectedItem) => {
            const item = form.value.order_items[index]
            item.selectedItem = selectedItem
            item.item_id = selectedItem.id
            item.search = selectedItem.name || selectedItem.service_name

            if (selectedItem.item_type === 'service') {
                updateServicePrice(item)
            } else {
                item.price = Number(selectedItem.price)
            }

            item.searchResults = []
            updateTotals()
        }

        // Calculations
        const calculateTotal = () => {
            return form.value.order_items.reduce((total, item) => {
                return total + (item.quantity * item.price)
            }, 0)
        }

        const calculateDiscount = () => {
            if (!form.value.voucher_id) return 0

            const voucher = props.vouchers.find(v => v.id === form.value.voucher_id)
            if (!voucher) return 0

            const total = calculateTotal()
            return voucher.discount_type === 'percentage'
                ? total * (voucher.discount_value / 100)
                : voucher.discount_value
        }

        const calculateFinalTotal = () => {
            return Math.max(0, calculateTotal() - calculateDiscount())
        }

        const updateTotals = () => {
            form.value.total_amount = calculateFinalTotal()
            form.value.discount_amount = calculateDiscount()
        }

        // Form submission
        const isProcessing = ref(false)

        const submitForm = async () => {
            try {
                // Thêm flag xử lý
                isProcessing.value = true

                console.log('Bắt đầu submit form với dữ liệu:', form.value);

                // Validate form
                if (!form.value.user_id) {
                    toast.error('Vui lòng chọn khách hàng');
                    return;
                }

                if (form.value.order_items.length === 0) {
                    toast.error('Vui lòng thêm ít nhất một sản phẩm hoặc dịch vụ');
                    return;
                }

                // Cập nhật tổng tiền
                form.value.total_amount = calculateTotal();
                form.value.discount_amount = calculateDiscount();

                console.log('Tổng tiền đã tính:', {
                    total: form.value.total_amount,
                    discount: form.value.discount_amount
                });

                // Tạo đơn hàng
                console.log('Gọi API tạo đơn hàng');
                const response = await axios.post('/api/orders', form.value);
                console.log('Response tạo đơn hàng:', response.data);

                const order = response.data.data;

                // Xử lý thanh toán qua PayOS nếu được chọn
                if (form.value.payment_method_id === 3) {
                    console.log('Phương thức thanh toán PayOS được chọn');
                    await handlePayOSPayment(order);
                } else {
                    console.log('Chuyển hướng đến trang chi tiết đơn hàng');
                    router.visit(`/orders/${order.id}`);
                }

            } catch (error) {
                console.error('Chi tiết lỗi tạo đơn hàng:', {
                    message: error.message,
                    response: error.response?.data,
                    stack: error.stack
                });
                toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi tạo đơn hàng');
            } finally {
                isProcessing.value = false
            }
        }

        // Formatting
        const formatCurrency = (value) => {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(value)
        }

        const canUpdateStatus = computed(() => {
            return ['pending', 'confirmed', 'shipping', 'delivered'].includes(props.order.status)
        })

        // Thêm watch để theo dõi thay đổi service_type
        watch(() => form.value.order_items, (items) => {
            items.forEach((item) => {
                if (item.selectedItem && item.item_type === 'service') {
                    updateServicePrice(item);
                }
            });
        }, { deep: true });

        // Hàm mới để cập nhật giá dịch vụ
        const updateServicePrice = (item) => {
            if (!item.selectedItem) return;

            switch (item.service_type) {
                case 'combo_5':
                    item.price = Number(item.selectedItem.combo_5_price || 0);
                    break;
                case 'combo_10':
                    item.price = Number(item.selectedItem.combo_10_price || 0);
                    break;
                default: // 'single'
                    item.price = Number(item.selectedItem.single_price || 0);
                    break;
            }
            updateTotals();
        }

        // Thêm watch cho service_type
        watch(() => form.value.order_items.map(item => item.service_type), () => {
            form.value.order_items.forEach(item => {
                if (item.item_type === 'service' && item.selectedItem) {
                    updateServicePrice(item);
                }
            });
        }, { deep: true });

        const handlePayOSPayment = async (order) => {
            try {
                const returnUrl = `${window.location.origin}/payment/callback`;
                const cancelUrl = `${window.location.origin}/payment/callback?status=cancel`;

                const response = await axios.post(`/api/orders/${order.id}/payment-link`, {
                    returnUrl,
                    cancelUrl
                });

                if (response.data.success && response.data.data.checkoutUrl) {
                    window.location.href = response.data.data.checkoutUrl;
                } else {
                    throw new Error(response.data.message || 'Không thể tạo link thanh toán');
                }
            } catch (error) {
                toast.error('Lỗi xử lý thanh toán: ' + (error.response?.data?.message || error.message));
                router.visit(`/orders/${order.id}`);
            }
        };

        const handlePaymentCallback = async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const orderCode = urlParams.get('orderCode');

            if (!orderCode) {
                toast.error('Thiếu thông tin đơn hàng');
                return;
            }

            try {
                // Verify payment với PayOS
                const response = await axios.post('/api/payment/verify', {
                    orderCode
                });

                if (response.data.success) {
                    toast.success('Thanh toán thành công');
                    // Chuyển về trang chi tiết đơn hàng
                    router.visit(`/orders/${response.data.data.order_id}`);
                } else {
                    toast.error('Thanh toán thất bại');
                    router.visit('/orders');
                }
            } catch (error) {
                console.error('Payment verification error:', error);
                toast.error('Lỗi xác thực thanh toán');
            }
        };

        // Thêm computed property để tính toán số tiền sau giảm giá
        const finalAmount = computed(() => {
            return form.value.total_amount - form.value.discount_amount;
        });

        // Cập nhật hàm tính toán khi áp dụng voucher
        const applyVoucher = async (voucherId) => {
            try {
                form.value.voucher_id = voucherId;

                // Tính toán lại giá trị giảm giá dựa trên voucher
                if (voucherId) {
                    const voucher = props.vouchers.find(v => v.id === voucherId);
                    if (voucher) {
                        if (voucher.discount_type === 'percentage') {
                            form.value.discount_amount = (form.value.total_amount * voucher.discount_value) / 100;
                        } else {
                            form.value.discount_amount = voucher.discount_value;
                        }
                    }
                } else {
                    form.value.discount_amount = 0;
                }

                // Cập nhật tổng tiền và số tiền giảm giá
                updateTotals();
            } catch (error) {
                toast.error('Lỗi khi áp dụng voucher: ' + error.message);
            }
        };

        return {
            form,
            userSearch,
            userResults,
            selectedUser,
            searchUsers,
            selectUser,
            addOrderItem,
            removeOrderItem,
            searchItems,
            selectItem,
            calculateTotal,
            calculateDiscount,
            calculateFinalTotal,
            formatCurrency,
            submitForm,
            canUpdateStatus,
            updateServicePrice,
            handlePayOSPayment,
            handlePaymentCallback,
            finalAmount,
            applyVoucher,
            updateTotals,
            isProcessing,
        }
    }
}
</script>

<style scoped>
.form-input[readonly] {
    background-color: #f3f4f6;
    cursor: not-allowed;
}

.form-group {
    margin-bottom: 1rem;
}

input[readonly] {
    @apply bg-gray-50 dark:bg-dark-surface;
    cursor: not-allowed;
    @apply text-gray-700 dark:text-dark-text;
}

input[readonly]:focus {
    @apply border-gray-300 dark:border-dark-border;
    box-shadow: none;
}

.absolute {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>