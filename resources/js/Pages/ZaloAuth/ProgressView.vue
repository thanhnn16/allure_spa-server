<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="p-8 bg-white rounded-lg shadow-lg text-center">
      <div class="w-16 h-16 mx-auto mb-4 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin"></div>
      <p class="text-lg text-gray-700 font-medium">{{ message }}</p>
      <div v-if="error" class="mt-4 text-sm text-red-500">{{ error }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const message = ref('Đang xử lý đăng nhập...');
const error = ref('');

onMounted(async () => {
  const code = route.query.code;
  const state = route.query.state;
  
  if (code && state) {
    try {
      // Thử mở app
      const result = await openApp(code, state);
      if (!result) {
        // Nếu không mở được app, xử lý trên web
        handleWebAuth(code, state);
      }
    } catch (err) {
      message.value = 'Có lỗi xảy ra khi đăng nhập';
      error.value = err.message;
    }
  } else {
    error.value = 'Không tìm thấy thông tin xác thực';
  }
});

const openApp = async (code, state) => {
  try {
    window.location.href = `allurespa://zalo-oauth?code=${code}&state=${state}`;
    return true;
  } catch {
    return false;
  }
};

const handleWebAuth = (code, state) => {
  // Xử lý auth trên web
  message.value = 'Đang xử lý xác thực qua web...';
  // Thêm logic xử lý auth qua web ở đây
};
</script>
