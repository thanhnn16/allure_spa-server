<template>
  <div class="print-template w-[210mm] mx-auto bg-white p-8 font-sans">
    <!-- Header - Cải thiện layout -->
    <div class="flex items-center justify-between mb-8 border-b pb-6">
      <div class="flex items-center gap-4">
        <img src="/images/icon-192x192.png" alt="Allure Spa Logo" class="w-20 h-20 object-contain" />
        <div>
          <h1 class="text-3xl font-bold tracking-wider text-gray-800">ALLURE SPA</h1>
          <p class="text-base leading-relaxed text-gray-600 mt-2">
            720A Điện Biên Phủ, P. 22, Bình Thạnh, HCM<br>
            ĐT: (84) 986 910 920
          </p>
        </div>
      </div>
      <div class="text-right">
        <h2 class="text-2xl font-bold mb-3 tracking-wide text-gray-800">HÓA ĐƠN THANH TOÁN</h2>
        <p class="text-base font-medium">#{{ invoice.id }}</p>
        <p class="text-gray-600">{{ formatDateTime(invoice.created_at) }}</p>
      </div>
    </div>

    <!-- Thông tin khách hàng - Layout mới -->
    <div class="mb-8 p-4 bg-gray-50 rounded-lg">
      <div class="grid grid-cols-2 gap-4 text-base">
        <div class="space-y-2">
          <div class="flex gap-4">
            <span class="font-medium w-32">Khách hàng:</span>
            <span>{{ invoice.user?.full_name }}</span>
          </div>
          <div class="flex gap-4">
            <span class="font-medium w-32">Số điện thoại:</span>
            <span>{{ invoice.user?.phone_number }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bảng sản phẩm/dịch vụ - Cải thiện layout -->
    <div class="mb-8">
      <table class="w-full text-base">
        <thead>
          <tr class="bg-gray-50">
            <th class="py-3 px-4 text-left font-bold border-b">SP/DV</th>
            <th class="py-3 px-4 text-center font-bold border-b w-20">SL</th>
            <th class="py-3 px-4 text-right font-bold border-b w-32">Đơn giá</th>
            <th class="py-3 px-4 text-right font-bold border-b w-32">T.Tiền</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in invoice.order?.order_items" :key="item.id" class="border-b">
            <td class="py-3 px-4">
              <div class="font-medium">{{ getItemName(item) }}</div>
              <div class="text-sm text-gray-600">{{ getItemCode(item) }}</div>
              <div v-if="item.service_type" class="text-sm font-medium text-gray-600">
                {{ formatServiceType(item.service_type) }}
              </div>
            </td>
            <td class="py-3 px-4 text-center">{{ item.quantity }}</td>
            <td class="py-3 px-4 text-right">{{ formatCurrency(item.price) }}</td>
            <td class="py-3 px-4 text-right font-medium">{{ formatCurrency(item.price * item.quantity) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Tổng kết - Layout mới -->
    <div class="flex justify-end mb-8">
      <div class="w-80 space-y-3 text-base">
        <div class="flex justify-between py-2">
          <span>Tổng tiền hàng:</span>
          <span class="font-medium">{{ formatCurrency(subtotalAmount) }}</span>
        </div>

        <div v-if="invoice.order?.discount_amount" class="flex justify-between py-2">
          <span>Giảm giá:</span>
          <span class="font-medium text-red-600">-{{ formatCurrency(invoice.order.discount_amount) }}</span>
        </div>

        <div class="flex justify-between py-3 border-t border-b border-gray-200 font-bold">
          <span>Tổng thanh toán:</span>
          <span>{{ formatCurrency(invoice.total_amount) }}</span>
        </div>

        <div class="flex justify-between py-2">
          <span>Đã thanh toán:</span>
          <span class="font-medium">{{ formatCurrency(invoice.paid_amount) }}</span>
        </div>

        <div class="flex justify-between py-2 text-lg font-bold">
          <span>Còn lại:</span>
          <span>{{ formatCurrency(invoice.remaining_amount) }}</span>
        </div>
      </div>
    </div>

    <!-- Footer - Cải thiện layout -->
    <div class="mt-12 text-center space-y-4 border-t pt-8">
      <p class="font-medium text-gray-800 text-lg">Cảm ơn quý khách đã sử dụng dịch vụ!</p>
      <div class="text-base text-gray-600 space-y-2">
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
    width: 210mm;
    padding: 20mm;
    margin: 0 auto;
    background-color: white !important;
  }

  @page {
    margin: 0;
    size: A4;
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
