<!-- PaymentCallback.vue -->
<template>
  <div class="payment-callback">
    <div v-if="loading" class="loading">
      <div class="flex items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        <span class="ml-3">Đang xác thực thanh toán...</span>
      </div>
    </div>

    <div v-else class="max-w-lg mx-auto p-6">
      <div v-if="success" class="success bg-green-50 dark:bg-green-900/20 p-6 rounded-lg">
        <div class="text-center">
          <i class="mdi mdi-check-circle text-5xl text-green-500 mb-4"></i>
          <h3 class="text-xl font-semibold text-green-700 dark:text-green-400 mb-2">
            Thanh toán thành công!
          </h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Mã giao dịch: {{ transactionId }}
          </p>
          <button @click="goToOrder" class="btn bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg">
            Xem chi tiết đơn hàng
          </button>
        </div>
      </div>

      <div v-else class="failed bg-red-50 dark:bg-red-900/20 p-6 rounded-lg">
        <div class="text-center">
          <i class="mdi mdi-close-circle text-5xl text-red-500 mb-4"></i>
          <h3 class="text-xl font-semibold text-red-700 dark:text-red-400 mb-2">
            Thanh toán thất bại
          </h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">{{ error }}</p>
          <button @click="retryPayment" class="btn bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg">
            Thử lại
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

export default {
  setup() {
    const route = useRoute()
    const router = useRouter()

    const loading = ref(true)
    const success = ref(false)
    const error = ref(null)
    const transactionId = ref(null)
    const orderId = ref(null)

    const verifyPayment = async () => {
      try {
        const orderCode = route.query.orderCode
        const invoiceId = route.query.invoice_id

        const response = await axios.post('/api/payos/verify', {
          orderCode,
          invoice_id: invoiceId
        })

        if (response.data.success) {
          success.value = true
          transactionId.value = orderCode
          orderId.value = response.data.data.order_id
        } else {
          throw new Error(response.data.message)
        }
      } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi xác thực thanh toán'
        success.value = false
      } finally {
        loading.value = false
      }
    }

    const goToOrder = () => {
      if (orderId.value) {
        router.push(`/orders/${orderId.value}`)
      } else {
        router.push('/orders')
      }
    }

    const retryPayment = () => {
      router.back()
    }

    onMounted(() => {
      verifyPayment()
    })

    return {
      loading,
      success,
      error,
      transactionId,
      goToOrder,
      retryPayment
    }
  }
}
</script>