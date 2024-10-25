<template>
  <CardBoxModal
    v-model="isModalActive"
    title="Xác nhận xóa sản phẩm"
    button="danger"
    has-cancel
    @confirm="deleteProduct"
    @cancel="$emit('close')"
  >
    <p class="text-sm text-gray-600">
      Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.
    </p>
  </CardBoxModal>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CardBoxModal from '@/Components/CardBoxModal.vue'

const props = defineProps({
  product: Object,
})

const emit = defineEmits(['close', 'product-deleted'])

const isModalActive = ref(true)
const form = useForm({})

const deleteProduct = () => {
  form.delete(route('products.destroy', props.product.id), {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      emit('product-deleted')
      isModalActive.value = false
    },
  })
}
</script>
