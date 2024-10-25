<template>
  <Head :title="'Chi tiết dịch vụ: ' + service.service_name" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Chi tiết dịch vụ
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-3xl font-bold text-gray-900">{{ service.service_name }}</h1>
              <BaseButtons type="justify-end" no-wrap>
                <BaseButton
                  color="info"
                  label="Quay lại"
                  :icon="mdiArrowLeft"
                  @click="goBack"
                />
                <BaseButton
                  :href="route('services.edit', service.id)"
                  color="info"
                  label="Chỉnh sửa"
                  :icon="mdiPencil"
                />
                <BaseButton
                  color="danger"
                  label="Xóa"
                  :icon="mdiTrashCan"
                  @click="confirmDelete"
                />
              </BaseButtons>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div>
                <img
                  :src="service.media && service.media.length > 0 ? service.media[0].url : 'https://via.placeholder.com/600x400'"
                  :alt="service.service_name"
                  class="w-full h-auto rounded-lg shadow-lg object-cover"
                />
              </div>
              <div class="space-y-4">
                <p class="text-gray-600">
                  <span class="font-semibold">Danh mục:</span>
                  {{ service.category ? service.category.service_category_name : 'Không có danh mục' }}
                </p>
                <p class="text-3xl font-bold text-green-600">
                  {{ formatPrice(service.single_price) }}
                </p>
                <p class="text-gray-700">
                  <span class="font-semibold">Thời gian:</span>
                  {{ service.duration }} phút
                </p>
                <p v-if="service.description" class="text-gray-700">
                  <span class="font-semibold">Mô tả:</span>
                  {{ service.description }}
                </p>
                <p class="text-gray-700">
                  <span class="font-semibold">Combo 5 lần:</span>
                  {{ formatPrice(service.combo_5_price) }}
                </p>
                <p class="text-gray-700">
                  <span class="font-semibold">Combo 10 lần:</span>
                  {{ formatPrice(service.combo_10_price) }}
                </p>
                <p class="text-gray-700">
                  <span class="font-semibold">Thời hạn sử dụng combo:</span>
                  {{ service.validity_period }} ngày
                </p>
              </div>
            </div>

            <!-- Remove the staff section as it's not present in the provided data -->

          </div>
        </div>
      </div>
    </div>

    <CardBoxModal
      v-model="showDeleteModal"
      title="Xác nhận xóa"
      button="danger"
      has-cancel
    >
      <p>Bạn có chắc chắn muốn xóa dịch vụ này?</p>
      <p class="text-sm text-gray-500 mt-2">
        Hành động này sẽ xóa mềm dịch vụ. Bạn có thể khôi phục nó sau này nếu cần.
      </p>
      <template #footer>
        <BaseButtons>
          <BaseButton label="Xóa dịch vụ" color="danger" @click="deleteService" :disabled="form.processing" />
          <BaseButton label="Hủy" color="info" @click="showDeleteModal = false" outline />
        </BaseButtons>
      </template>
    </CardBoxModal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { mdiPencil, mdiTrashCan, mdiArrowLeft } from '@mdi/js'
import AuthenticatedLayout from '@/Layouts/LayoutAuthenticated.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'

const props = defineProps({
  service: Object,
})

const showDeleteModal = ref(false)
const form = useForm({})

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const confirmDelete = () => {
  showDeleteModal.value = true
}

const deleteService = () => {
  form.delete(route('services.destroy', props.service.id), {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      showDeleteModal.value = false
    },
  })
}

const goBack = () => {
  router.visit(route('services.index'))
}
</script>
