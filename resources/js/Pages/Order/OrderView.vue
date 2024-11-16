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
              <option value="shipping">Đang giao hàng</option>
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
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full items-center',
                    getStatusClass(order.status)
                  ]">
                    <span :class="[
                      'h-2 w-2 rounded-full mr-2 self-center',
                      getStatusDotClass(order.status)
                    ]"></span>
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

                    <!-- Nút Hủy -->
                    <button @click="openCancelModal(order.id)"
                      class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-all duration-200">
                      <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path :d="mdiClose" fill="currentColor" />
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
import { mdiEye, mdiPencil, mdiClose } from '@mdi/js'
import { useToast } from "vue-toastification"

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

    const toast = useToast()
    const showCancelModal = ref(false)
    const cancelReason = ref('')
    const selectedOrderId = ref(null)

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
      const statusTexts = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang giao hàng',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy'
      };
      return statusTexts[status.toLowerCase()] || 'Không xác định';
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

    const openCancelModal = (orderId) => {
      selectedOrderId.value = orderId
      cancelReason.value = ''
      showCancelModal.value = true
    }

    const cancelOrder = async () => {
      if (!cancelReason.value.trim()) {
        toast.error('Vui lòng nhập lý do hủy đơn hàng')
        return
      }

      try {
        await axios.put(`/api/orders/${selectedOrderId.value}/cancel`, {
          reason: cancelReason.value
        })
        
        toast.success('Đơn hàng đã được hủy thành công')
        showCancelModal.value = false
        router.reload()
      } catch (error) {
        console.error('Error cancelling order:', error)
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi hủy đơn hàng')
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
      const baseClasses = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full items-center transition-colors duration-150';
      const statusClasses = {
        'pending': `${baseClasses} bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300`,
        'confirmed': `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300`,
        'shipping': `${baseClasses} bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300`,
        'completed': `${baseClasses} bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300`,
        'cancelled': `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300`
      };
      return statusClasses[status.toLowerCase()] || `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300`;
    }

    const getStatusDotClass = (status) => {
      const dotClasses = {
        'pending': 'bg-yellow-400',
        'confirmed': 'bg-blue-400',
        'shipping': 'bg-purple-400',
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
      showCancelModal,
      cancelReason,
      openCancelModal,
      cancelOrder,
      formatDate,
      mdiEye,
      mdiPencil,
      mdiClose
    }
  },
}
</script>