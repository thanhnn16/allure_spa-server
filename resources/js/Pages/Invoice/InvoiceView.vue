<template>
  <LayoutAuthenticated>
    <Head title="Quản lý hóa đơn" />
    <SectionMain>
      <div class="container mx-auto px-4 py-8">
        <!-- Error message -->
        <div v-if="error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {{ error }}
        </div>

        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold">Quản lý hóa đơn</h1>
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
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center space-x-4">
            <select v-model="filters.status" class="form-select rounded-md shadow-sm">
              <option value="">Tất cả trạng thái</option>
              <option value="pending">Chờ thanh toán</option>
              <option value="partial">Thanh toán một phần</option>
              <option value="paid">Đã thanh toán</option>
              <option value="cancelled">Đã hủy</option>
            </select>
            <input v-model="filters.search" type="text" placeholder="Tìm kiếm theo ID hoặc tên khách hàng"
              class="form-input rounded-md shadow-sm" />
          </div>
          <button @click="applyFilters" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Áp dụng bộ lọc
          </button>
        </div>

        <!-- Bảng hiển thị hóa đơn -->
        <div v-if="invoices?.data?.length > 0" class="overflow-x-auto bg-white shadow-md rounded-lg">
          <table class="min-w-full leading-normal">
            <thead>
              <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  ID Hóa đơn
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Khách hàng
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Tổng tiền
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Đã thanh toán
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Còn lại
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Trạng thái
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Hành động
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="invoice in invoices.data" :key="invoice.id">
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  {{ invoice.id }}
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  {{ invoice.user.full_name }}
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  {{ formatCurrency(invoice.total_amount) }}
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  {{ formatCurrency(invoice.paid_amount) }}
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  {{ formatCurrency(calculateRemainingAmount(invoice)) }}
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  <span :class="getStatusClass(invoice.status)">
                    {{ getStatusText(invoice.status) }}
                  </span>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  <button @click="viewInvoiceDetails(invoice.id)" class="text-blue-600 hover:text-blue-900">
                    Xem chi tiết
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="bg-white shadow-md rounded-lg p-8 text-center">
          <p class="text-gray-600 text-lg">Chưa có dữ liệu hóa đơn.</p>
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
import { ref, onMounted } from 'vue'
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

    onMounted(() => {
      console.log('Invoices data:', props.invoices)
      if (props.error) {
        console.error('Error:', props.error)
      }
    })

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
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
      });
    }

    const createNewInvoice = () => {
      router.visit('/invoices/create');
    }

    const calculateRemainingAmount = (invoice) => {
      return invoice.total_amount - invoice.paid_amount;
    }

    const testPayOS = async () => {
      try {
        const response = await axios.post('/api/payos/test', {
          amount: 2000,
          description: "Test PayOS payment",
          returnUrl: window.location.origin + "/success",
          cancelUrl: window.location.origin + "/cancel"
        });
        
        // Redirect to PayOS checkout URL
        if (response.data.checkoutUrl) {
          window.location.href = response.data.checkoutUrl;
        }
      } catch (error) {
        console.error('PayOS test error:', error);
        alert('Error initiating PayOS payment');
      }
    }

    return {
      filters,
      formatCurrency,
      getStatusClass,
      getStatusText,
      viewInvoiceDetails,
      applyFilters,
      createNewInvoice,
      calculateRemainingAmount,
      testPayOS,
    }
  },
}
</script>
