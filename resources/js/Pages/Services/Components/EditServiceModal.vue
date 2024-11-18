<template>
  <CardBoxModal
    :model-value="modelValue"
    @update:model-value="updateModal"
    title="Chỉnh sửa dịch vụ"
    button="primary"
    button-label="Cập nhật"
    has-cancel
    @confirm="handleSubmit"
    @cancel="closeModal"
  >
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
            <option v-for="category in categories" :key="category.id" :value="category.id">
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
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'

const props = defineProps({
  modelValue: Boolean,
  service: Object,
})

const emit = defineEmits(['update:modelValue', 'close', 'service-updated'])

const categories = ref([])

const form = useForm({
  service_name: '',
  description: '',
  duration: 0,
  category_id: '',
  single_price: 0,
  combo_5_price: 0,
  combo_10_price: 0,
  validity_period: 0,
})

onMounted(async () => {
  // Fetch categories
  try {
    const response = await fetch('/api/services/categories')
    const data = await response.json()
    categories.value = data.data
  } catch (error) {
    console.error('Error fetching categories:', error)
  }

  // Initialize form with service data
  if (props.service) {
    form.service_name = props.service.service_name
    form.description = props.service.description
    form.duration = props.service.duration
    form.category_id = props.service.category_id
    form.single_price = props.service.single_price
    form.combo_5_price = props.service.combo_5_price
    form.combo_10_price = props.service.combo_10_price
    form.validity_period = props.service.validity_period
  }
})

const updateModal = (value) => {
  emit('update:modelValue', value)
}

const closeModal = () => {
  form.reset()
  emit('close')
}

const handleSubmit = () => {
  form.put(route('services.update', props.service.id), {
    onSuccess: () => {
      emit('service-updated')
    },
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