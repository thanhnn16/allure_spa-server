<template>
  <CardBoxModal :modelValue="modelValue" @update:modelValue="$emit('update:modelValue')" title="Chỉnh sửa sản phẩm"
    @submit="handleSubmit" @cancel="$emit('close')" :isLoading="isSubmitting" :hasButton="false">
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Basic Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Tên sản phẩm *</label>
          <input v-model="form.name" :class="[
            'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
            validationErrors.name ? 'border-red-500' : 'border-gray-300'
          ]" @blur="validateField('name', form.name)" type="text" required>
          <span v-if="validationErrors.name" class="text-red-500 text-sm">{{ validationErrors.name }}</span>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Danh mục *</label>
          <select v-model="form.category_id" :class="[
            'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
            validationErrors.category_id ? 'border-red-500' : 'border-gray-300'
          ]" required>
            <option value="">Chọn danh mục</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.category_name }}
            </option>
          </select>
          <span v-if="validationErrors.category_id" class="text-red-500 text-sm">{{ validationErrors.category_id
            }}</span>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Giá *</label>
          <input 
            v-model="displayPrice"
            @input="handlePriceInput"
            @blur="formatDisplayPrice"
            type="text"
            :class="[
              'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
              validationErrors.price ? 'border-red-500' : 'border-gray-300'
            ]"
            required
          >
          <span v-if="validationErrors.price" class="text-red-500 text-sm">{{ validationErrors.price }}</span>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Số lượng *</label>
          <input v-model="form.quantity" type="number" min="0" :class="[
            'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
            validationErrors.quantity ? 'border-red-500' : 'border-gray-300'
          ]" required>
          <span v-if="validationErrors.quantity" class="text-red-500 text-sm">{{ validationErrors.quantity }}</span>
        </div>
      </div>

      <!-- Product Details -->
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Mô tả thương hiệu</label>
          <textarea v-model="form.brand_description" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Cách sử dụng</label>
          <textarea v-model="form.usage" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>

        <!-- ... Các trường khác tương tự ... -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Lợi ích</label>
          <textarea v-model="form.benefits" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Thành phần chính</label>
          <textarea v-model="form.key_ingredients" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Thành phần đầy đủ</label>
          <textarea v-model="form.ingredients" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Hướng dẫn sử dụng</label>
          <textarea v-model="form.directions" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Hướng dẫn bảo quản</label>
          <textarea v-model="form.storage_instructions" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Ghi chú sản phẩm</label>
          <textarea v-model="form.product_notes" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>
      </div>

      <!-- Validation Errors Summary -->
      <div v-if="Object.keys(validationErrors).length > 0" class="bg-red-50 p-4 rounded-md">
        <p class="text-red-700 font-medium">Vui lòng sửa các lỗi sau:</p>
        <ul class="mt-2 text-sm text-red-600">
          <li v-for="(error, field) in validationErrors" :key="field">
            {{ error }}
          </li>
        </ul>
      </div>

      <div class="flex justify-end space-x-2">
        <BaseButton type="button" label="Hủy" @click="$emit('close')" />
        <BaseButton type="submit" color="info" label="Cập nhật" :loading="isSubmitting" :disabled="isSubmitting" />
      </div>
    </form>
  </CardBoxModal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  product: {
    type: Object,
    required: true
  },
  categories: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'product-updated'])
const toast = useToast()
const validationErrors = ref({})
const isSubmitting = ref(false)

// Format number to Vietnamese currency format
const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price)
}

// Parse Vietnamese formatted number back to raw number
const parsePrice = (formattedPrice) => {
  return Number(formattedPrice.replace(/[^\d]/g, ''))
}

const displayPrice = ref('')
const form = useForm({
  name: props.product.name,
  category_id: props.product.category_id,
  price: props.product?.price || 0,
  quantity: props.product.quantity,
  brand_description: props.product.brand_description || '',
  usage: props.product.usage || '',
  benefits: props.product.benefits || '',
  key_ingredients: props.product.key_ingredients || '',
  ingredients: props.product.ingredients || '',
  directions: props.product.directions || '',
  storage_instructions: props.product.storage_instructions || '',
  product_notes: props.product.product_notes || '',
})

