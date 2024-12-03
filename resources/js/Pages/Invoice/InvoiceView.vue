<template>
  <LayoutAuthenticated>
    <Head title="Quản lý hóa đơn" />
    <SectionMain>
      <div class="container mx-auto px-4 py-8">
        <!-- Error message -->
        <div v-if="error" class="mb-4 bg-red-100 dark:bg-red-200 border border-red-400 text-red-700 dark:text-red-800 px-4 py-3 rounded">
          {{ error }}
        </div>

        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-dark-text">Quản lý hóa đơn</h1>
          <div class="space-x-4">
            <button @click="testPayOS" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
              Test PayOS (2,000 VNĐ)
            </button>
            <button @click="createNewInvoice"
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
              Tạo hóa đơn mới
            </button>
          </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="mb-6 flex flex-wrap items-center gap-4">
          <div class="flex items-center space-x-4 flex-grow">
            <select 
              v-model="filters.status" 
              @change="applyFilters"
              class="form-select rounded-md shadow-sm w-48 bg-white dark:bg-dark-surface text-gray-900 dark:text-dark-text"
            >
              <option value="">Tất cả trạng thái</option>
              <option value="pending">Chờ thanh toán</option>
              <option value="partial">Thanh toán một phần</option>
              <option value="paid">Đã thanh toán</option>
              <option value="cancelled">Đã hủy</option>
            </select>
            <input 
              v-model="filters.search" 
              @input="debounceSearch"
              type="text" 
              placeholder="Tìm kiếm theo ID hoặc tên khách hàng"
              class="form-input rounded-md shadow-sm flex-grow bg-white dark:bg-dark-surface text-gray-900 dark:text-dark-text" 
            />
          </div>
        </div>

        <!-- Bảng hiển thị hóa đơn -->
        <div v-if="invoices?.data?.length > 0" class="overflow-x-auto bg-white dark:bg-dark-surface shadow-md rounded-lg">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-border">
            <thead>
              <tr>
                <th class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider">
                  ID Hóa đơn
                </th>
                <th class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider">
                  Khách hàng
                </th>
                <th class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider">
                  Tổng tiền
                </th>
                <th class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider">
                  Đã thanh toán
                </th>
                <th class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider">
                  Còn lại
                </th>
                <th class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider">
                  Trạng thái
                </th>
                <th class="px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider">
                  Hành động
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-dark-surface divide-y divide-gray-200 dark:divide-dark-border">
              <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-dark-surface-hover">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-dark-text">
                  <span title="#{{ invoice.id }}" class="cursor-help">
                    #{{ truncateId(invoice.id) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-dark-text-muted">
                  {{ invoice.user.full_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-dark-text-muted">
                  {{ formatCurrency(invoice.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-dark-text-muted">
                  {{ formatCurrency(invoice.paid_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-dark-text-muted">
                  {{ formatCurrency(calculateRemainingAmount(invoice)) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(invoice.status)" class="flex items-center w-fit">
                    <span class="h-2 w-2 rounded-full mr-2" :class="getStatusDotClass(invoice.status)"></span>
                    {{ getStatusText(invoice.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button 
                    @click="viewInvoiceDetails(invoice.id)" 
                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-500 font-medium hover:underline">
                    Xem chi tiết
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="bg-white dark:bg-dark-surface shadow-md rounded-lg p-8 text-center">
          <p class="text-gray-600 dark:text-dark-text-muted text-lg">Chưa có dữ liệu hóa đơn.</p>
          <button @click="createNewInvoice"
            class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Tạo hóa đơn đầu tiên
          </button>
        </div>

        <!-- Phân trang -->
        <div v-if="invoices?.links" class="mt-6">
          <Pagination 
            :links="invoices.links" 
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
import axios from 'axios'

export default {
  components: {
    Head,
    LayoutAuthenticated,
    SectionMain,
    Pagination,
  },
  props: {
    invoices: {
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

    onBeforeUnmount(() => {
      if (searchTimeout.value) clearTimeout(searchTimeout.value)
    })

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    const getStatusClass = (status) => {
      switch (status) {
        case 'pending':
          return 'px-3 py-1 text-sm font-medium rounded-full bg-yellow-50 text-yellow-700';
        case 'partial':
          return 'px-3 py-1 text-sm font-medium rounded-full bg-blue-50 text-blue-700';
        case 'paid':
          return 'px-3 py-1 text-sm font-medium rounded-full bg-green-50 text-green-700';
        case 'cancelled':
          return 'px-3 py-1 text-sm font-medium rounded-full bg-red-50 text-red-700';
        default:
          return 'px-3 py-1 text-sm font-medium rounded-full bg-gray-50 text-gray-700';
      }
    }

    const getStatusDotClass = (status) => {
      switch (status) {
        case 'pending':
          return 'bg-yellow-400';
        case 'partial':
          return 'bg-blue-400';
        case 'paid':
          return 'bg-green-400';
        case 'cancelled':
          return 'bg-red-400';
        default:
          return 'bg-gray-400';
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

    const viewInvoiceDetails = (invoiceId) => {
      router.visit(`/invoices/${invoiceId}`);
    }

    const applyFilters = () => {
      router.get('/invoices', {
        status: filters.value.status,
        search: filters.value.search,
      }, {
        preserveState: true,
        preserveScroll: true,
        replace: true // Use replace to avoid adding to browser history
      })
    }

    const createNewInvoice = () => {
      router.visit('/invoices/create');
    }

    const calculateRemainingAmount = (invoice) => {
      return invoice.total_amount - invoice.paid_amount;
    }

    const testPayOS = async () => {
      try {
        // Get current origin for return URLs
        const origin = window.location.origin;
        
        const response = await axios.post('/api/payos/test', {
            amount: 2000,
            description: "Test PayOS payment",
            returnUrl: `${origin}/success`,
            cancelUrl: `${origin}/cancel`
        });
        
        if (response.data.success && response.data.checkoutUrl) {
            if (response.data.orderCode) {
                localStorage.setItem('payos_order_code', response.data.orderCode);
            }
            window.location.href = response.data.checkoutUrl;
        } else {
            throw new Error('Invalid PayOS response');
        }
      } catch (error) {
        console.error('PayOS test error:', error);
        console.error('Error response:', error.response?.data);
        
        let errorMessage = 'Error initiating PayOS payment';
        if (error.response?.data?.message) {
            errorMessage += `: ${error.response.data.message}`;
        }
        
        alert(errorMessage);
      }
    }

    const debounceSearch = () => {
      if (searchTimeout.value) clearTimeout(searchTimeout.value)
      searchTimeout.value = setTimeout(() => {
        applyFilters()
      }, 500) // Wait 500ms after user stops typing
    }

    const truncateId = (id) => {
      if (!id) return '';
      if (id.length <= 8) return id;
      return id.substring(0, 8) + '...';
    }

    return {
      filters,
      formatCurrency,
      getStatusClass,
      getStatusDotClass,
      getStatusText,
      viewInvoiceDetails,
      applyFilters,
      createNewInvoice,
      calculateRemainingAmount,
      testPayOS,
      debounceSearch,
      truncateId,
    }
  },
}
</script>

