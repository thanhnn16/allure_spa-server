<template>
    <LayoutAuthenticated>
      <Head :title="`Sửa đơn hàng #${order.id}`" />
      <SectionMain>
        <div class="container mx-auto px-4 py-8">
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">Sửa đơn hàng #{{ order.id }}</h1>
            <Link :href="route('orders.show', order.id)" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
              Quay lại
            </Link>
          </div>
  
          <form @submit.prevent="submitForm">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-6 gap-6">
                  <div class="col-span-6 sm:col-span-3">
                    <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                    <select id="status" v-model="form.status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      <option value="pending">Chờ xử lý</option>
                      <option value="processing">Đang xử lý</option>
                      <option value="completed">Hoàn thành</option>
                      <option value="cancelled">Đã hủy</option>
                    </select>
                  </div>
  
                  <div class="col-span-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Các mục trong đơn hàng</h3>
                    <div v-for="(item, index) in form.order_items" :key="index" class="mb-4 p-4 border rounded-md">
                      <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                          <label class="block text-sm font-medium text-gray-700">Sản phẩm/Dịch vụ</label>
                          <input type="text" v-model="item.item_name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" readonly />
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                          <label class="block text-sm font-medium text-gray-700">Loại</label>
                          <input type="text" :value="item.item_type === 'product' ? 'Sản phẩm' : 'Dịch vụ'" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" readonly />
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                          <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                          <input type="number" v-model.number="item.quantity" min="1" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" />
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                          <label class="block text-sm font-medium text-gray-700">Đơn giá</label>
                          <input type="number" v-model.number="item.price" min="0" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" />
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                          <label class="block text-sm font-medium text-gray-700">Thành tiền</label>
                          <input type="text" :value="formatCurrency(item.quantity * item.price)" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" readonly />
                        </div>
                      </div>
                    </div>
                  </div>
  
                  <div class="col-span-6">
                    <label for="note" class="block text-sm font-medium text-gray-700">Ghi chú</label>
                    <textarea id="note" v-model="form.note" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                  </div>
                </div>
              </div>
              <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  Lưu thay đổi
                </button>
              </div>
            </div>
          </form>
        </div>
      </SectionMain>
    </LayoutAuthenticated>
  </template>
  
  <script>
  import { Head, Link } from "@inertiajs/vue3";
  import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
  import SectionMain from '@/Components/SectionMain.vue'
  import { ref } from 'vue'
  import axios from 'axios'
  
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
    setup(props) {
      const form = ref({
        status: props.order.status,
        order_items: props.order.order_items,
        note: props.order.note,
      })
  
      const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
      }
  
      const submitForm = async () => {
        try {
          const response = await axios.put(`/api/orders/${props.order.id}`, form.value);
          // Handle successful update (e.g., show success message, redirect)
          console.log('Order updated:', response.data);
        } catch (error) {
          console.error('Error updating order:', error);
          // Handle error (e.g., show error message)
        }
      }
  
      return {
        form,
        formatCurrency,
        submitForm,
      }
    },
  }
  </script>