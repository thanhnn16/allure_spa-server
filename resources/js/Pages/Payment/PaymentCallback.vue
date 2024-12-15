<!-- resources/js/Pages/Payment/PaymentCallback.vue -->
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-dark-bg">
    <div class="max-w-md w-full">
      <PaymentCallback />
    </div>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import PaymentCallback from '@/Components/PaymentCallback.vue'

export default {
  components: {
    Head,
    PaymentCallback
  },
  
  props: {
    orderCode: String,
    invoice_id: String
  }
}
</script>