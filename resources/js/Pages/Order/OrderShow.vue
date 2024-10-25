<template>
    <LayoutAuthenticated>
      <Head :title="`Chi tiết đơn hàng #${order.id}`" />
      <SectionMain>
        <div class="container mx-auto px-4 py-8">
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">Chi tiết đơn hàng #{{ order.id }}</h1>
            <Link :href="route('orders.index')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
              Quay lại
            </Link>
          </div>
  
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900">Thông tin đơn hàng</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
              <dl class="sm:divide-y sm:divide-gray-200">
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">Khách hàng</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ order.user.full_name }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">Tổng tiền</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ formatCurrency(order.total_amount) }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">Trạng thái</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span :class="getStatusClass(order.status)">
                      {{ getStatusText(order.status) }}
                    </span>
                  </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">Ngày tạo</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ formatDate(order.created_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
  
          <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Các mục trong đơn hàng</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Sản phẩm/Dịch vụ
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Loại
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Số lượng
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Đơn giá
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Thành tiền
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in order.order_items" :key="item.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ item.item_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ item.item_type === 'product' ? 'Sản phẩm' : 'Dịch vụ' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ item.quantity }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatCurrency(item.price) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatCurrency(item.quantity * item.price) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </SectionMain>
    </LayoutAuthenticated>
  </template>
  
  <script>
  import { Head, Link } from "@inertiajs/vue3";
  import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
  import SectionMain from '@/Components/SectionMain.vue'
  
  export default {
    components: {
      Head,
      Link,
      LayoutAuthenticated,
      SectionMain,
    },
    props: {
      order: Object,
    },
    setup() {
      const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
      }
  
      const formatDate = (date) => {
        return new Date(date).toLocaleDateString('vi-VN');
      }
  
      const getStatusClass = (status) => {
        switch (status) {
          case 'pending':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
          case 'processing':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800';
          case 'completed':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
          case 'cancelled':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
          default:
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
        }
      }
  
      const getStatusText = (status) => {
        switch (status) {
          case 'pending':
            return 'Chờ xử lý';
          case 'processing':
            return 'Đang xử lý';
          case 'completed':
            return 'Hoàn thành';
          case 'cancelled':
            return 'Đã hủy';
          default:
            return 'Không xác định';
        }
      }
  
      return {
        formatCurrency,
        formatDate,
        getStatusClass,
        getStatusText,
      }
    },
  }
  </script>