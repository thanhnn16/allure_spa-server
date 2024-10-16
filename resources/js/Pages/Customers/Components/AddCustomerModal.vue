<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Thêm khách hàng mới</h3>
          <div class="text-sm text-gray-500 mb-4">
            <span class="text-red-500">*</span> Trường bắt buộc
          </div>
          <NotificationBar v-if="errorMessage" color="danger" :icon="mdiAlertCircle" class="mb-4">
            {{ errorMessage }}
          </NotificationBar>
          <form @submit.prevent="addCustomer">
            <div class="mb-4">
              <label for="full_name" class="block text-sm font-medium text-gray-700">
                Họ và tên <span class="text-red-500">*</span>
              </label>
              <input type="text" id="full_name" v-model="form.full_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div class="mb-4">
              <label for="phone_number" class="block text-sm font-medium text-gray-700">
                Số điện thoại
              </label>
              <input type="tel" id="phone_number" v-model="form.phone_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div class="mb-4">
              <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
              <input type="email" id="email" v-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div class="mb-4">
              <label for="gender" class="block text-sm font-medium text-gray-700">
                Giới tính <span class="text-red-500">*</span>
              </label>
              <select id="gender" v-model="form.gender" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="male">Nam</option>
                <option value="female">Nữ</option>
                <option value="other">Khác</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Ngày sinh</label>
              <input type="date" id="date_of_birth" v-model="form.date_of_birth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
          </form>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button @click="addCustomer" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" :disabled="form.processing">
            <span v-if="!form.processing">Thêm khách hàng</span>
            <span v-else>Đang xử lý...</span>
          </button>
          <button @click="$emit('update:modelValue', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" :disabled="form.processing">
            Hủy
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import NotificationBar from '@/Components/NotificationBar.vue';
import { mdiAlertCircle } from '@mdi/js';
import { useMainStore } from '@/Stores/main';

const props = defineProps({
  modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue', 'customerAdded']);

const form = useForm({
  full_name: '',
  phone_number: '',
  email: '',
  gender: 'other',
  date_of_birth: '',
});

const errorMessage = ref('');

const mainStore = useMainStore();

const addCustomer = () => {
  form.post(route('users.store'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('customerAdded');
      emit('update:modelValue', false);
      form.reset();
      errorMessage.value = '';
      mainStore.fetchUsers()
    },
    onError: (errors) => {
      errorMessage.value = Object.values(errors).join('\n');
    },
  });
};
</script>
