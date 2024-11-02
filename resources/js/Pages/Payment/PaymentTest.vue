<template>
  <LayoutAuthenticated>
    <Head title="Test Payment" />
    <SectionMain>
      <div class="container mx-auto px-4 py-8">
        <!-- Payment Status Card -->
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="p-6">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-8">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
              <p class="text-gray-600">Đang xử lý thanh toán...</p>
            </div>

            <!-- Success State -->
            <div v-else-if="status === 'success'" class="text-center py-8">
              <div class="mb-4">
                <svg class="h-16 w-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <h2 class="text-2xl font-bold text-gray-800 mb-2">Thanh toán thành công!</h2>
              <p class="text-gray-600 mb-6">Cảm ơn bạn đã thực hiện giao dịch test.</p>
              <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-600">Mã giao dịch</p>
                  <p class="font-medium">{{ paymentData?.orderCode }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-600">Số tiền</p>
                  <p class="font-medium">{{ formatCurrency(paymentData?.amount) }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-600">Thời gian thanh toán</p>
                  <p class="font-medium">{{ formatDateTime(paymentData?.createdAt) }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-600">Trạng thái</p>
                  <p class="font-medium text-green-600">{{ getStatusText(paymentData?.status) }}</p>
                </div>
              </div>
            </div>

            <!-- Error State -->
            <div v-else-if="status === 'error'" class="text-center py-8">
              <div class="mb-4">
                <svg class="h-16 w-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </div>
              <h2 class="text-2xl font-bold text-gray-800 mb-2">Thanh toán thất bại</h2>
              <p class="text-gray-600 mb-6">{{ errorMessage }}</p>
            </div>

            <!-- Cancel State -->
            <div v-else-if="status === 'cancel'" class="text-center py-8">
              <div class="mb-4">
                <svg class="h-16 w-16 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
              </div>
              <h2 class="text-2xl font-bold text-gray-800 mb-2">Giao dịch đã bị hủy</h2>
              <p class="text-gray-600">Bạn đã hủy giao dịch test này.</p>
            </div>

            <!-- Actions -->
            <div class="mt-8 text-center">
              <button @click="goBack" 
                      class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-lg">
                Quay lại trang chủ
              </button>
            </div>
          </div>
        </div>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<script>
import { Head, router } from "@inertiajs/vue3";
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
  components: {
    Head,
    LayoutAuthenticated,
    SectionMain,
  },
  
  props: {
    status: {
      type: String,
      default: null
    },
    transactionId: {
      type: String,
      default: null
    }
  },

  setup(props) {
    const loading = ref(true)
    const status = ref(props.status || 'pending')
    const errorMessage = ref('')
    const transactionId = ref(props.transactionId)
    const paymentData = ref(null)

    const getStatusText = (status) => {
      const statusMap = {
        'PAID': 'Đã thanh toán',
        'PENDING': 'Chờ thanh toán',
        'CANCELLED': 'Đã hủy',
        'EXPIRED': 'Đã hết hạn'
      }
      return statusMap[status] || status
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleString('vi-VN')
    }

    onMounted(async () => {
      // Get URL parameters
      const urlParams = new URLSearchParams(window.location.search)
      const paymentStatus = urlParams.get('status')
      const orderCode = urlParams.get('orderCode')
      const invoiceId = urlParams.get('invoice_id')

      if (paymentStatus === 'success' && orderCode) {
        try {
          // Verify payment with backend
          const response = await axios.post('/api/payos/verify', {
            orderCode: orderCode,
            invoice_id: invoiceId
          })
          
          if (response.data.success) {
            status.value = 'success'
            paymentData.value = response.data.data
          } else {
            status.value = 'error'
            errorMessage.value = response.data.message || 'Không thể xác minh giao dịch'
          }
        } catch (error) {
          status.value = 'error'
          errorMessage.value = error.response?.data?.message || 'Đã có lỗi xảy ra'
        }
      } else if (paymentStatus === 'cancel') {
        status.value = 'cancel'
      } else {
        status.value = 'error'
        errorMessage.value = 'Trạng thái thanh toán không hợp lệ'
      }

      loading.value = false
    })

    const goBack = () => {
      router.visit('/invoices')
    }

    return {
      loading,
      status,
      errorMessage,
      transactionId,
      paymentData,
      getStatusText,
      formatDateTime,
      goBack
    }
  }
}
</script>
