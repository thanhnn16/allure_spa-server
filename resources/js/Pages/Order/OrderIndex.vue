 
<template>
    <LayoutAuthenticated>
      <Head title="Quản lý đơn hàng" />
      <SectionMain>
        <div class="container mx-auto px-4 py-8">
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">Quản lý đơn hàng</h1>
            <Link :href="route('orders.create')" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
              Tạo đơn hàng mới
            </Link>
          </div>
  
          <!-- Bộ lọc và tìm kiếm -->
          <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
              <select v-model="filters.status" class="form-select rounded-md shadow-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ xử lý</option>
                <option value="processing">Đang xử lý</option>
                <option value="completed">Hoàn thành</option>
                <option value="cancelled">Đã hủy</option>
              </select>
              <input v-model="filters.search" type="text" placeholder="Tìm kiếm theo ID hoặc tên khách hàng"
                class="form-input rounded-md shadow-sm" />
            </div>
            <button @click="applyFilters" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
              Áp dụng bộ lọc
            </button>
          </div>
  
          <!-- Bảng hiển thị đơn hàng -->
          <div v-if="orders.length > 0" class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full leading-normal">
              <thead>
                <tr>
                  <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    ID Đơn hàng
                  </th>
                  <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Khách hàng
                  </th>
                  <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Tổng tiền
                  </th>
                  <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Trạng thái
                  </th>
                  <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Ngày tạo
                  </th>
                  <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Hành động
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders" :key="order.id">
                  <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    {{ order.id }}
                  </td>
                  <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    {{ order.user.full_name }}
                  </td>
                  <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    {{ formatCurrency(order.total_amount) }}
                  </td>
                  <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <span :class="getStatusClass(order.status)">
                      {{ getStatusText(order.status) }}
                    </span>
                  </td>
                  <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    {{ formatDate(order.created_at) }}
                  </td>
                  <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <Link :href="route('orders.show', order.id)" class="text-blue-600 hover:text-blue-900 mr-2">
                      Xem
                    </Link>
                    <Link :href="route('orders.edit', order.id)" class="text-green-600 hover:text-green-900 mr-2">
                      Sửa
                    </Link>
                    <button @click="deleteOrder(order.id)" class="text-red-600 hover:text-red-900">
                      Xóa
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="bg-white shadow-md rounded-lg p-8 text-center">
            <p class="text-gray-600 text-lg">Chưa có dữ liệu đơn hàng.</p>
          </div>
  
          <!-- Phân trang -->
          <div class="mt-6">
            <Pagination :links="links" />
          </div>
        </div>
      </SectionMain>
    </LayoutAuthenticated>
  </template>
  
  <script>
  import { Head, Link } from "@inertiajs/vue3";
  import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
  import SectionMain from '@/Components/SectionMain.vue'
  import Pagination from '@/Components/Pagination.vue'
  import { ref } from 'vue'
  import axios from 'axios'
  
  export default {
    components: {
      Head,
      Link,
      LayoutAuthenticated,
      SectionMain,
      Pagination,
    },
    props: {
      orders: Array,
      links: Array,
    },
    setup() {
      const filters = ref({
        status: '',
        search: '',
      })
  
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
  
      const applyFilters = () => {
        // Implement filter logic here
      }
  
      const deleteOrder = async (orderId) => {
        if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
          try {
            await axios.delete(`/api/orders/${orderId}`);
            // Refresh the orders list after deletion
            // You might want to use Inertia to reload the page or update the orders list
          } catch (error) {
            console.error('Error deleting order:', error);
          }
        }
      }
  
      return {
        filters,
        formatCurrency,
        formatDate,
        getStatusClass,
        getStatusText,
        applyFilters,
        deleteOrder,
      }
    },
  }
  </script>