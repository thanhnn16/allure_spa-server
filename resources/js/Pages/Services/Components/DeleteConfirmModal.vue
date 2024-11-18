<template>
  <CardBoxModal
    :model-value="modelValue"
    @update:model-value="updateModal"
    title="Xác nhận xóa"
    button="danger"
    button-label="Xóa"
    has-cancel
    @confirm="handleDelete"
    @cancel="closeModal"
  >
    <p class="text-gray-600 dark:text-gray-400">
      Bạn có chắc chắn muốn xóa dịch vụ "{{ service.service_name }}" không? Hành động này không thể hoàn tác.
    </p>
  </CardBoxModal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import CardBoxModal from '@/Components/CardBoxModal.vue'

const props = defineProps({
  modelValue: Boolean,
  service: Object,
})

const emit = defineEmits(['update:modelValue', 'close', 'service-deleted'])

const form = useForm({})

const updateModal = (value) => {
  emit('update:modelValue', value)
}

const closeModal = () => {
  emit('close')
}

const handleDelete = () => {
  form.delete(route('services.destroy', props.service.id), {
    onSuccess: () => {
      emit('service-deleted')
    },
  })
}
</script> 