const handlePriceInput = (event) => {
  // Remove non-digit characters and format
  const rawValue = event.target.value.replace(/[^\d]/g, '')
  if (rawValue) {
    displayPrice.value = formatPrice(rawValue)
    form.price = parsePrice(displayPrice.value)
  } else {
    displayPrice.value = ''
    form.price = 0
  }
}

const formatDisplayPrice = () => {
  if (form.price) {
    displayPrice.value = formatPrice(form.price)
  }
}

const validateForm = () => {
  validationErrors.value = {}
  let isValid = true

  if (!form.name?.trim()) {
    validationErrors.value.name = 'Tên sản phẩm là bắt buộc'
    isValid = false
  }

  if (!form.category_id) {
    validationErrors.value.category_id = 'Vui lòng chọn danh mục'
    isValid = false
  }

  if (!form.price || isNaN(Number(form.price)) || Number(form.price) <= 0) {
    validationErrors.value.price = 'Giá sản phẩm phải lớn hơn 0'
    isValid = false
  }

  if (!form.quantity || isNaN(Number(form.quantity)) || Number(form.quantity) < 0) {
    validationErrors.value.quantity = 'Số lượng không được âm'
    isValid = false
  }

  return isValid
}

const validateField = (fieldName, value) => {
  const validation = {
    name: (val) => {
      if (!val) return 'Tên sản phẩm là bắt buộc'
      if (val.length < 3) return 'Tên sản phẩm phải có ít nhất 3 ký tự'
      return null
    },
    category_id: (val) => !val ? 'Danh mục là bắt buộc' : null,
    price: (val) => {
      if (!val) return 'Giá là bắt buộc'
      if (isNaN(val)) return 'Giá phải là số'
      if (parseFloat(val) <= 0) return 'Giá phải lớn hơn 0'
      return null
    },
    quantity: (val) => {
      if (!val) return 'Số lượng là bắt buộc'
      if (isNaN(val)) return 'Số lượng phải là số'
      if (parseInt(val) < 0) return 'Số lượng không được âm'
      return null
    }
  }

  if (validation[fieldName]) {
    const error = validation[fieldName](value)
    if (error) {
      validationErrors.value[fieldName] = error
      return false
    } else {
      delete validationErrors.value[fieldName]
      return true
    }
  }
  return true
}

const handleSubmit = async () => {
  if (isSubmitting.value) return
  if (!validateForm()) return

  isSubmitting.value = true

  try {
    form.put(route('products.update', props.product.id), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Cập nhật sản phẩm thành công')
        emit('product-updated')
        router.reload()
        emit('close')
      },
      onError: (errors) => {
        validationErrors.value = errors
        toast.error('Có lỗi xảy ra khi cập nhật sản phẩm')
      }
    })
  } catch (error) {
    console.error('Error updating product:', error)
    toast.error('Có lỗi xảy ra khi cập nhật sản phẩm')
  } finally {
    isSubmitting.value = false
  }
}

// Add watchers for real-time validation
watch(() => form.name, (value) => validateField('name', value))
watch(() => form.category_id, (value) => validateField('category_id', value))
watch(() => form.price, (value) => validateField('price', value))
watch(() => form.quantity, (value) => validateField('quantity', value))

onMounted(() => {
  // Initialize form with product data
  Object.keys(form).forEach(key => {
    if (props.product[key] !== undefined) {
      form[key] = props.product[key]
    }
  })

  // Initialize display price
  if (props.product?.price) {
    displayPrice.value = formatPrice(props.product.price)
  }
})
</script>

<style scoped>
.error-input {
  @apply border-red-500 focus:border-red-500 focus:ring-red-500;
}

.error-message {
  @apply text-red-500 text-sm mt-1;
}
</style>
