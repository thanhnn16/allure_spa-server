<!-- PaymentPayOS.vue -->
<template>
    <div class="payment-payos">
        <div v-if="loading" class="loading">
            <span>Đang xử lý thanh toán...</span>
        </div>

        <div v-else-if="error" class="error">
            <p>{{ error }}</p>
            <button @click="retryPayment" class="retry-btn">
                Thử lại
            </button>
        </div>

        <div v-else class="payment-info">
            <h3>Thông tin thanh toán</h3>
            <div class="amount">
                <span>Số tiền cần thanh toán:</span>
                <strong>{{ formatCurrency(invoice.remaining_amount) }}</strong>
            </div>

            <button @click="processPayment" :disabled="processing" class="pay-btn">
                {{ processing ? 'Đang xử lý...' : 'Thanh toán ngay' }}
            </button>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue'
import axios from 'axios'

export default {
    name: 'PaymentPayOS',

    props: {
        invoice: {
            type: Object,
            required: true
        }
    },

    setup(props) {
        const loading = ref(false)
        const error = ref(null)
        const processing = ref(false)

        const processPayment = async () => {
            try {
                processing.value = true
                error.value = null

                const response = await axios.post(
                    `/invoices/${props.invoice.id}/pay-with-payos`
                )

                if (response.data.success && response.data.checkoutUrl) {
                    // Chuyển hướng đến trang thanh toán PayOS
                    window.location.href = response.data.checkoutUrl
                } else {
                    throw new Error('Không thể tạo link thanh toán')
                }

            } catch (err) {
                error.value = err.response?.data?.message || 'Có lỗi xảy ra khi xử lý thanh toán'
            } finally {
                processing.value = false
            }
        }

        const retryPayment = () => {
            error.value = null
            processPayment()
        }

        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount)
        }

        return {
            loading,
            error,
            processing,
            processPayment,
            retryPayment,
            formatCurrency
        }
    }
}
</script>

<style scoped>
.payment-payos {
    padding: 1rem;
}

.loading {
    text-align: center;
    padding: 2rem;
}

.error {
    color: red;
    text-align: center;
    padding: 1rem;
}

.payment-info {
    max-width: 500px;
    margin: 0 auto;
}

.amount {
    margin: 1rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pay-btn {
    width: 100%;
    padding: 0.75rem;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.pay-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.retry-btn {
    padding: 0.5rem 1rem;
    background: #2196F3;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 1rem;
}
</style>