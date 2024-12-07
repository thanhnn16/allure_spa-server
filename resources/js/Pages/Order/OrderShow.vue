<template>
  <LayoutAuthenticated>

    <Head :title="`Chi tiết đơn hàng #${order.id}`" />
    <SectionMain :breadcrumbs="[
      { label: 'Quản lý đơn hàng', href: route('orders.index') },
      { label: `Đơn hàng #${order.id}` }
    ]">
      <!-- Grid layout cho thông tin đơn hàng -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cột trái: Thông tin chính và sản phẩm -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Card thông tin cơ bản -->
          <CardBox>
            <template #header>
              <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium">Thông tin đơn hàng #{{ order.id }}</h3>
                <StatusBadge :status="order.status" />
              </div>
            </template>

            <div class="grid grid-cols-2 gap-4">
              <!-- Thông tin khách hàng -->
              <div>
                <h4 class="font-medium mb-2">Thông tin khách hàng</h4>
                <div class="space-y-2">
                  <p><span class="text-gray-500">Họ tên:</span> {{ order.user.full_name }}</p>
                  <p><span class="text-gray-500">Email:</span> {{ order.user.email }}</p>
                  <p><span class="text-gray-500">Điện thoại:</span> {{ order.user.phone_number }}</p>
                </div>
              </div>

              <!-- Thông tin đơn hàng -->
              <div>
                <h4 class="font-medium mb-2">Thông tin đặt hàng</h4>
                <div class="space-y-2">
                  <p><span class="text-gray-500">Ngày đặt:</span> {{ formatDateTime(order.created_at) }}</p>
                  <p><span class="text-gray-500">Phương thức:</span> {{ order.payment_method?.method_name }}</p>
                  <p v-if="order.voucher"><span class="text-gray-500">Mã giảm giá:</span> {{ order.voucher.code }}</p>
                </div>
              </div>
            </div>

            <!-- Địa chỉ giao hàng -->
            <div class="mt-4 pt-4 border-t dark:border-dark-border">
              <h4 class="font-medium mb-2">Địa chỉ giao hàng</h4>
              <div v-if="order.shipping_address" class="bg-gray-50 dark:bg-dark-bg/50 p-3 rounded-lg">
                <p class="font-medium">{{ order.shipping_address.address }}</p>
                <p class="text-gray-600 dark:text-gray-400">
                  {{ formatAddress(order.shipping_address) }} ({{ order.shipping_address.ward.name }}, {{
                    order.shipping_address.district.name }}, {{ order.shipping_address.province.name }})
                </p>
              </div>
              <p v-else class="text-gray-500 text-sm">Chưa có địa chỉ giao hàng</p>
            </div>
          </CardBox>

          <!-- Card danh sách sản phẩm -->
          <CardBox>
            <template #header>
              <h3 class="text-lg font-medium">Danh sách sản phẩm/dịch vụ</h3>
            </template>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y dark:divide-dark-border">
                <thead>
                  <tr class="bg-gray-50 dark:bg-dark-bg/50">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Sản phẩm/Dịch vụ
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Đơn giá
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Số lượng
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Thành tiền
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y dark:divide-dark-border">
                  <tr v-for="item in order.order_items" :key="item.id"
                    class="hover:bg-gray-50 dark:hover:bg-dark-bg/30">
                    <td class="px-4 py-4">
                      <div class="flex items-center">
                        <img v-if="getItemImage(item)" :src="getItemImage(item)"
                          class="h-10 w-10 rounded-lg object-cover mr-3">
                        <div>
                          <div class="font-medium">{{ getItemName(item) }}</div>
                          <div class="text-sm text-gray-500">
                            <ItemTypeBadge :type="item.item_type" />
                            <span v-if="item.service_type" class="ml-2">
                              {{ getServiceTypeText(item.service_type) }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-4">{{ formatCurrency(item.price) }}</td>
                    <td class="px-4 py-4">{{ item.quantity }}</td>
                    <td class="px-4 py-4 font-medium">
                      {{ formatCurrency(item.price * item.quantity) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-dark-bg/50">
                  <tr>
                    <td colspan="3" class="px-4 py-3 text-right font-medium">Tổng tiền:</td>
                    <td class="px-4 py-3 font-medium">{{ formatCurrency(order.total_amount) }}</td>
                  </tr>
                  <tr v-if="order.discount_amount">
                    <td colspan="3" class="px-4 py-3 text-right font-medium">Giảm giá:</td>
                    <td class="px-4 py-3 font-medium text-green-600">
                      -{{ formatCurrency(order.discount_amount) }}
                    </td>
                  </tr>
                  <tr class="border-t dark:border-dark-border">
                    <td colspan="3" class="px-4 py-3 text-right font-medium">Thành tiền:</td>
                    <td class="px-4 py-3 font-medium text-lg">
                      {{ formatCurrency(order.total_amount - order.discount_amount) }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </CardBox>

          <!-- Timeline đơn hàng -->
          <CardBox v-if="orderTimeline.length">
            <template #header>
              <h3 class="text-lg font-medium">Lịch sử đơn hàng</h3>
            </template>

            <div class="flow-root">
              <ul role="list" class="-mb-8">
                <li v-for="(event, eventIdx) in orderTimeline" :key="event.id">
                  <div class="relative pb-8">
                    <span v-if="eventIdx !== orderTimeline.length - 1"
                      class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-dark-border"
                      aria-hidden="true" />
                    <div class="relative flex space-x-3">
                      <div>
                        <span :class="[
                          'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-dark-surface',
                          event.iconBackground
                        ]">
                          <i :class="[event.iconClass, 'text-white']" aria-hidden="true"></i>
                        </span>
                      </div>
                      <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                        <div>
                          <p class="text-sm text-gray-500">
                            {{ event.content }}
                          </p>
                        </div>
                        <div class="whitespace-nowrap text-right text-sm text-gray-500">
                          <time :datetime="event.datetime">{{ formatDateTime(event.datetime) }}</time>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </CardBox>
        </div>

        <!-- Cột phải: Thông tin thanh toán và actions -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Card trạng thái và actions -->
          <CardBox>
            <div class="space-y-4">
              <!-- Phần trạng thái -->
              <div>
                <h3 class="text-lg font-medium mb-3">Trạng thái đơn hàng</h3>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Trạng thái hiện tại</span>
                  <StatusBadge :status="order.status" class="px-3 py-1" />
                </div>
              </div>

              <!-- Phần actions -->
              <div class="space-y-3 pt-3 border-t dark:border-dark-border">
                <button v-if="canUpdateStatus" @click="openUpdateStatusModal"
                  class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium flex items-center justify-center transition-colors duration-200">
                  <i class="mdi mdi-pencil-outline mr-2"></i>
                  Cập nhật trạng thái
                </button>

                <button v-if="canCancel" @click="openCancelModal"
                  class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-medium flex items-center justify-center transition-colors duration-200">
                  <i class="mdi mdi-close-circle-outline mr-2"></i>
                  Hủy đơn hàng
                </button>

                <Link v-if="hasCreatedServicePackages" :href="route('users.show', order.user_id)"
                  class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-medium flex items-center justify-center transition-colors duration-200">
                  <i class="mdi mdi-package-variant mr-2"></i>
                  Xem gói dịch vụ người dùng
                </Link>

                <button v-if="needsServicePackageCreation && !hasCreatedServicePackages" @click="openCompleteModal"
                  class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-medium flex items-center justify-center transition-colors duration-200">
                  <i class="mdi mdi-check-circle-outline mr-2"></i>
                  {{ order.status === 'completed' ? 'Tạo gói dịch vụ' : 'Hoàn thành đơn hàng' }}
                </button>
              </div>
            </div>
          </CardBox>


          <!-- Card thông tin thanh toán -->
          <CardBox>
            <template #header>
              <h3 class="text-lg font-medium">Thông tin thanh toán</h3>
            </template>

            <!-- Khi đã có hóa đơn -->
            <div v-if="order.invoice" class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-500">Trạng thái</span>
                <PaymentStatusBadge :status="order.invoice.status" />
              </div>

              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-500">Tổng tiền</span>
                  <span class="font-medium">{{ formatCurrency(order.invoice.total_amount) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Đã thanh toán</span>
                  <span class="text-green-600 font-medium">
                    {{ formatCurrency(order.invoice.paid_amount) }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Còn lại</span>
                  <span class="text-red-600 font-medium">
                    {{ formatCurrency(order.invoice.remaining_amount) }}
                  </span>
                </div>
              </div>

              <Link :href="route('invoices.show', order.invoice.id)"
                class="flex items-center justify-center px-4 py-2.5 font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 transition-colors duration-200 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-surface">
              <i class="mdi mdi-file-document-outline text-xl mr-2"></i>
              Xem chi tiết hóa đơn
              </Link>
            </div>

            <!-- Khi chưa có hóa đơn -->
            <div v-else class="text-center py-6">
              <div class="mb-4">
                <i class="mdi mdi-file-document-plus-outline text-5xl text-gray-400"></i>
              </div>
              <p class="text-gray-500 mb-4">Chưa có hóa đơn cho đơn hàng này</p>
              <button v-if="canCreateInvoice" @click="openCreateInvoiceModal"
                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center transition-colors duration-200">
                <i class="mdi mdi-plus-circle-outline mr-2 text-xl"></i>
                Tạo hóa đơn mới
              </button>
            </div>
          </CardBox>
        </div>
      </div>

      <!-- Các modal -->
      <UpdateStatusModal v-model="showStatusModal" :order="order" :available-statuses="getAvailableStatuses"
        @updated="handleStatusUpdated" />

      <CreateInvoiceModal v-model="showCreateInvoiceModal" :order="order" @created="handleInvoiceCreated" />

      <CompleteOrderModal v-model="showCompleteModal" :order="order" :has-service-combo="hasServiceCombo"
        @completed="handleOrderCompleted" />

      <CancelOrderModal v-model="showCancelModal" :order="order" :title="`Hủy đơn hàng #${order.id}`"
        @cancelled="handleOrderCancelled" />
    </SectionMain>
  </LayoutAuthenticated>
</template>

<script>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from "@inertiajs/vue3"
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import PaymentStatusBadge from '@/Components/PaymentStatusBadge.vue'
import ItemTypeBadge from '@/Components/ItemTypeBadge.vue'
import UpdateStatusModal from '@/Components/UpdateStatusModal.vue'
import CreateInvoiceModal from '@/Components/CreateInvoiceModal.vue'
import CompleteOrderModal from '@/Components/CompleteOrderModal.vue'
import CancelOrderModal from '@/Components/CancelOrderModal.vue'
import axios from 'axios'
import { useToast } from "vue-toastification"
import BaseIcon from '@/Components/BaseIcon.vue'

export default {
  name: 'OrderShow',
  components: {
    Head,
    Link,
    LayoutAuthenticated,
    SectionMain,
    CardBox,
    StatusBadge,
    PaymentStatusBadge,
    ItemTypeBadge,
    UpdateStatusModal,
    CreateInvoiceModal,
    CompleteOrderModal,
    CancelOrderModal,
    BaseIcon,
  },
  props: {
    order: Object,
  },
  setup(props) {
    const showStatusModal = ref(false)
    const showPaymentModal = ref(false)
    const showCreateInvoiceModal = ref(false)
    const showCompleteModal = ref(false)
    const showCancelModal = ref(false)
    const newStatus = ref(props.order.status)
    const statusNote = ref('')
    const invoiceNote = ref('')
    const completeNote = ref('')
    const cancelNote = ref('')
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
      return ['pending', 'confirmed', 'shipping'].includes(props.order.status)
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

    const canComplete = computed(() => {
      return ['confirmed', 'shipping'].includes(props.order.status) &&
        props.order.invoice?.status === 'paid'
    })

    const hasServiceCombo = computed(() => {
      return props.order.order_items.some(item =>
        item.item_type === 'service' && ['combo_5', 'combo_10'].includes(item.service_type)
      )
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

    const openCompleteModal = () => {
      showCompleteModal.value = true
      completeNote.value = ''
    }

    const openCancelModal = () => {
      showCancelModal.value = true
      cancelNote.value = ''
    }

    const completeOrder = async () => {
      loading.value = true
      try {
        const response = await axios.post(`/api/orders/${props.order.id}/complete`, {
          note: completeNote.value
        })

        if (response.data.success) {
          toast.success('Đơn hàng đã được hoàn thành thành công')
          if (hasServiceCombo.value) {
            toast.info('Các gói liệu trình đã được tạo cho khách hàng')
          }
          router.reload()
        }
      } catch (error) {
        console.error('Error completing order:', error)
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi hoàn thành đơn hàng')
      } finally {
        loading.value = false
        showCompleteModal.value = false
      }
    }

    const cancelOrder = async () => {
      loading.value = true
      try {
        const response = await axios.post(`/api/orders/${props.order.id}/cancel`, {
          note: cancelNote.value
        })

        if (response.data.success) {
          toast.success('Đơn hàng đã được hủy thành công')
          router.reload()
        }
      } catch (error) {
        console.error('Error canceling order:', error)
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi hủy đơn hàng')
      } finally {
        loading.value = false
        showCancelModal.value = false
      }
    }

    // Chỉ hiển thị các trạng thái có thể chuyển đổi
    const getAvailableStatuses = computed(() => {
      const transitions = {
        'pending': ['confirmed', 'cancelled'],
        'confirmed': ['shipping', 'completed', 'cancelled'],
        'shipping': ['completed', 'cancelled'],
        'completed': [],
        'cancelled': []
      };

      return transitions[props.order.status] || [];
    });

    // Add orderTimeline computed property
    const orderTimeline = computed(() => {
      const timeline = [];

      // Add order creation event
      timeline.push({
        id: 'created',
        content: 'Đơn hàng được tạo',
        datetime: props.order.created_at,
        iconClass: 'mdi mdi-plus-circle',
        iconBackground: 'bg-blue-500'
      });

      // Add status changes if any
      if (props.order.status_histories?.length) {
        props.order.status_histories.forEach(history => {
          timeline.push({
            id: history.id,
            content: `Trạng thái đơn hàng thay đổi thành "${getStatusText(history.status)}"${history.note ? ` - ${history.note}` : ''}`,
            datetime: history.created_at,
            iconClass: 'mdi mdi-clipboard-check',
            iconBackground: 'bg-green-500'
          });
        });
      }

      // Add completion/cancellation event if applicable
      if (props.order.status === 'completed') {
        timeline.push({
          id: 'completed',
          content: 'Đơn hàng hoàn thành',
          datetime: props.order.completed_at || props.order.updated_at,
          iconClass: 'mdi mdi-check-circle',
          iconBackground: 'bg-green-600'
        });
      } else if (props.order.status === 'cancelled') {
        timeline.push({
          id: 'cancelled',
          content: 'Đơn hàng đã hủy',
          datetime: props.order.cancelled_at || props.order.updated_at,
          iconClass: 'mdi mdi-close-circle',
          iconBackground: 'bg-red-600'
        });
      }

      // Sort timeline by datetime
      return timeline.sort((a, b) => new Date(b.datetime) - new Date(a.datetime));
    });

    // Thêm hàm formatDateTime
    const formatDateTime = (datetime) => {
      return new Date(datetime).toLocaleString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    // Thêm các hàm xử lý sự kiện
    const handleStatusUpdated = async (data) => {
      loading.value = true;
      try {
        const response = await axios.put(route('orders.update', props.order.id), {
          status: data.status,
          note: data.note
        });

        if (response.data.success) {
          toast.success('Cập nhật trạng thái thành công');
          router.reload();
        } else {
          throw new Error(response.data.message || 'Có lỗi xảy ra');
        }
      } catch (error) {
        console.error('Error updating order status:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
      } finally {
        loading.value = false;
        showStatusModal.value = false;
      }
    }

    const handleInvoiceCreated = async (data) => {
      loading.value = true;
      try {
        const response = await axios.post(`/api/orders/${props.order.id}/create-invoice`, {
          note: data.note
        });

        if (response.data.success) {
          toast.success('Tạo hóa đơn thành công');
          if (response.data.data?.id) {
            router.visit(route('invoices.show', response.data.data.id));
          } else {
            router.reload();
          }
        } else {
          throw new Error(response.data.message || 'Có lỗi xảy ra');
        }
      } catch (error) {
        console.error('Error creating invoice:', error);
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi tạo hóa đơn');
      } finally {
        loading.value = false;
        showCreateInvoiceModal.value = false;
      }
    };

    const handleOrderCompleted = () => {
      toast.success('Đơn hàng đã được hoàn thành thành công', {
        timeout: 3000
      });

      if (hasServiceCombo.value) {
        toast.info('Các gói liệu trình đã được tạo cho khách hàng', {
          timeout: 4000
        });
      }

      router.reload();
    }

    const handleOrderCancelled = () => {
      router.reload();
    }

    // Thêm các hàm helper cho item
    const getItemName = (item) => {
      return item.item_name;
    }

    const getItemImage = (item) => {
      return item.product?.image || item.service?.image || null;
    }

    // Thêm computed property canCancel
    const canCancel = computed(() => {
      return ['pending', 'confirmed'].includes(props.order.status) &&
        (!props.order.invoice || props.order.invoice.status === 'pending')
    })

    // Watch cho modal states nếu cần
    watch(() => showStatusModal.value, (newVal) => {
      if (!newVal) {
        statusNote.value = ''
        newStatus.value = props.order.status
      }
    })

    watch(() => showCancelModal.value, (newVal) => {
      if (!newVal) {
        cancelNote.value = ''
      }
    })

    // Thêm hàm formatAddress
    const formatAddress = (address) => {
      if (!address) return '';
      return `${address.ward}, ${address.district}, ${address.province}`;
    }

    // Cập nhật computed property needsServicePackageCreation
    const needsServicePackageCreation = computed(() => {
      // Kiểm tra nếu đơn hàng đã hoàn thành nhưng chưa có gói dịch vụ

      if (props.order.status === 'completed') {
        const hasServicePackages = props.order.userServicePackages?.some(
          userServicePackage => userServicePackage.order_id === props.order.id
        );
        return !hasServicePackages && hasServiceCombo.value;
      }

      // Kiểm tra nếu đơn hàng có thể hoàn thành
      return ['confirmed', 'shipping'].includes(props.order.status) &&
        props.order.invoice?.status === 'paid' &&
        hasServiceCombo.value;
    });

    const hasCreatedServicePackages = computed(() => {

      // Kiểm tra từng điều kiện và log kết quả
      const isCompleted = props.order.status === 'completed';

      const hasCombo = hasServiceCombo.value;

      // Kiểm tra gói dịch vụ thông qua user
      const userPackages = props.order.user?.user_service_packages || [];
      const hasPackages = userPackages.some(userPackage => userPackage.order_id === props.order.id);


      // Log kết quả cuối cùng
      const result = isCompleted && hasCombo && hasPackages;

      return result;
    });

    return {
      showStatusModal,
      showPaymentModal,
      showCreateInvoiceModal,
      showCompleteModal,
      showCancelModal,
      newStatus,
      statusNote,
      invoiceNote,
      completeNote,
      cancelNote,
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
      canComplete,
      hasServiceCombo,
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
      getAvailableStatuses,
      openCompleteModal,
      openCancelModal,
      completeOrder,
      cancelOrder,
      orderTimeline,
      formatDateTime,
      handleStatusUpdated,
      handleInvoiceCreated,
      handleOrderCompleted,
      handleOrderCancelled,
      getItemName,
      getItemImage,
      canCancel,
      formatAddress,
      needsServicePackageCreation,
      hasCreatedServicePackages,
    }
  }
}
</script>
