<template>
  <CardBoxModal :model-value="modelValue" @update:model-value="updateModal"
    :title="isEditing ? 'Chỉnh sửa dịch vụ' : 'Thêm dịch vụ mới'" button="primary"
    :button-label="isEditing ? 'Cập nhật' : 'Thêm mới'" has-cancel @confirm="handleSubmit" @cancel="closeModal">
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tên dịch vụ -->
        <div>
          <label class="form-label required">Tên dịch vụ</label>
          <input type="text" v-model="form.service_name" class="form-input" required />
          <div v-if="form.errors.service_name" class="form-error">
            {{ form.errors.service_name }}
          </div>
        </div>

        <!-- Danh mục -->
        <div>
          <label class="form-label required">Danh mục</label>
          <select v-model="form.category_id" class="form-select" required>
            <option value="">Chọn danh mục</option>
            <option v-for="category in props.categories" :key="category.id" :value="category.id">
              {{ category.service_category_name }}
            </option>
          </select>
          <div v-if="form.errors.category_id" class="form-error">
            {{ form.errors.category_id }}
          </div>
        </div>

        <!-- Giá dịch vụ đơn lẻ -->
        <div>
          <label class="form-label required">Giá dịch vụ đơn lẻ</label>
          <input type="number" v-model="form.single_price" class="form-input" required min="0" />
          <div v-if="form.errors.single_price" class="form-error">
            {{ form.errors.single_price }}
          </div>
        </div>

        <!-- Thời gian (phút) -->
        <div>
          <label class="form-label required">Thời gian (phút)</label>
          <input type="number" v-model="form.duration" class="form-input" required min="0" />
          <div v-if="form.errors.duration" class="form-error">
            {{ form.errors.duration }}
          </div>
        </div>

        <!-- Giá combo 5 lần -->
        <div>
          <label class="form-label">Giá combo 5 lần</label>
          <input type="number" v-model="form.combo_5_price" class="form-input" min="0" />
          <div v-if="form.errors.combo_5_price" class="form-error">
            {{ form.errors.combo_5_price }}
          </div>
        </div>

        <!-- Giá combo 10 lần -->
        <div>
          <label class="form-label">Giá combo 10 lần</label>
          <input type="number" v-model="form.combo_10_price" class="form-input" min="0" />
          <div v-if="form.errors.combo_10_price" class="form-error">
            {{ form.errors.combo_10_price }}
          </div>
        </div>

        <!-- Thời hạn sử dụng combo (ngày) -->
        <div>
          <label class="form-label">Thời hạn sử dụng combo (ngày)</label>
          <input type="number" v-model="form.validity_period" class="form-input" min="0" />
          <div v-if="form.errors.validity_period" class="form-error">
            {{ form.errors.validity_period }}
          </div>
        </div>
      </div>

      <!-- Product Images -->
      <div v-if="!isEditing" class="py-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Hình ảnh dịch vụ</label>
        <div class="flex flex-wrap gap-4 mb-4">
          <div v-for="(preview, index) in previews" :key="index"
            class="relative w-24 h-24 border rounded-lg overflow-hidden">
            <img :src="preview" class="w-full h-full object-cover">
            <button @click.prevent="removeImage(index)"
              class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
              <BaseIcon :path="mdiClose" size="16" />
            </button>
          </div>
          <div
            class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-primary-500"
            @click="triggerFileInput">
            <BaseIcon :path="mdiPlus" size="24" class="text-gray-400" />
            <input type="file" ref="fileInput" class="hidden" multiple @change="handleImageUpload" accept="image/*">
          </div>
        </div>
        <p class="text-xs text-gray-500">PNG, JPG, GIF tối đa 2MB</p>
        <span v-if="form.errors.images" class="text-red-500 text-sm">{{ form.errors.images }}</span>
      </div>

      <!-- Mô tả -->
      <div>
        <label class="form-label">Mô tả</label>
        <textarea v-model="form.description" class="form-textarea" rows="4"></textarea>
        <div v-if="form.errors.description" class="form-error">
          {{ form.errors.description }}
        </div>
      </div>
    </form>
  </CardBoxModal>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { useToast } from 'vue-toastification'
import { mdiClose, mdiPlus } from '@mdi/js'

const toast = useToast()
const fileInput = ref(null)
const previews = ref([])
const imageFiles = ref([])

const props = defineProps({
  modelValue: Boolean,
  service: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'service-saved'])

const isEditing = computed(() => !!props.service)

const form = useForm({
  service_name: '',
  description: '',
  duration: 0,
  category_id: '',
  single_price: 0,
  combo_5_price: 0,
  combo_10_price: 0,
  validity_period: 0,
  images: [],
})

// Thêm các methods xử lý ảnh
const triggerFileInput = () => {
  fileInput.value.click()
}

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  const existingCount = imageFiles.value.length
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
    imageFiles.value.push(file)
  })

  form.images = imageFiles.value
}

const removeImage = (index) => {
  previews.value.splice(index, 1)
  imageFiles.value.splice(index, 1)
  form.images = imageFiles.value
}

onMounted(async () => {
  if (props.service) {
    form.service_name = props.service.service_name
    form.description = props.service.description
    form.duration = props.service.duration
    form.category_id = props.service.category_id
    form.single_price = props.service.single_price
    form.combo_5_price = props.service.combo_5_price || 0
    form.combo_10_price = props.service.combo_10_price || 0
    form.validity_period = props.service.validity_period || 0

    if (props.service.media && props.service.media.length > 0) {
      previews.value = []
      props.service.media.forEach(media => {
        const url = media.file_path.startsWith('http')
          ? media.file_path
          : `/storage/${media.file_path.replace(/^\/+/, '')}`
        previews.value.push(url)
      })
    }
  }
})

const updateModal = (value) => {
  emit('update:modelValue', value)
}

const closeModal = () => {
  emit('close')
}

const handleSubmit = () => {
  const formData = new FormData()

  // Append all form fields
  Object.keys(form).forEach(key => {
    if (key !== 'images' || !isEditing.value) {
      formData.append(key, form[key])
    }
  })

  // Append images only if not editing
  if (!isEditing.value) {
    imageFiles.value.forEach((file, index) => {
      formData.append(`images[${index}]`, file)
    })
  }

  const url = isEditing.value
    ? route('services.update', props.service.id)
    : route('services.store')

  const method = isEditing.value ? 'put' : 'post'

  form[method](url, {
    data: formData,
    forceFormData: true,
    onSuccess: () => {
      toast.success(isEditing.value ? 'Cập nhật dịch vụ thành công' : 'Thêm dịch vụ mới thành công')
      emit('service-saved')
      closeModal()
    },
    onError: (errors) => {
      console.log(errors)
      toast.error('Có lỗi xảy ra, vui lòng thử lại')
    }
  })
}
</script>

<style scoped>
.form-label {
  @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1;
}

.form-label.required::after {
  content: "*";
  @apply text-red-500 ml-1;
}

.form-input,
.form-select,
.form-textarea {
  @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-dark-bg dark:border-dark-border dark:text-gray-100;
}

.form-error {
  @apply mt-1 text-sm text-red-600 dark:text-red-400;
}
</style>