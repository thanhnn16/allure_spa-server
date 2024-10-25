<template>
  <CardBoxModal
    v-model="isModalActive"
    title="Chỉnh sửa sản phẩm"
    button="success"
    has-cancel
    @confirm="updateProduct"
    @cancel="$emit('close')"
  >
    <form @submit.prevent="updateProduct" class="space-y-4">
      <div class="flex flex-col">
        <label for="name" class="mb-2 font-medium text-gray-700">Tên sản phẩm</label>
        <input id="name" v-model="form.name" type="text" class="form-input rounded-md shadow-sm">
      </div>
      <div class="flex flex-col">
        <label for="price" class="mb-2 font-medium text-gray-700">Giá</label>
        <input id="price" v-model="form.price" type="number" step="0.01" class="form-input rounded-md shadow-sm">
      </div>
      <!-- Thêm các trường khác tại đây -->
    </form>
  </CardBoxModal>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CardBoxModal from '@/Components/CardBoxModal.vue'

const props = defineProps({
  product: Object,
})

const emit = defineEmits(['close', 'product-updated'])

const isModalActive = ref(true)
const form = useForm({
  name: props.product.name,
  price: props.product.price,
  // Khởi tạo các trường khác tại đây
})

const updateProduct = () => {
  form.put(route('products.update', props.product.id), {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      emit('product-updated')
      isModalActive.value = false
    },
  })
}
</script>
