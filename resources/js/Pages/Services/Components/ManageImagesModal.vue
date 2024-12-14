<template>
  <CardBoxModal v-model="isActive" title="Quản lý hình ảnh dịch vụ" button="info" has-cancel @confirm="handleSubmit"
    @cancel="handleClose">
    <div class="space-y-6">
      <!-- Hiển thị ảnh hiện tại -->
      <div>
        <h3 class="text-lg font-medium mb-4">Ảnh hiện tại</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="(image, index) in currentImages" :key="index"
            class="relative group border-2 rounded-lg overflow-hidden"
            :class="{ 'border-green-500': image.position === 0, 'border-gray-200': image.position !== 0 }">
            <img :src="getImageUrl(image)" :alt="'Service image ' + index" class="w-full h-32 object-cover">
            <div
              class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
              <button @click.prevent="setAsMain(image.id)"
                class="text-white bg-green-600 p-2 rounded-full hover:bg-green-700"
                :class="{ 'opacity-50 cursor-not-allowed': image.position === 0 }" :disabled="image.position === 0">
                <BaseIcon :path="mdiStar" size="20" />
              </button>
              <button @click.prevent="deleteImage(image.id)"
                class="text-white bg-red-600 p-2 rounded-full hover:bg-red-700">
                <BaseIcon :path="mdiDelete" size="20" />
              </button>
            </div>
            <div v-if="image.position === 0"
              class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs">
              Ảnh chính
            </div>
          </div>
        </div>
      </div>

      <!-- Upload ảnh mới -->
      <div>
        <h3 class="text-lg font-medium mb-4">Thêm ảnh mới</h3>
        <div class="flex flex-wrap gap-4">
          <div v-for="(preview, index) in previews" :key="index"
            class="relative w-32 h-32 border rounded-lg overflow-hidden">
            <img :src="preview" class="w-full h-full object-cover">
            <button @click.prevent="removeNewImage(index)"
              class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
              <BaseIcon :path="mdiClose" size="16" />
            </button>
          </div>
          <div
            class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-indigo-500"
            @click="triggerFileInput">
            <BaseIcon :path="mdiPlus" size="24" class="text-gray-400" />
            <input type="file" ref="fileInput" class="hidden" multiple @change="handleImageUpload" accept="image/*">
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF tối đa 2MB</p>
      </div>
    </div>
  </CardBoxModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { mdiDelete, mdiPlus, mdiClose, mdiStar } from '@mdi/js'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const props = defineProps({
  modelValue: Boolean,
  service: Object,
})

const emit = defineEmits(['update:modelValue', 'updated'])
const toast = useToast()
const fileInput = ref(null)
const isSubmitting = ref(false)
const newImages = ref([])
const previews = ref([])

const isActive = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const currentImages = computed(() => {
  return [...(props.service?.media || [])].sort((a, b) => a.position - b.position)
})

const triggerFileInput = () => {
  fileInput.value.click()
}

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  const existingCount = newImages.value.length
  const totalCount = existingCount + files.length

  if (totalCount > 10) {
    toast.error('Không được upload quá 10 ảnh')
    return
  }

  files.forEach(file => {
    if (file.size > 2 * 1024 * 1024) {
      toast.error(`File ${file.name} vượt quá 2MB`)
      return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      previews.value.push(e.target.result)
    }
    reader.readAsDataURL(file)
    newImages.value.push(file)
  })
}

const removeNewImage = (index) => {
  newImages.value.splice(index, 1)
  previews.value.splice(index, 1)
}

const deleteImage = async (imageId) => {
  try {
    await axios.delete(route('media.destroy', imageId))
    toast.success('Xóa ảnh thành công')
    emit('updated')
  } catch (error) {
    toast.error('Không thể xóa ảnh')
    console.error('Error deleting image:', error)
  }
}

const handleSubmit = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true

  try {
    const formData = new FormData()
    newImages.value.forEach((file, index) => {
      formData.append(`images[${index}]`, file)
    })

    await axios.post(route('services.upload-images', props.service.id), formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    toast.success('Cập nhật ảnh thành công')
    emit('updated')
    handleClose()
  } catch (error) {
    toast.error('Không thể cập nhật ảnh')
    console.error('Error uploading images:', error)
  } finally {
    isSubmitting.value = false
  }
}

const handleClose = () => {
  newImages.value = []
  previews.value = []
  isActive.value = false
}

const getImageUrl = (image) => {
  if (!image || !image.file_path) return '/images/placeholder-service.jpg';

  if (image.file_path.startsWith('http')) {
    return image.file_path;
  }

  if (image.file_path.startsWith('storage/')) {
    return '/' + image.file_path;
  }

  return '/storage/' + image.file_path;
}

const setAsMain = async (imageId) => {
  try {
    await axios.put(route('media.reorder'), {
      items: currentImages.value.map(img => ({
        id: img.id,
        position: img.id === imageId ? 0 : (img.position === 0 ? 1 : img.position)
      }))
    })

    toast.success('Đã đặt làm ảnh chính')
    emit('updated')
  } catch (error) {
    console.error('Error setting main image:', error)
    toast.error('Không thể đặt làm ảnh chính')
  }
}
</script>

<style scoped>
.border-2 {
  border-width: 2px;
}
</style>