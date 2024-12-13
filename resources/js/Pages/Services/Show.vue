<template>
  <LayoutAuthenticated>
    <Head :title="'Chi tiết dịch vụ: ' + service.service_name" />
    <SectionMain :breadcrumbs="[
      { label: 'Danh sách dịch vụ', href: route('services.index') },
      { label: service.service_name }
    ]">
      <div class="flex justify-between items-center mb-6">
        <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" :title="'Chi tiết dịch vụ'" main />
      </div>

      <CardBox class="mb-6 dark:bg-dark-surface">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div class="bg-white dark:bg-dark-surface rounded-xl shadow-lg p-4">
              <Carousel v-if="service.media && service.media.length > 0" :items="processedMedia"
                :settings="carouselSettings" class="rounded-lg overflow-hidden">
              </Carousel>
              <img v-else src="/images/placeholder-service.jpg" :alt="service.service_name"
                class="w-full h-auto rounded-lg shadow">
            </div>
            <div class="flex flex-wrap gap-2">
              <BaseButton :icon="mdiImage" label="Quản lý ảnh" color="success"
                class="flex-grow md:flex-grow-0" @click="showManageImagesModal = true" />
              <BaseButton :icon="mdiPencil" label="Chỉnh sửa" color="info"
                class="flex-grow md:flex-grow-0" @click="showEditModal = true" />
              <BaseButton :icon="mdiTranslate" label="Quản lý bản dịch" color="warning"
                class="flex-grow md:flex-grow-0" @click="showTranslationsModal = true" />
              <BaseButton :icon="mdiDelete" label="Xóa" color="danger" class="flex-grow md:flex-grow-0"
                @click="showDeleteModal = true" />
            </div>
          </div>

          <div class="bg-white dark:bg-dark-surface rounded-xl shadow-lg p-6 space-y-6">
            <div class="border-b dark:border-dark-border pb-4">
              <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ service.service_name }}</h3>
              <p class="text-gray-600 dark:text-gray-400 mt-2">
                {{ service.category ? service.category.service_category_name : 'Không có danh mục' }}
              </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 dark:bg-dark-bg rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Giá dịch vụ đơn lẻ</h4>
                <p class="text-2xl font-bold text-green-600 dark:text-green-500">
                  {{ formatPrice(service.single_price) }}
                </p>
              </div>
              <div class="bg-gray-50 dark:bg-dark-bg rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Thời gian</h4>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ service.duration }} phút</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 dark:bg-dark-bg rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Combo 5 lần</h4>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                  {{ formatPrice(service.combo_5_price) }}
                </p>
              </div>
              <div class="bg-gray-50 dark:bg-dark-bg rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Combo 10 lần</h4>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                  {{ formatPrice(service.combo_10_price) }}
                </p>
              </div>
            </div>

            <div class="space-y-4">
              <div v-if="service.description" class="border-b dark:border-dark-border pb-4">
                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Mô tả</h4>
                <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ service.description }}</p>
              </div>
              <div class="border-b dark:border-dark-border pb-4">
                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Thời hạn sử dụng combo</h4>
                <p class="text-gray-600 dark:text-gray-400">{{ service.validity_period }} ngày</p>
              </div>
            </div>
          </div>
        </div>
      </CardBox>

      <!-- Price History Section -->
      <CardBox class="mb-6 dark:bg-dark-surface">
        <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Lịch sử giá</h3>
        <div class="overflow-x-auto">
          <template v-if="service.price_history && service.price_history.length > 0">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-border">
              <!-- ... (giữ nguyên phần bảng lịch sử giá) ... -->
            </table>
          </template>
          <p v-else class="text-gray-500 italic">Chưa có dữ liệu lịch sử giá.</p>
        </div>
      </CardBox>
    </SectionMain>

    <!-- Modals -->
    <EditServiceModal v-model="showEditModal" :service="service" @close="showEditModal = false"
      @service-updated="handleServiceUpdated" />

    <DeleteConfirmModal v-if="showDeleteModal" v-model="showDeleteModal" :service="service" @close="showDeleteModal = false"
      @service-deleted="handleServiceDeleted" />

    <ManageImagesModal v-model="showManageImagesModal" :service="service" @close="showManageImagesModal = false"
      @updated="handleImagesUpdated" />

    <TranslationsModal v-model="showTranslationsModal" :service="service" @close="showTranslationsModal = false"
      @translations-updated="handleTranslationsUpdated" />
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import Carousel from '@/Components/Carousel.vue'
import { mdiPackageVariantClosed, mdiPencil, mdiDelete, mdiImage, mdiTranslate } from '@mdi/js'

// Thêm các import cho modal components
import EditServiceModal from './Components/ServiceFormModal.vue'
import DeleteConfirmModal from './Components/DeleteConfirmModal.vue'
import ManageImagesModal from './Components/ManageImagesModal.vue'
import TranslationsModal from './Components/TranslationsModal.vue'

const props = defineProps({
  service: Object,
})

const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showManageImagesModal = ref(false)
const showTranslationsModal = ref(false)

const carouselSettings = {
  itemsToShow: 1,
  snapAlign: 'center',
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const processedMedia = computed(() => {
  return props.service.media?.map(item => ({
    ...item,
    file_path: item.file_path.startsWith('http') 
      ? item.file_path 
      : `/storage/${item.file_path.replace(/^\/+/, '')}`
  })) ?? []
})

const handleServiceUpdated = () => {
  showEditModal.value = false
  router.reload({ only: ['service'] })
}

const handleServiceDeleted = () => {
  showDeleteModal.value = false
  router.visit(route('services.index'))
}

const handleImagesUpdated = () => {
  router.reload({ only: ['service'] })
}

const handleTranslationsUpdated = () => {
  router.reload({ only: ['service'] })
}
</script>
