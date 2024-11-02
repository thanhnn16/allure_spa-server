<template>
    <LayoutAuthenticated>

        <Head title="Tạo hóa đơn mới" />
        <SectionMain>
            <div class="container mx-auto px-4 py-8">
                <h1 class="text-3xl font-bold mb-6">Tạo hóa đơn mới</h1>
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- User Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Khách hàng
                        </label>
                        <div class="relative">
                            <input
                                v-model="userSearch"
                                @input="searchUsers"
                                type="text"
                                placeholder="Nhập tên hoặc số điện thoại khách hàng..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                            <!-- Dropdown kết quả tìm kiếm -->
                            <div v-if="userResults.length > 0" 
                                 class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200 max-h-60 overflow-y-auto">
                                <div v-for="user in userResults" 
                                     :key="user.id"
                                     @click="selectUser(user)"
                                     class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                                    <div class="font-medium">{{ user.full_name }}</div>
                                    <div class="text-sm text-gray-500">
                                        SĐT: {{ user.phone_number }}
                                        <span v-if="user.email" class="ml-2">Email: {{ user.email }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Sản phẩm/Dịch vụ</h3>
                        <button type="button" @click="addOrderItem"
                            class="mb-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Thêm sản phẩm/dịch vụ
                        </button>
                        <div v-for="(item, index) in form.order_items" :key="index" class="mb-4 p-4 border rounded-md">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Loại:</label>
                                    <select v-model="item.item_type" @change="searchItems(index)"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="product">Sản phẩm</option>
                                        <option value="service">Dịch vụ</option>
                                    </select>
                                </div>
                                <div v-if="item.item_type === 'service'">
                                    <label class="block text-sm font-medium text-gray-700">Loại dịch vụ:</label>
                                    <select v-model="item.service_type"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="single">Đơn lẻ</option>
                                        <option value="combo_5">Combo 5</option>
                                        <option value="combo_10">Combo 10</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Tìm kiếm:</label>
                                <input type="text" v-model="item.search" @input="searchItems(index)"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    :placeholder="item.item_type === 'product' ? 'Tìm sản phẩm' : 'Tìm liệu trình'" />
                                <ul v-if="item.searchResults.length > 0"
                                    class="mt-1 bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                    <li v-for="result in item.searchResults" :key="result.id"
                                        @click="selectItem(index, result)"
                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white">
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ result.name || result.service_name }}</span>
                                            <span v-if="result.item_type === 'service'" class="text-sm">
                                                Đơn lẻ: {{ formatCurrency(result.single_price) }}
                                                <br>
                                                Combo 5: {{ formatCurrency(result.combo_5_price) }}
                                                <br>
                                                Combo 10: {{ formatCurrency(result.combo_10_price) }}
                                            </span>
                                            <span v-else class="text-sm">
                                                {{ formatCurrency(result.price) }}
                                            </span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số lượng:</label>
                                    <input v-model.number="item.quantity" type="number" min="1"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Đơn giá:</label>
                                    <input
                                        :value="formatCurrency(item.price)"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                                        readonly
                                        :disabled="true"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Thành tiền:</label>
                                    <input :value="formatCurrency(item.quantity * item.price)" readonly
                                        class="mt-1 bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" />
                                </div>
                            </div>
                            <button type="button" @click="removeOrderItem(index)"
                                class="mt-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Xóa
                            </button>
                        </div>
                    </div>

                    <!-- Voucher -->
                    <div>
                        <label for="voucher" class="block text-sm font-medium text-gray-700">Voucher:</label>
                        <select v-model="form.voucher_id"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Phương thức thanh toán:</label>
                        <select v-model="form.payment_method_id" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option v-for="method in paymentMethods" :key="method.id" :value="method.id">
                                {{ method.method_name }}
                            </option>
                        </select>
                    </div>

                    <!-- Note -->
                    <div>
                        <label for="note" class="block text-sm font-medium text-gray-700">Ghi chú:</label>
                        <textarea v-model="form.note" rows="3"
                            class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Tạo hóa đơn
                    </button>
                </form>

                <!-- Xem trước hóa đơn -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold mb-4">Xem trước hóa đơn</h2>
                    <div v-if="form.user_id" class="bg-white shadow-lg rounded-lg overflow-hidden">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                            <h3 class="text-xl text-white font-semibold">Thông tin hóa đơn</h3>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 space-y-6">
                            <!-- Thông tin khách hàng -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-700 mb-2">Thông tin khách hàng</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Họ tên:</p>
                                        <p class="font-medium">{{ selectedCustomer?.full_name || 'Chưa chọn' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Số điện thoại:</p>
                                        <p class="font-medium">{{ selectedCustomer?.phone_number || 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Chi tiết đơn hàng -->
                            <div class="border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sản phẩm/Dịch vụ</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Đơn giá</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Số lượng</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in form.order_items" :key="item.item_id">
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ item.search }}</div>
                                                <div v-if="item.item_type === 'service'" class="text-xs text-gray-500">
                                                    {{ item.service_type === 'single' ? 'Đơn lẻ' : 
                                                       item.service_type === 'combo_5' ? 'Combo 5 lần' : 
                                                       item.service_type === 'combo_10' ? 'Combo 10 lần' : '' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm text-gray-500">
                                                {{ formatCurrency(item.price) }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm text-gray-500">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm text-gray-900 font-medium">
                                                {{ formatCurrency(item.quantity * item.price) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Tổng cộng -->
                            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tổng tiền:</span>
                                    <span class="font-medium">{{ formatCurrency(calculateTotal()) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Giảm giá:</span>
                                    <span class="font-medium text-red-600">-{{ formatCurrency(calculateDiscount()) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold pt-2 border-t">
                                    <span>Thành tiền:</span>
                                    <span class="text-indigo-600">{{ formatCurrency(calculateFinalTotal()) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<script>
import { Head } from "@inertiajs/vue3";
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { debounce } from 'lodash'
import { useForm, router } from '@inertiajs/vue3'  // Thay đ���i import
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
        const toast = useToast();

        const form = ref({
            user_id: '',
            voucher_id: null,
            payment_method_id: '',
            order_items: [],
            note: '',
            total_amount: 0,
            discount_amount: 0,
        })

        const userSearch = ref('')
        const userResults = ref([])
        const selectedUser = ref(null)

        // Thêm watch để debug giá trị tìm kiếm
        watch(userSearch, (newValue) => {
            console.log('userSearch changed:', newValue)
        })

        const searchUsers = debounce(async () => {
            console.log('Searching for:', userSearch.value)
            
            if (userSearch.value.length < 2) {
                console.log('Search term too short')
                userResults.value = []
                return
            }

            try {
                console.log('Making API call to /api/users/search')
                const response = await axios.get('/api/users/search', {
                    params: { query: userSearch.value }
                })
                console.log('API response:', response)
                
                userResults.value = response.data.data || []
                console.log('Updated userResults:', userResults.value)
            } catch (error) {
                console.error('Search error:', error)
                console.log('Error response:', error.response)
                userResults.value = []
            }
        }, 300)

        const selectUser = (user) => {
            console.log('Selected user:', user);
            form.value.user_id = user.id;
            selectedUser.value = user; // Thêm dòng này
            userSearch.value = user.full_name;
            userResults.value = [];
        }

        const addOrderItem = () => {
            form.value.order_items.push({
                item_type: 'product',
                item_id: null,
                service_type: 'single', // Mặc định là đơn lẻ
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
            const item = form.value.order_items[index];
            if (item.search.length < 2) {
                item.searchResults = [];
                return;
            }
            try {
                const endpoint = item.item_type === 'product' ? '/api/products/search' : '/api/services/search';
                const response = await axios.get(endpoint, {
                    params: { query: item.search }
                });
                // Thêm item_type vào kết quả tìm kiếm
                item.searchResults = response.data.data.map(result => ({
                    ...result,
                    item_type: item.item_type
                }));
            } catch (error) {
                console.error('Error searching items:', error);
                item.searchResults = [];
            }
        }

        const selectedItemPrice = (item) => {
            if (!item || !item.item_type || !item.service_type) return 0;
            
            if (item.item_type === 'service') {
                switch (item.service_type) {
                    case 'combo_5':
                        return item.selectedItem?.combo_5_price || 0;
                    case 'combo_10':
                        return item.selectedItem?.combo_10_price || 0;
                    default:
                        return item.selectedItem?.single_price || 0;
                }
            }
            return item.selectedItem?.price || 0;
        }

        const selectItem = (index, selectedItem) => {
            console.log('Selected item:', selectedItem);
            
            const item = form.value.order_items[index];
            item.selectedItem = selectedItem;
            item.item_id = selectedItem.id;
            item.search = selectedItem.name || selectedItem.service_name;
            
            if (selectedItem.item_type === 'service') {
                // Ensure service_type has a default value
                if (!item.service_type) {
                    item.service_type = 'single';
                }
                
                // Handle both price formats
                if (selectedItem.single_price !== undefined) {
                    // New price format
                    switch (item.service_type) {
                        case 'combo_5':
                            item.price = Number(selectedItem.combo_5_price);
                            break;
                        case 'combo_10':
                            item.price = Number(selectedItem.combo_10_price);
                            break;
                        default:
                            item.price = Number(selectedItem.single_price);
                            break;
                    }
                } else {
                    // Legacy price format
                    item.price = Number(selectedItem.price);
                }
            } else {
                item.price = Number(selectedItem.price);
            }
            
            console.log('Final price:', item.price);
            
            item.searchResults = [];
            updateTotals();
        }

        // Thêm watcher cho service_type
        watch(() => form.value.order_items, (items) => {
            items.forEach((item) => {
                if (item.selectedItem) {
                    if (item.item_type === 'service') {
                        if (item.selectedItem.single_price !== undefined) {
                            // New price format
                            switch (item.service_type) {
                                case 'combo_5':
                                    item.price = Number(item.selectedItem.combo_5_price);
                                    break;
                                case 'combo_10':
                                    item.price = Number(item.selectedItem.combo_10_price);
                                    break;
                                default:
                                    item.price = Number(item.selectedItem.single_price);
                                    break;
                            }
                        } else {
                            // Legacy price format
                            item.price = Number(item.selectedItem.price);
                        }
                        console.log('Updated price:', item.price);
                    }
                }
            });
            updateTotals();
        }, { deep: true });

        // Thêm watch cho item_type để reset các giá trị khi chuyển đổi loại
        watch(() => form.value.order_items, (items) => {
            items.forEach((item, index) => {
                // Tạo một bản sao của item_type để so sánh với giá trị trước đó
                const prevItemType = item._prevItemType;
                if (prevItemType && prevItemType !== item.item_type) {
                    // Reset các giá trị khi chuyển đổi loại
                    item.item_id = null;
                    item.price = 0;
                    item.search = '';
                    item.searchResults = [];
                    item.selectedItem = null;
                    item.service_type = item.item_type === 'service' ? 'single' : null;
                }
                // Lưu giá trị item_type hiện tại để so sánh trong lần thay đổi tiếp theo
                item._prevItemType = item.item_type;
            });
        }, { deep: true });

        // Cập nhật hm updateTotals
        const updateTotals = () => {
            const subtotal = form.value.order_items.reduce((total, item) => {
                return total + (item.quantity * item.price);
            }, 0);
            
            form.value.total_amount = subtotal;
            
            // Cập nhật discount nếu có voucher
            if (form.value.voucher_id) {
                const voucher = props.vouchers.find(v => v.id === form.value.voucher_id);
                if (voucher) {
                    form.value.discount_amount = voucher.discount_type === 'percentage' 
                        ? (subtotal * voucher.discount_value / 100)
                        : voucher.discount_value;
                }
            }
            
            // Cập nhật final total
            form.value.final_total = Math.max(0, subtotal - (form.value.discount_amount || 0));
        }

        const calculateTotal = () => {
            return form.value.order_items.reduce((total, item) => {
                const itemTotal = (item.quantity || 0) * (item.price || 0);
                return total + itemTotal;
            }, 0);
        }

        const calculateDiscount = () => {
            if (!form.value.voucher_id) return 0;
            
            const voucher = props.vouchers.find(v => v.id === form.value.voucher_id);
            if (!voucher) return 0;

            const total = calculateTotal();
            if (voucher.discount_type === 'percentage') {
                return total * (voucher.discount_value / 100);
            }
            return voucher.discount_value;
        }

        const calculateFinalTotal = () => {
            const total = calculateTotal();
            const discount = calculateDiscount();
            return Math.max(0, total - discount);
        }

        const formatCurrency = (value) => { 
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
        }

        const submitForm = async () => {
            try {
                form.value.total_amount = calculateFinalTotal()
                form.value.discount_amount = calculateDiscount()
                const response = await axios.post('/api/invoices', form.value)
                
                const paymentMethod = props.paymentMethods.find(
                    method => method.id === form.value.payment_method_id
                )
                
                // Kiểm tra nếu là phương thức chuyển khoản
                if (paymentMethod?.method_name.toLowerCase().includes('chuyển khoản')) {
                    // Gọi API để tạo payment link PayOS
                    try {
                        const payosResponse = await axios.post(`/api/invoices/${response.data.data.id}/pay-with-payos`)
                        if (payosResponse.data.success && payosResponse.data.checkoutUrl) {
                            // Chuyển hướng đến trang thanh toán PayOS
                            router.visit(payosResponse.data.checkoutUrl, {
                                method: 'get',
                                preserveState: false
                            })
                            return
                        }
                    } catch (payosError) {
                        console.error('PayOS payment error:', payosError)
                        toast.error('Không thể khởi tạo thanh toán PayOS')
                        return
                    }
                }
                
                // Nếu là thanh toán tiền mặt hoặc có lỗi với PayOS, chuyển đến trang chi tiết hóa đơn
                router.visit(`/invoices/${response.data.data.id}`, {
                    method: 'get',
                    preserveState: false
                })
                
                toast.success('Tạo hóa đơn thành công!')
                
            } catch (error) {
                console.error('Error creating invoice:', error)
                toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi tạo hóa đơn!')
            }
        }

        const selectedCustomer = computed(() => {
            return selectedUser.value;
        });

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
            selectedCustomer,
        }
    },
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
    background-color: #f9fafb;
    cursor: not-allowed;
    color: #374151;
}

input[readonly]:focus {
    border-color: #d1d5db;
    box-shadow: none;
}

/* Thêm style cho dropdown */
.absolute {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>













