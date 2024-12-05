<template>
  <div class="print-template w-[76mm] mx-auto bg-white p-4 font-sans">
    <!-- Header - Modernized -->
    <div class="text-center mb-8">
      <img src="/images/icon-192x192.png" alt="Allure Spa Logo" class="w-16 h-16 mx-auto mb-4 object-contain" />
      <h1 class="text-2xl font-bold tracking-wider mb-2 text-gray-800">ALLURE SPA</h1>
      <p class="text-sm leading-relaxed text-gray-600">
        720A Điện Biên Phủ, P. 22, Bình Thạnh, HCM<br>
        ĐT: (84) 986 910 920
      </p>
    </div>

    <!-- Invoice Info - Refined -->
    <div class="text-center mb-8 border-y-2 border-gray-200 py-4">
      <h2 class="text-xl font-bold mb-3 tracking-wide text-gray-800">HÓA ĐƠN THANH TOÁN</h2>
      <div class="text-sm space-y-1.5 text-gray-600">
        <p class="font-medium">#{{ invoice.id }}</p>
        <p>{{ formatDateTime(invoice.created_at) }}</p>
      </div>
    </div>

    <!-- Customer Info - Simplified -->
    <div class="mb-6 text-sm">
      <div class="grid grid-cols-1 gap-2">
        <div class="flex justify-between items-center">
          <span class="font-medium">Khách hàng:</span>
          <span>{{ invoice.user?.full_name }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="font-medium">Số điện thoại:</span>
          <span>{{ invoice.user?.phone_number }}</span>
        </div>
      </div>
    </div>

    <!-- Items Table - Compact version -->
    <div class="mb-6">
      <table class="w-full text-[11px]">
        <thead>
          <tr class="border-b border-gray-300">
            <th class="py-2 text-left font-bold">SP/DV</th>
            <th class="py-2 text-right font-bold">SL</th>
            <th class="py-2 text-right font-bold">Đơn giá</th>
            <th class="py-2 text-right font-bold">T.Tiền</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in invoice.order?.order_items" :key="item.id" class="border-b border-gray-100">
            <td class="py-1.5">
              <div class="font-medium">{{ getItemName(item) }}</div>
              <div class="text-[9px] text-gray-600">{{ getItemCode(item) }}</div>
              <div v-if="item.service_type" class="text-[9px] font-medium text-gray-600">
                {{ formatServiceType(item.service_type) }}
              </div>
            </td>
            <td class="py-1.5 text-right">{{ item.quantity }}</td>
            <td class="py-1.5 text-right">{{ formatCurrency(item.price) }}</td>
            <td class="py-1.5 text-right font-medium">{{ formatCurrency(item.price * item.quantity) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Summary - Simplified -->
    <div class="text-sm space-y-2 mb-6">
      <div class="flex justify-between">
        <span>Tổng tiền hàng:</span>
        <span>{{ formatCurrency(subtotalAmount) }}</span>
      </div>

      <div v-if="invoice.order?.discount_amount" class="flex justify-between">
        <span>Giảm giá:</span>
        <span>-{{ formatCurrency(invoice.order.discount_amount) }}</span>
      </div>

      <div class="flex justify-between font-bold border-t border-gray-200 pt-2">
        <span>Tổng thanh toán:</span>
        <span>{{ formatCurrency(invoice.total_amount) }}</span>
      </div>

      <div class="flex justify-between">
        <span>Đã thanh toán:</span>
        <span>{{ formatCurrency(invoice.paid_amount) }}</span>
      </div>

      <div class="flex justify-between font-medium">
        <span>Còn lại:</span>
        <span>{{ formatCurrency(invoice.remaining_amount) }}</span>
      </div>
    </div>

    <!-- Footer - Enhanced -->
    <div class="mt-10 text-center space-y-3 border-t-2 border-gray-200 pt-6">
      <p class="font-medium text-gray-800 text-base">Cảm ơn quý khách đã sử dụng dịch vụ!</p>
      <div class="text-sm text-gray-600 space-y-1.5">
        <p class="font-medium">Hotline: (84) 986 910 920</p>
        <p class="font-medium">Website: www.allurespa.com.vn</p>
      </div>
    </div>
  </div>
</template>

<script>
import AllureLogo from '@/Components/AllureLogo.vue'
import { computed } from 'vue'

export default {
  components: {
    AllureLogo
  },
  props: {
    invoice: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const subtotalAmount = computed(() => {
      if (!props.invoice?.order?.order_items) return 0;
      return props.invoice.order.order_items.reduce((sum, item) => {
        return sum + (Number(item.price) * Number(item.quantity));
      }, 0);
    });

    return {
      subtotalAmount
    }
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(value)
    },
    formatDateTime(datetime) {
      return new Date(datetime).toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    getItemName(item) {
      if (item.item_type === 'service') {
        return item.service?.service_name || 'Dịch vụ không xác định';
      } else if (item.item_type === 'product') {
        return item.product?.name || 'Sản phẩm không xác định';
      }
      return 'Không xác định';
    },
    getItemCode(item) {
      if (item.item_type === 'service') {
        return item.service?.code || '-';
      } else if (item.item_type === 'product') {
        return item.product?.code || '-';
      }
      return '-';
    },
    formatServiceType(type) {
      const types = {
        'single': 'Đơn lẻ',
        'combo_5': 'Combo 5 lần',
        'combo_10': 'Combo 10 lần'
      };
      return types[type] || type;
    }
  },
  mounted() {
    this.$emit('rendered')
  }
}
</script>

<style scoped>
@media print {
  .print-template {
    width: 76mm;
    padding: 12px;
    margin: 0 auto;
    background-color: white !important;
  }

  /* Sử dụng font system tốt cho tiếng Việt */
  * {
    font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial,
      'Noto Sans', 'Liberation Sans', sans-serif, 'Apple Color Emoji',
      'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji' !important;
    color: black !important;
    background-color: transparent !important;
  }

  /* Tối ưu cho in ấn */
  @page {
    margin: 0;
    size: 80mm auto;
  }

  table {
    page-break-inside: avoid;
    border-collapse: collapse;
  }

  th,
  td {
    padding: 8px 4px;
  }

  img {
    display: block !important;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }

  /* Đảm bảo độ tương phản khi in */
  .text-gray-600 {
    color: #4b5563 !important;
  }

  .text-gray-800 {
    color: #1f2937 !important;
  }
}
</style>
