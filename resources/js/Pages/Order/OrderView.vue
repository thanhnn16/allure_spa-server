<template>
  <LayoutAuthenticated>

    <Head title="Quản lý đơn hàng" />
    <SectionMain>
      <div class="container mx-auto px-4 py-8 dark:bg-dark-bg">
        <!-- Error message -->
        <div v-if="error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {{ error }}
        </div>

        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold">Quản lý đơn hàng</h1>
          <button @click="createNewOrder"
            class="bg-green-500 dark:bg-green-600 hover:bg-green-600 dark:hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors">
            Tạo đơn hàng mới
          </button>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="mb-6 flex flex-wrap items-center gap-4">
          <div class="flex items-center space-x-4 flex-grow">
            <select v-model="filters.status" @change="applyFilters"
              class="form-select rounded-md shadow-sm w-48 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text">
              <option value="">Tất cả trạng thái</option>
              <option value="pending">Chờ xác nhận</option>
              <option value="confirmed">Đã xác nhận</option>
              <option value="completed">Hoàn thành</option>
              <option value="cancelled">Đã hủy</option>
            </select>
            <input v-model="filters.search" @input="debounceSearch" type="text"
              placeholder="Tìm kiếm theo ID hoặc tên khách hàng"
              class="form-input rounded-md shadow-sm flex-grow dark:bg-dark-surface dark:border-dark-border dark:text-dark-text" />
          </div>
        </div>

        <!-- Bảng hiển thị đơn hàng -->
        <div v-if="orders?.data?.length > 0" class="overflow-x-auto bg-white dark:bg-dark-surface shadow-md rounded-lg">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-border">
            <thead>
              <tr>
                <th
                  class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Mã đơn hàng
                </th>
                <th
                  class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Khách hàng
                </th>
                <th
                  class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Tổng tiền
                </th>
                <th
                  class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Giảm giá
                </th>
                <th
                  class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Trạng thái
                </th>
                <th
                  class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Thanh toán
                </th>
                <th
                  class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Hành động
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-dark-surface divide-y divide-gray-200 dark:divide-dark-border">
              <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-dark-bg/50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-dark-text">
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
                  <span :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    getStatusClass(order.status)
                  ]">
                    <span :class="[
                      'h-2 w-2 rounded-full mr-2 self-center',
                      getStatusDotClass(order.status)
                    ]"></span>
                    {{ order.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <span v-if="order.invoice" :class="getPaymentStatusClass(order.invoice.status)">
                    {{ getPaymentStatusText(order.invoice.status) }}
                  </span>
                  <span v-else class="text-yellow-600">Chưa thanh toán</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <div class="flex space-x-2">
                    <!-- Nút Xem chi tiết -->
                    <button @click="viewOrderDetails(order.id)"
                      class="p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-all duration-200">
                      <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path :d="mdiEye" fill="currentColor" />
                      </svg>
                    </button>

                    <!-- Nút Chỉnh sửa -->
                    <Link :href="route('orders.edit', order.id)"
                      class="p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-all duration-200">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                      <path :d="mdiPencil" fill="currentColor" />
                    </svg>
                    </Link>

                    <!-- Nút Xóa -->
                    <button @click="deleteOrder(order.id)"
                      class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-all duration-200">
                      <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path :d="mdiDelete" fill="currentColor" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Phân trang -->
        <div v-if="orders?.links" class="mt-6">
          <Pagination :links="orders.links" class="mt-6" />
        </div>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<script>
import { Head, Link, router } from "@inertiajs/vue3";
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import Pagination from '@/Components/Pagination.vue'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios';
import { mdiEye, mdiPencil, mdiDelete } from '@mdi/js'

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

    // Add debounce search function
    const debounceSearch = () => {
      if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
      }
      searchTimeout.value = setTimeout(() => {
        applyFilters();
      }, 300);
    }

    // Add apply filters function
    const applyFilters = () => {
      router.get(
        route('orders.index'),
        {
          status: filters.value.status,
          search: filters.value.search,
        },
        {
          preserveState: true,
          preserveScroll: true,
          replace: true,
        }
      );
    }

    // Cleanup timeout on component unmount
    onBeforeUnmount(() => {
      if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
      }
    });

    const getStatusText = (status) => {
      switch (status) {
        case 'pending':
          return 'Chờ xác nhận';
        case 'confirmed':
          return 'Đã xác nhận';
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

    const deleteOrder = async (orderId) => {
      if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
        try {
          await axios.delete(`/api/orders/${orderId}`);
          // Refresh lại trang sau khi xóa
          router.reload();
        } catch (error) {
          console.error('Error deleting order:', error);
        }
      }
    }

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(amount);
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('vi-VN');
    }

    const getStatusClass = (status) => {
      const baseClasses = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full';
      const statusClasses = {
        'pending': `${baseClasses} dark:bg-yellow-900/50 dark:text-yellow-400 bg-yellow-100 text-yellow-800`,
        'confirmed': `${baseClasses} dark:bg-blue-900/50 dark:text-blue-400 bg-blue-100 text-blue-800`,
        'completed': `${baseClasses} dark:bg-green-900/50 dark:text-green-400 bg-green-100 text-green-800`,
        'cancelled': `${baseClasses} dark:bg-red-900/50 dark:text-red-400 bg-red-100 text-red-800`
      };
      return statusClasses[status.toLowerCase()] || `${baseClasses} dark:bg-gray-900/50 dark:text-gray-400 bg-gray-100 text-gray-800`;
    }

    const getStatusDotClass = (status) => {
      const dotClasses = {
        'pending': 'bg-yellow-400',
        'confirmed': 'bg-blue-400',
        'completed': 'bg-green-400',
        'cancelled': 'bg-red-400'
      };
      return dotClasses[status.toLowerCase()] || 'bg-gray-400';
    }

    const getPaymentStatusClass = (status) => {
      const statusClasses = {
        'pending': 'text-yellow-600',
        'partial': 'text-blue-600',
        'paid': 'text-green-600',
        'cancelled': 'text-red-600'
      };
      return statusClasses[status.toLowerCase()] || 'text-gray-600';
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
      deleteOrder,
      formatDate,
      mdiEye,
      mdiPencil,
      mdiDelete
    }
  },
}
</script>