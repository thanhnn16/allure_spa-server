<template>
  <CardBoxModal
    :model-value="modelValue"
    @update:model-value="updateModal"
    title="Quản lý hình ảnh"
    button="primary"
    button-label="Đóng"
    has-cancel
    @confirm="closeModal"
    @cancel="closeModal"
  >
    <div class="space-y-6">
      <!-- Current Images -->
      <div v-if="service.media && service.media.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div v-for="image in service.media" :key="image.id" class="relative group">
          <img :src="image.file_path.startsWith('http') ? image.file_path : `/storage/${image.file_path}`"
            :alt="service.service_name"
            class="w-full h-48 object-cover rounded-lg">
          <button @click="deleteImage(image.id)"
            class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
            <i class="mdi mdi-close text-base"></i>
          </button>
        </div>
      </div>
      <p v-else class="text-gray-500 dark:text-gray-400 text-center">
        Chưa có hình ảnh nào
      </p>

      <!-- Upload Form -->
      <div class="space-y-4">
        <div class="flex items-center justify-center w-full">
          <label class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-dark-bg dark:bg-dark-surface hover:bg-gray-100">
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
              <i class="mdi mdi-upload text-4xl mb-4 text-gray-500 dark:text-gray-400"></i>
              <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                <span class="font-semibold">Nhấp để tải lên</span> hoặc kéo và thả
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                PNG, JPG hoặc JPEG (Tối đa 10MB)
              </p>
            </div>
            <input type="file" class="hidden" multiple accept="image/*" @change="handleFileUpload" ref="fileInput">
          </label>
        </div>

        <div v-if="uploadProgress > 0" class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
          <div class="bg-primary-600 h-2.5 rounded-full" :style="{ width: `${uploadProgress}%` }"></div>
        </div>
      </div>
    </div>
  </CardBoxModal>
</template>

<script setup>
import { ref } from 'vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'

const props = defineProps({
  modelValue: Boolean,
  service: Object,
})

const emit = defineEmits(['update:modelValue', 'close', 'updated'])

const fileInput = ref(null)
const uploadProgress = ref(0)

const updateModal = (value) => {
  emit('update:modelValue', value)
}

const closeModal = () => {
  uploadProgress.value = 0
  emit('close')
}

const handleFileUpload = async (event) => {
  const files = event.target.files
  if (!files.length) return

  const formData = new FormData()
  Array.from(files).forEach(file => {
    formData.append('images[]', file)
  })

  try {
    const response = await fetch(`/api/services/${props.service.id}/images`, {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
      }
    })

    if (!response.ok) throw new Error('Upload failed')

    emit('updated')
    fileInput.value.value = ''
    uploadProgress.value = 0
  } catch (error) {
    console.error('Error uploading images:', error)
    // Handle error (show notification, etc.)
  }
}

const deleteImage = async (imageId) => {
  try {
    const response = await fetch(`/api/media/${imageId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      }
    })

    if (!response.ok) throw new Error('Delete failed')

    emit('updated')
  } catch (error) {
    console.error('Error deleting image:', error)
    // Handle error (show notification, etc.)
  }
}
</script> 