<template>
    <LayoutAuthenticated>

        <Head title="Tạo hóa đơn mới" />
        <SectionMain>
            <div class="container mx-auto px-4 py-8">
                <h1 class="text-3xl font-bold mb-6">Tạo hóa đơn mới</h1>
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Tìm kiếm khách hàng -->
                    <div>
                        <label for="user" class="block text-sm font-medium text-gray-700">Khách hàng:</label>
                        <div class="mt-1 relative">
                            <input type="text" v-model="userSearch" @input="searchUsers"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                placeholder="Tìm theo tên hoặc số điện thoại" />
                            <ul v-if="userResults.length > 0"
                                class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                <li v-for="user in userResults" :key="user.id" @click="selectUser(user)"
                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white">
                                    {{ user.full_name }} ({{ user.phone_number }})
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Voucher -->
                    <div>
                        <label for="voucher" class="block text-sm font-medium text-gray-700">Voucher:</label>
                        <select v-model="form.voucher_id"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Không áp dụng</option>
                            <option v-for="voucher in vouchers" :key="voucher.id" :value="voucher.id">
                                {{ voucher.code }} - {{ voucher.discount_value }}{{ voucher.discount_type ===
                                    'percentage' ? '%' : 'đ'
                                }}
                            </option>
                        </select>
                    </div>

                    <!-- Phương thức thanh toán -->
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Phương thức thanh
                            toán:</label>
                        <select v-model="form.payment_method_id" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option v-for="method in paymentMethods" :key="method.id" :value="method.id">
                                {{ method.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Danh sách sản phẩm/dịch vụ -->
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
                                        <option value="treatment">Liệu trình</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Danh mục:</label>
                                    <select v-model="item.category_id" @change="searchItems(index)"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option v-for="category in getCategoriesForType(item.item_type)"
                                            :key="category.id" :value="category.id">
                                            {{ category.category_name }}
                                        </option>
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
                                        {{ result.name }} - {{ formatCurrency(result.price) }}
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
                                    <input v-model.number="item.price" type="number" min="0"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" />
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

                    <!-- Ghi chú -->
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
                    <div v-if="form.user_id" class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Thông tin hóa đơn</h3>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                            <dl class="sm:divide-y sm:divide-gray-200">
                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Khách hàng</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ selectedUser ?
                                        selectedUser.full_name
                                        : ''
                                        }}</dd>
                                </div>
                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Tổng tiền</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{
                                        formatCurrency(calculateTotal()) }}
                                    </dd>
                                </div>
                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Giảm giá</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{
                                        formatCurrency(calculateDiscount()) }}
                                    </dd>
                                </div>
                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Thành tiền</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{
                                        formatCurrency(calculateFinalTotal())
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<script>
import { Head } from "@inertiajs/vue3";
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import { ref, computed } from 'vue'
import axios from 'axios'


export default {
    props: {
        vouchers: Array,
        paymentMethods: Array,
        productCategories: Array,
        treatmentCategories: Array,
    },
    setup(props) {
        const form = ref({
            user_id: '',
            voucher_id: null,
            payment_method_id: '',
            order_items: [],
            note: '',
        })

        const userSearch = ref('')
        const userResults = ref([])
        const selectedUser = ref(null)

        const searchUsers = async () => {
            if (userSearch.value.length < 2) return
            try {
                const response = await axios.get(`/api/users/search?query=${userSearch.value}`)
                userResults.value = response.data
            } catch (error) {
                console.error('Error searching users:', error)
            }
        }

        const selectUser = (user) => {
            form.value.user_id = user.id
            selectedUser.value = user
            userSearch.value = user.full_name
            userResults.value = []
        }

        const addOrderItem = () => {
            form.value.order_items.push({
                item_type: 'product',
                category_id: '',
                item_id: null,
                quantity: 1,
                price: 0,
                search: '',
                searchResults: [],
            })
        }

        const removeOrderItem = (index) => {
            form.value.order_items.splice(index, 1)
        }

        const searchItems = async (index) => {
            const item = form.value.order_items[index]
            if (item.search.length < 2) return
            try {
                const response = await axios.get(`/api/${item.item_type}s/search`, {
                    params: {
                        query: item.search,
                        category_id: item.category_id,
                    }
                })
                item.searchResults = response.data
            } catch (error) {
                console.error('Error searching items:', error)
            }
        }

        const selectItem = (index, item) => {
            const orderItem = form.value.order_items[index]
            orderItem.item_id = item.id
            orderItem.price = item.price
            orderItem.search = item.name
            orderItem.searchResults = []
        }

        const getCategoriesForType = (type) => {
            return type === 'product' ? props.productCategories : props.treatmentCategories
        }

        const calculateTotal = () => {
            return form.value.order_items.reduce((total, item) => total + (item.quantity * item.price), 0)
        }

        const calculateDiscount = () => {
            if (form.value.voucher_id) {
                const voucher = props.vouchers.find(v => v.id === form.value.voucher_id)
                if (voucher) {
                    if (voucher.discount_type === 'percentage') {
                        return calculateTotal() * (voucher.discount_value / 100)
                    } else {
                        return voucher.discount_value
                    }
                }
            }
            return 0
        }

        const calculateFinalTotal = () => {
            return calculateTotal() - calculateDiscount()
        }

        const formatCurrency = (value) => {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
        }

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
            getCategoriesForType,
            calculateTotal,
            calculateDiscount,
            calculateFinalTotal,
            formatCurrency,
        }
    },
}
</script>