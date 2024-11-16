<template>
  <LayoutAuthenticated>

    <Head :title="`Chi tiết đơn hàng #${order.id}`" />
    <SectionMain>
      <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6 dark:bg-dark-surface/50 p-4 rounded-lg">
          <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold dark:text-dark-text">Chi tiết đơn hàng #{{ order.id }}</h1>
            <span :class="getStatusClass(order.status)">
              <span :class="['h-2 w-2 rounded-full mr-2 self-center', getStatusDotClass(order.status)]"></span>
              {{ getStatusText(order.status) }}
            </span>
          </div>
          <div class="flex space-x-3">
            <button v-if="canUpdateStatus" @click="openUpdateStatusModal"
              class="bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
              <i class="mdi mdi-pencil mr-2"></i>Cập nhật trạng thái
            </button>
            <Link :href="route('orders.index')"
              class="bg-gray-500 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
            <i class="mdi mdi-arrow-left mr-2"></i>Quay lại
            </Link>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Thông tin đơn hàng -->
          <div class="lg:col-span-2">
            <div class="bg-white dark:bg-dark-surface shadow-md dark:shadow-gray-800/30 overflow-hidden sm:rounded-lg">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-dark-border">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-dark-text">Thông tin đơn hàng </h3>
              </div>
              <div class="px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200 dark:divide-dark-border">
                  <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Khách hàng</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-dark-text sm:mt-0 sm:col-span-2">
                      {{ order.user.full_name }}
                    </dd>
                  </div>
                  <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng tiền</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-dark-text sm:mt-0 sm:col-span-2">
                      {{ formatCurrency(order.total_amount) }}
                    </dd>
                  </div>
                  <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Giảm giá</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-dark-text sm:mt-0 sm:col-span-2">
                      {{ formatCurrency(order.discount_amount) }}
                    </dd>
                  </div>
                  <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ngày tạo</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-dark-text sm:mt-0 sm:col-span-2">
                      {{ formatDate(order.created_at) }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Danh sách sản phẩm/dịch vụ -->
            <div class="mt-6">
              <div class="bg-white dark:bg-dark-surface shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-dark-border">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-dark-text">Các mục trong đơn hàng
                  </h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-border">
                  <thead class="bg-gray-50 dark:bg-dark-surface/80">
                    <tr>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Sản phẩm/Dịch vụ
                      </th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Loại
                      </th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Số lượng
                      </th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Đơn giá
                      </th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Thành tiền
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-dark-surface divide-y divide-gray-200 dark:divide-dark-border">
                    <tr v-for="item in order.order_items" :key="item.id"
                      class="hover:bg-gray-50 dark:hover:bg-dark-bg/30 transition-colors duration-150">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-dark-text">
                        <div class="flex flex-col">
                          <span class="font-medium">{{ item.item_name }}</span>
                          <span v-if="item.item_type === 'service' && item.service_type"
                            class="text-xs text-gray-500 dark:text-gray-400">
                            {{ getServiceTypeText(item.service_type) }}
                          </span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <span :class="getItemTypeClass(item.item_type)">
                          {{ item.item_type === 'product' ? 'Sản phẩm' : 'Dịch vụ' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ item.quantity }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ formatCurrency(item.price) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ formatCurrency(item.quantity * item.price) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Thông tin thanh toán -->
          <div class="lg:col-span-1">
            <div class="bg-white dark:bg-dark-surface shadow overflow-hidden sm:rounded-lg">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-dark-border">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-dark-text">Thông tin thanh toán</h3>
              </div>
              <div class="px-4 py-5">
                <!-- Khi đã có hóa đơn -->
                <div v-if="order.invoice" class="space-y-4">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Trạng thái</span>
                    <span :class="getPaymentStatusClass(order.invoice.status)">
                      {{ getPaymentStatusText(order.invoice.status) }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Tổng tiền</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-dark-text">
                      {{ formatCurrency(order.invoice.total_amount) }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Đã thanh toán</span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">
                      {{ formatCurrency(order.invoice.paid_amount) }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Còn lại</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">
                      {{ formatCurrency(order.invoice.remaining_amount) }}
                    </span>
                  </div>

                  <!-- Nút xem chi tiết hóa đơn -->
                  <div class="pt-4 border-t border-gray-200 dark:border-dark-border">
                    <Link :href="route('invoices.show', order.invoice.id)"
                      class="w-full bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold py-3 px-4 rounded transition-colors flex items-center justify-center">
                    <i class="mdi mdi-file-document-outline mr-2"></i>
                    Xem chi tiết hóa đơn #{{ order.invoice.id }}
                    </Link>
                  </div>
                </div>

                <!-- Khi chưa có hóa đơn -->
                <div v-else class="space-y-4">
                  <div class="text-center py-6">
                    <div class="mb-4">
                      <i class="mdi mdi-file-document-outline text-5xl text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-6 text-lg">
                      Chưa có hóa đơn cho đơn hàng này
                    </span>

                    <!-- Hiển thị tổng quan về đơn hàng -->
                    <div
                      class="bg-gray-50 dark:bg-dark-bg/50 p-6 rounded-lg mb-6 border border-gray-200 dark:border-dark-border">
                      <div class="space-y-3">
                        <div class="flex justify-between text-lg">
                          <span class="text-gray-600 dark:text-gray-400">Tổng tiền hàng:</span>
                          <span class="font-medium text-gray-900 dark:text-dark-text">
                            {{ formatCurrency(order.total_amount) }}
                          </span>
                        </div>
                        <div v-if="order.discount_amount > 0" class="flex justify-between text-lg">
                          <span class="text-gray-600 dark:text-gray-400">Giảm giá:</span>
                          <span class="text-green-600 dark:text-green-400 font-medium">
                            -{{ formatCurrency(order.discount_amount) }}
                          </span>
                        </div>
                        <div class="flex justify-between pt-3 border-t dark:border-dark-border text-lg">
                          <span class="font-semibold text-gray-900 dark:text-dark-text">Tổng thanh toán:</span>
                          <span class="font-semibold text-gray-900 dark:text-dark-text">
                            {{ formatCurrency(order.total_amount - order.discount_amount) }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Nút tạo hóa đơn -->
                    <button v-if="canCreateInvoice" @click="openCreateInvoiceModal"
                      class="w-full bg-green-500 dark:bg-green-600 hover:bg-green-600 dark:hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition-colors flex items-center justify-center text-lg">
                      <i class="mdi mdi-file-plus-outline mr-2 text-xl"></i>
                      Tạo hóa đơn mới
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal cập nhật trạng thái -->
      <CardBoxModal v-model="showStatusModal" title="Cập nhật trạng thái đơn hàng" button="info" button-label="Cập nhật"
        has-cancel @confirm="updateOrderStatus">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trạng thái mới</label>
            <select v-model="newStatus"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-dark-text focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400">
              <option v-for="status in getAvailableStatuses" :key="status" :value="status">
                {{ getStatusText(status) }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ghi chú</label>
            <textarea v-model="statusNote" rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-dark-text focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400"></textarea>
          </div>
        </div>
      </CardBoxModal>

      <!-- Modal xử lý thanh toán -->
      <CardBoxModal v-model="showPaymentModal" title="Xử lý thanh toán" button="success" button-label="Thanh toán"
        has-cancel @confirm="processPayment" class="dark:bg-dark-surface"
        :header-class="'dark:bg-dark-surface dark:text-dark-text border-b dark:border-dark-border'"
        :actions-class="'dark:bg-dark-surface border-t dark:border-dark-border'"
        :button-class="'dark:bg-green-600 dark:hover:bg-green-700 dark:text-white'"
        :cancel-button-class="'dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-white'">
        <!-- Nội dung modal thanh toán -->
        <div class="dark:bg-dark-surface p-4 rounded-lg">
          <!-- Thêm nội dung của modal thanh toán ở đây -->
        </div>
      </CardBoxModal>

      <!-- Modal tạo hóa đơn -->
      <CardBoxModal v-model="showCreateInvoiceModal" title="Tạo hóa đơn" button="success" button-label="Tạo hóa đơn"
        has-cancel @confirm="createInvoice">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ghi chú</label>
            <textarea v-model="invoiceNote" rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-dark-text focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400"
              placeholder="Nhập ghi chú cho hóa đơn (nếu có)"></textarea>
          </div>

          <!-- Thông tin hóa đơn -->
          <div class="bg-gray-50 dark:bg-dark-bg/50 p-4 rounded-md">
            <h4 class="font-medium mb-2 dark:text-dark-text">Thông tin hóa đơn</h4>
            <div class="space-y-2">
              <div class="flex justify-between dark:text-dark-text">
                <span>Tổng tiền hàng:</span>
                <span>{{ formatCurrency(order.total_amount) }}</span>
              </div>
              <div v-if="order.discount_amount > 0" class="flex justify-between text-green-600 dark:text-green-400">
                <span>Giảm giá:</span>
                <span>-{{ formatCurrency(order.discount_amount) }}</span>
              </div>
              <div class="flex justify-between font-bold pt-2 border-t dark:border-dark-border dark:text-dark-text">
                <span>Tổng thanh toán:</span>
                <span>{{ formatCurrency(order.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<script>
import { ref, computed } from 'vue'
import { Head, Link, router } from "@inertiajs/vue3"
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import axios from 'axios'
import { useToast } from "vue-toastification"

export default {
  components: {
    Head,
    Link,
    LayoutAuthenticated,
    SectionMain,
    CardBoxModal
  },
  props: {
    order: Object,
  },
  setup(props) {
    console.log('Order data:', props.order)
    console.log('Invoice data:', props.order.invoice)

    const showStatusModal = ref(false)
    const showPaymentModal = ref(false)
    const showCreateInvoiceModal = ref(false)
    const newStatus = ref(props.order.status)
    const statusNote = ref('')
    const invoiceNote = ref('')
    const toast = useToast()
    const loading = ref(false)

    // Các hàm format và helpers
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount)
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    // Các hàm xử lý trạng thái và style
    const getStatusClass = (status) => {
      const baseClasses = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full items-center transition-colors duration-150'
      const statusClasses = {
        'pending': `${baseClasses} bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300`,
        'confirmed': `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300`,
        'shipping': `${baseClasses} bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300`,
        'completed': `${baseClasses} bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300`,
        'cancelled': `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300`
      }
      return statusClasses[status] || `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300`
    }

    const getStatusDotClass = (status) => {
      const statusClasses = {
        'pending': 'bg-yellow-400',
        'confirmed': 'bg-blue-400',
        'shipping': 'bg-purple-400',
        'completed': 'bg-green-400',
        'cancelled': 'bg-red-400'
      }
      return statusClasses[status] || 'bg-gray-400'
    }

    const getPaymentStatusClass = (status) => {
      const baseClasses = 'text-sm font-medium'
      const statusClasses = {
        'pending': `${baseClasses} text-yellow-600 dark:text-yellow-400`,
        'partial': `${baseClasses} text-blue-600 dark:text-blue-400`,
        'paid': `${baseClasses} text-green-600 dark:text-green-400`,
        'cancelled': `${baseClasses} text-red-600 dark:text-red-400`
      }
      return statusClasses[status] || `${baseClasses} text-gray-600 dark:text-gray-400`
    }

    const getPaymentStatusText = (status) => {
      const statusTexts = {
        'pending': 'Chờ thanh toán',
        'partial': 'Thanh toán một phần',
        'paid': 'Đã thanh toán',
        'cancelled': 'Đã hủy'
      }
      return statusTexts[status] || 'Không xác định'
    }

    const getItemTypeClass = (type) => {
      const baseClasses = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
      return type === 'product'
        ? `${baseClasses} bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-400`
        : `${baseClasses} bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-400`
    }

    const canUpdateStatus = computed(() => {
      return ['pending', 'confirmed'].includes(props.order.status)
    })

    const canProcessPayment = computed(() => {
      return props.order.invoice &&
        !['paid', 'cancelled'].includes(props.order.invoice.status) &&
        props.order.status !== 'cancelled';
    })

    const canCreateInvoice = computed(() => {
      return !props.order.invoice &&
        props.order.status !== 'cancelled' &&
        props.order.status !== 'completed'
    })

    const updateOrderStatus = async () => {
      loading.value = true;
      try {
        const response = await axios.put(route('orders.update', props.order.id), {
          status: newStatus.value,
          note: statusNote.value
        });

        if (response.data.success) {
          toast.success('Cập nhật trạng thái thành công');
          router.reload();
        }
      } catch (error) {
        console.error('Error updating order status:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
      } finally {
        loading.value = false;
        showStatusModal.value = false;
      }
    };

    const processPayment = async (paymentData) => {
      try {
        await axios.post(`/api/invoices/${props.order.invoice.id}/payments`, paymentData)
        showPaymentModal.value = false
        // Refresh page or update invoice data
      } catch (error) {
        console.error('Error processing payment:', error)
      }
    }

    const createInvoice = async () => {
      loading.value = true;
      try {
        const response = await axios.post(`/api/orders/${props.order.id}/create-invoice`, {
          note: invoiceNote.value
        });

        if (response.data.success) {
          toast.success('Hóa đơn đã được tạo thành công');

          // Chuyển hướng đến trang chi tiết hóa đơn mới
          router.visit(route('invoices.show', response.data.data.id));
        } else {
          throw new Error(response.data.message);
        }
      } catch (error) {
        console.error('Error creating invoice:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi tạo hóa đơn');
      } finally {
        loading.value = false;
        showCreateInvoiceModal.value = false;
        invoiceNote.value = '';
      }
    };

    const getStatusText = (status) => {
      const statusTexts = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang giao hàng',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy'
      }
      return statusTexts[status] || 'Không xác định'
    }

    const goToInvoice = () => {
      if (props.order.invoice) {
        router.visit(route('invoices.show', props.order.invoice.id))
      }
    }

    const openPaymentModal = () => {
      if (props.order.invoice) {
        router.visit(route('invoices.show', props.order.invoice.id))
      }
    }

    const openCreateInvoiceModal = () => {
      showCreateInvoiceModal.value = true
    }

    const getServiceTypeText = (type) => {
      const types = {
        'single': 'Đơn lẻ',
        'combo_5': 'Combo 5 lần',
        'combo_10': 'Combo 10 lần'
      }
      return types[type] || type
    }

    const openUpdateStatusModal = () => {
      newStatus.value = props.order.status;
      statusNote.value = props.order.note || '';
      showStatusModal.value = true;
    };

    // Chỉ hiển thị các trạng thái có thể chuyển đổi
    const getAvailableStatuses = computed(() => {
      const transitions = {
        'pending': ['confirmed', 'cancelled'],
        'confirmed': ['shipping', 'cancelled'],
        'shipping': ['completed'],
        'completed': [],
        'cancelled': []
      };
      
      return transitions[props.order.status] || [];
    });

    return {
      showStatusModal,
      showPaymentModal,
      showCreateInvoiceModal,
      newStatus,
      statusNote,
      invoiceNote,
      formatCurrency,
      formatDate,
      getStatusClass,
      getStatusDotClass,
      getPaymentStatusClass,
      getPaymentStatusText,
      getItemTypeClass,
      canUpdateStatus,
      canProcessPayment,
      canCreateInvoice,
      updateOrderStatus,
      processPayment,
      createInvoice,
      getStatusText,
      goToInvoice,
      openPaymentModal,
      openCreateInvoiceModal,
      getServiceTypeText,
      loading,
      openUpdateStatusModal,
      getAvailableStatuses
    }
  }
}
</script>