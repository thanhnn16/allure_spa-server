<template>
  <div class="print-template bg-white">
    <!-- Header - giảm kích thước -->
    <div class="text-center mb-4">
      <AllureLogo class="w-auto h-12 mx-auto mb-1" />
      <h1 class="text-lg font-bold">ALLURE SPA</h1>
      <p class="text-xs text-gray-600 leading-tight">
        Tầng 1 Shophouse P1- SH02 Vinhomes Central Park, 720A Điện Biên Phủ, P. 22, Bình Thạnh, HCM<br>
        Điện thoại (Zalo): (84) 986 910 920 - Email: tranducdao88@gmail.com
      </p>
    </div>

    <div class="border-b border-gray-300 mb-3"></div>

    <!-- Invoice Title - giảm spacing -->
    <div class="text-center mb-3">
      <h2 class="text-lg font-bold">HÓA ĐƠN</h2>
      <p class="text-xs text-gray-600">Số: {{ invoice.id }} - Ngày: {{ formatDate(invoice.created_at) }}</p>
    </div>

    <!-- Customer Info - compact -->
    <div class="mb-3 text-sm">
      <p class="leading-tight"><strong>Khách hàng:</strong> {{ invoice.user?.full_name }}</p>
      <p class="leading-tight"><strong>Số điện thoại:</strong> {{ invoice.user?.phone_number }}</p>
    </div>

    <!-- Items Table - modernized -->
    <div class="w-full mb-3">
      <table class="w-full border-collapse text-xs">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="w-12 py-3 text-center font-medium text-gray-700">STT</th>
            <th class="py-3 text-left font-medium text-gray-700">Sản phẩm/Dịch vụ</th>
            <th class="w-28 py-3 text-right font-medium text-gray-700">Đơn giá</th>
            <th class="w-16 py-3 text-center font-medium text-gray-700">SL</th>
            <th class="w-28 py-3 text-right font-medium text-gray-700">Thành tiền</th>
            <th class="w-32 py-3 text-left font-medium text-gray-700">Ghi chú</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in invoice.order?.items" 
              :key="item.id" 
              class="border-b border-gray-100 hover:bg-gray-50">
            <td class="py-3 text-center text-gray-600">{{ index + 1 }}</td>
            <td class="py-3 text-gray-800">
              <div class="font-medium">{{ getItemName(item) }}</div>
              <div v-if="item.description" class="text-xs text-gray-500 mt-0.5">
                {{ item.description }}
              </div>
            </td>
            <td class="py-3 text-right text-gray-600 font-medium">
              {{ formatCurrency(item.price) }}
            </td>
            <td class="py-3 text-center text-gray-600">{{ item.quantity }}</td>
            <td class="py-3 text-right text-gray-800 font-medium">
              {{ formatCurrency(item.price * item.quantity) }}
            </td>
            <td class="py-3">
              <span v-if="item.service_type" 
                    class="inline-block px-2 py-0.5 text-xs rounded-full font-medium" 
                    :class="{
                      'bg-blue-50 text-blue-700': item.service_type === 'single',
                      'bg-green-50 text-green-700': item.service_type === 'combo_5',
                      'bg-purple-50 text-purple-700': item.service_type === 'combo_10'
                    }">
                {{ formatServiceType(item.service_type) }}
              </span>
            </td>
          </tr>
          <!-- Empty state with modern design -->
          <tr v-if="!invoice.order?.items?.length">
            <td colspan="6" class="py-8 text-center">
              <div class="text-gray-400">
                <i class="fas fa-box-open text-2xl mb-2"></i>
                <p class="text-sm">Không có dữ liệu</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Summary - compact -->
    <div class="border border-gray-200 rounded p-2 mb-3 text-sm">
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

    <!-- Note if exists - compact -->
    <div v-if="invoice.note" class="mb-3">
      <p class="text-xs"><strong>Ghi chú:</strong> {{ invoice.note }}</p>
    </div>

    <!-- Footer - compact -->
    <div class="text-center mt-4">
      <p class="text-xs text-gray-600 mb-1">Cảm ơn quý khách đã sử dụng dịch vụ!</p>
      <p class="text-xs">www.allurespa.com.vn</p>
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
    width: 210mm;
    min-height: 297mm;
    padding: 15mm;
    margin: 0 auto;
    background: white;
    font-size: 9pt; /* Giảm font size tổng thể */
}

/* Tối ưu spacing */
.print-template .mb-6 {
    margin-bottom: 0.75rem;
}

/* Header tối ưu */
.print-template .h-16 {
    height: 3rem;
}

/* Bảng tối ưu */
.print-template table {
    font-size: 9pt;
}

.print-template th,
.print-template td {
    padding: 4px 8px;
}

/* Tối ưu khoảng cách các phần */
.print-template .border-b-2 {
    margin: 0.5rem 0;
}

/* Tối ưu phần summary */
.print-template .border {
    padding: 0.5rem;
}

.print-template .py-2 {
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
}

/* Footer tối ưu */
.print-template .mt-8 {
    margin-top: 1rem;
}

/* Modern table styles */
.print-template table {
    border-spacing: 0;
    font-size: 9pt;
}

.print-template th {
    font-weight: 500;
}

.print-template td, 
.print-template th {
    padding: 8px 12px;
    vertical-align: middle;
}

.print-template tbody tr {
    transition: background-color 0.2s;
}

/* Ensure proper printing of background colors */
@media print {
    .print-template {
        width: 100%;
        height: auto; /* Thay đổi từ 100% thành auto */
        padding: 15mm;
        margin: 0;
        position: relative; /* Thay đổi từ fixed thành relative */
        top: 0;
        left: 0;
        background: white;
        page-break-after: avoid;
        page-break-inside: avoid;
    }

    /* Đảm bảo bảng không bị ngắt trang giữa chừng */
    table {
        page-break-inside: avoid;
    }

    /* Đảm bảo màu sắc */
    .print-template * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    @page {
        size: A4;
        margin: 0;
    }

    /* Modern table styles */
    .print-template table {
        border-spacing: 0;
        font-size: 9pt;
    }

    .print-template th {
        font-weight: 500;
    }

    .print-template td, 
    .print-template th {
        padding: 8px 12px;
        vertical-align: middle;
    }

    .print-template tbody tr {
        transition: background-color 0.2s;
    }

    /* Ensure proper printing of background colors */
    .print-template .bg-gray-50 {
        background-color: #f9fafb !important;
    }
    
    .print-template .bg-blue-50 {
        background-color: #eff6ff !important;
    }
    
    .print-template .bg-green-50 {
        background-color: #f0fdf4 !important;
    }
    
    .print-template .bg-purple-50 {
        background-color: #faf5ff !important;
    }
}
</style>
