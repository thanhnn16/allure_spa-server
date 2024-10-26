<template>
  <div class="print-template">
    <!-- Header -->
    <div class="text-center mb-6">
      <AllureLogo class="w-auto h-16 mx-auto mb-2" />
      <h1 class="text-xl font-bold">ALLURE SPA</h1>
      <p class="text-sm text-gray-600">
        Tầng 1 Shophouse P1- SH02 Vinhomes Central Park, 720A Điện Biên Phủ, P. 22, Bình Thạnh, HCM<br>
        Điện thoại (Zalo): (84) 986 910 920<br>
        Email: tranducdao88@gmail.com
      </p>
    </div>

    <div class="border-b-2 border-gray-300 mb-6"></div>

    <!-- Invoice Title -->
    <div class="text-center mb-6">
      <h2 class="text-xl font-bold">HÓA ĐƠN</h2>
      <p class="text-sm text-gray-600">Số: {{ invoice.id }}</p>
      <p class="text-sm text-gray-600">Ngày: {{ formatDate(invoice.created_at) }}</p>
    </div>

    <!-- Customer Info -->
    <div class="mb-6">
      <p><strong>Khách hàng:</strong> {{ invoice.user?.full_name }}</p>
      <p><strong>Số điện thoại:</strong> {{ invoice.user?.phone_number }}</p>
    </div>

    <!-- Items Table -->
    <div class="w-full mb-6">
      <table class="w-full border-collapse border border-gray-200">
        <thead>
          <tr>
            <th class="w-16 px-3 py-2 text-center text-sm font-semibold text-gray-700 border border-gray-200 bg-gray-50">
              STT
            </th>
            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-700 border border-gray-200 bg-gray-50">
              Sản phẩm/Dịch vụ
            </th>
            <th class="w-32 px-3 py-2 text-right text-sm font-semibold text-gray-700 border border-gray-200 bg-gray-50">
              Đơn giá
            </th>
            <th class="w-20 px-3 py-2 text-center text-sm font-semibold text-gray-700 border border-gray-200 bg-gray-50">
              SL
            </th>
            <th class="w-32 px-3 py-2 text-right text-sm font-semibold text-gray-700 border border-gray-200 bg-gray-50">
              Thành tiền
            </th>
            <th class="w-40 px-3 py-2 text-left text-sm font-semibold text-gray-700 border border-gray-200 bg-gray-50">
              Ghi chú
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in invoice.order?.items" 
              :key="item.id" 
              class="border-b border-gray-200">
            <td class="px-3 py-2 text-center text-sm text-gray-600 border-x border-gray-200">
              {{ index + 1 }}
            </td>
            <td class="px-3 py-2 text-sm text-gray-600 border-x border-gray-200">
              {{ getItemName(item) }}
            </td>
            <td class="px-3 py-2 text-right text-sm text-gray-600 border-x border-gray-200">
              {{ formatCurrency(item.price) }}
            </td>
            <td class="px-3 py-2 text-center text-sm text-gray-600 border-x border-gray-200">
              {{ item.quantity }}
            </td>
            <td class="px-3 py-2 text-right text-sm text-gray-600 border-x border-gray-200">
              {{ formatCurrency(item.price * item.quantity) }}
            </td>
            <td class="px-3 py-2 text-sm border-x border-gray-200">
              <span v-if="item.service_type" 
                    class="inline-block px-2 py-1 text-xs rounded" 
                    :class="{
                      'bg-blue-50 text-blue-700': item.service_type === 'single',
                      'bg-green-50 text-green-700': item.service_type === 'combo_5',
                      'bg-purple-50 text-purple-700': item.service_type === 'combo_10'
                    }">
                {{ formatServiceType(item.service_type) }}
              </span>
            </td>
          </tr>
          <!-- Empty state -->
          <tr v-if="!invoice.order?.items?.length">
            <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500 border border-gray-200">
              Không có dữ liệu
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Summary -->
    <div class="border border-gray-200 rounded-md p-4 mb-6">
      <div class="flex justify-between py-2 text-sm">
        <span class="text-gray-600">Tổng tiền hàng:</span>
        <span class="font-medium text-gray-900">{{ formatCurrency(invoice.total_amount) }}</span>
      </div>
      <div class="flex justify-between py-2 text-sm">
        <span class="text-gray-600">Giảm giá:</span>
        <span class="font-medium text-red-600">-{{ formatCurrency(invoice.order?.discount_amount || 0) }}</span>
      </div>
      <div class="flex justify-between py-2 text-sm">
        <span class="text-gray-600">Đã thanh toán:</span>
        <span class="font-medium text-green-600">{{ formatCurrency(invoice.paid_amount) }}</span>
      </div>
      <div class="flex justify-between py-2 text-sm">
        <span class="text-gray-600">Còn lại:</span>
        <span class="font-medium text-orange-600">{{ formatCurrency(invoice.remaining_amount) }}</span>
      </div>
      <div class="flex justify-between py-3 text-base font-medium border-t border-gray-200">
        <span class="text-gray-900">Thành tiền thanh toán:</span>
        <span class="text-gray-900">{{ formatCurrency(invoice.total_amount) }}</span>
      </div>
    </div>

    <!-- Note if exists -->
    <div v-if="invoice.note" class="mb-6">
      <p class="text-sm"><strong>Ghi chú:</strong> {{ invoice.note }}</p>
    </div>

    <!-- Footer -->
    <div class="text-center mt-8">
      <p class="text-sm text-gray-600 mb-2">Cảm ơn quý khách đã sử dụng dịch vụ!</p>
      <p class="text-sm">www.allurespa.com.vn</p>
    </div>
  </div>
</template>

<script>
import AllureLogo from '@/Components/AllureLogo.vue'

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
  mounted() {
    console.log('Invoice data:', this.invoice);
    console.log('Order items:', this.invoice.order?.items);
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND' 
      }).format(value)
    },
    formatServiceType(type) {
      if (!type) return '';
      
      const types = {
        'single': 'Đơn lẻ',
        'combo_5': 'Combo 5 lần',
        'combo_10': 'Combo 10 lần'
      };
      
      return types[type] || type;
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('vi-VN')
    },
    getItemName(item) {
      if (item.item_type === 'service') {
        return item.service?.service_name || 'Dịch vụ không xác định';
      } else if (item.item_type === 'product') {
        return item.product?.name || 'Sản phẩm không xác định';
      }
      return 'Không xác định';
    },
    calculateSubtotal() {
      if (!this.invoice.order?.items) return 0;
      return this.invoice.order.items.reduce((sum, item) => {
        return sum + (item.quantity * item.price);
      }, 0);
    }
  }
}
</script>

<style>
.print-template {
    width: 100%;
    max-width: 210mm; /* A4 width */
    margin: 0 auto;
    padding: 20px;
    background: white;
}

@media print {
    .print-template {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 20px;
        position: absolute;
        top: 0;
        left: 0;
    }

    /* Reset all inherited styles */
    .print-template * {
        font-family: Arial, sans-serif !important;
        line-height: 1.5 !important;
    }

    /* Ensure text and backgrounds print correctly */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-scheme: exact !important;
    }

    /* Hide all other elements when printing */
    body > *:not(.print-template) {
        display: none !important;
    }
}
</style>
