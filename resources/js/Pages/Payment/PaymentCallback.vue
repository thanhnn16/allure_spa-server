<template>

  <Head title="Thanh toán" />
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-dark-bg">
    <div class="max-w-md w-full">
      <PaymentCallback :orderCode="$page.props.orderCode || $page.url.split('orderCode=')[1]?.split('&')[0]"
        :invoice_id="$page.props.invoice_id" :status="$page.url.split('status=')[1]?.split('&')[0]" />
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

  setup() {
    const urlParams = new URLSearchParams(window.location.search);
    return {
      orderCode: urlParams.get('orderCode'),
      invoice_id: urlParams.get('invoice_id'),
      status: urlParams.get('status')
    }
  }
}
</script>