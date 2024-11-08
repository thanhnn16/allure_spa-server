<template>
  <LayoutAuthenticated>
    <Head title="Quản lý đơn hàng" />
    <SectionMain>
      <div class="container mx-auto px-4 py-8">
        <!-- Error message -->
        <div v-if="error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {{ error }}
        </div>

        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold">Quản lý đơn hàng</h1>
          <button @click="createNewOrder"
                  class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
            Tạo đơn hàng mới
          </button>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="mb-6 flex flex-wrap items-center gap-4">
          <div class="flex items-center space-x-4 flex-grow">
            <select 
              v-model="filters.status" 
              @change="applyFilters"
              class="form-select rounded-md shadow-sm w-48"
            >
              <option value="">Tất cả trạng thái</option>
              <option value="pending">Chờ xử lý</option>
              <option value="processing">Đang xử lý</option>
              <option value="completed">Hoàn thành</option>
              <option value="cancelled">Đã hủy</option>
            </select>
            <input 
              v-model="filters.search" 
              @input="debounceSearch"
              type="text" 
              placeholder="Tìm kiếm theo ID hoặc tên khách hàng"
              class="form-input rounded-md shadow-sm flex-grow" 
            />
          </div>
        </div>

        <!-- Bảng hiển thị đơn hàng -->
        <div v-if="orders?.data?.length > 0" class="overflow-x-auto bg-white shadow-md rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-6 py-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Mã đơn hàng
                </th>
                <th class="px-6 py-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Khách hàng
                </th>
                <th class="px-6 py-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tổng tiền
                </th>
                <th class="px-6 py-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Giảm giá
                </th>
                <th class="px-6 py-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Trạng thái
                </th>
                <th class="px-6 py-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Thanh toán
                </th>
                <th class="px-6 py-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Hành động
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  #{{ order.id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ order.user.full_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatCurrency(order.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatCurrency(order.discount_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(order.status)" class="flex items-center w-fit">
                    <span class="h-2 w-2 rounded-full mr-2" :class="getStatusDotClass(order.status)"></span>
                    {{ getStatusText(order.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <span v-if="order.invoice" :class="getPaymentStatusClass(order.invoice.status)">
                    {{ getPaymentStatusText(order.invoice.status) }}
                  </span>
                  <span v-else class="text-yellow-600">Chưa thanh toán</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button 
                    @click="viewOrderDetails(order.id)" 
                    class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline">
                    Xem chi tiết
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Phân trang -->
        <div v-if="orders?.links" class="mt-6">
          <Pagination 
            :links="orders.links" 
            class="mt-6"
          />
        </div>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<script>
import { Head, router } from "@inertiajs/vue3";
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import Pagination from '@/Components/Pagination.vue'
import { ref, onMounted, onBeforeUnmount } from 'vue'

export default {
  components: {
    Head,
    LayoutAuthenticated,
    SectionMain,
    Pagination,
  },
  props: {
    orders: {
      type: Object,
      required: true,
      default: () => ({
        data: [],
        links: [],
        meta: {}
      })
    },
    error: {
      type: String,
      default: null
    }
  },
  setup(props) {
    const filters = ref({
      status: '',
      search: '',
    })

    const searchTimeout = ref(null)

    // ... các hàm tiện ích giữ nguyên, chỉ đổi tên từ invoice sang order ...

    const getStatusText = (status) => {
      switch (status) {
        case 'pending':
          return 'Chờ xử lý';
        case 'processing':
          return 'Đang xử lý';
        case 'completed':
          return 'Hoàn thành';
        case 'cancelled':
          return 'Đã hủy';
        default:
          return 'Không xác định';
      }
    }

    const getPaymentStatusText = (status) => {
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

    const viewOrderDetails = (orderId) => {
      router.visit(`/orders/${orderId}`);
    }

    const createNewOrder = () => {
      router.visit('/orders/create');
    }

    return {
      filters,
      formatCurrency,
      getStatusClass,
      getStatusDotClass,
      getStatusText,
      getPaymentStatusText,
      getPaymentStatusClass,
      viewOrderDetails,
      createNewOrder,
      applyFilters,
      debounceSearch,
    }
  },
}
</script> 