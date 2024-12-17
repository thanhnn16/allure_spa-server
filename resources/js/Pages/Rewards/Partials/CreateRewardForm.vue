<script setup>
import { ref } from 'vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormControl from '@/Components/FormControl.vue'
import { mdiPlus } from '@mdi/js'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  products: {
    type: Array,
    required: true
  },
  services: {
    type: Array, 
    required: true
  }
})

const toast = useToast()
const loading = ref(false)

const form = ref({
  name: '',
  description: '',
  item_type: 'product',
  item_id: '',
  points_required: '',
  quantity_available: '',
  start_date: '',
  end_date: '',
})

const submitForm = async () => {
  try {
    loading.value = true
    await axios.post('/rewards', form.value)
    toast.success('Tạo reward thành công')
    form.value = {
      name: '',
      description: '',
      item_type: 'product',
      item_id: '',
      points_required: '',
      quantity_available: '',
      start_date: '',
      end_date: '',
    }
    emit('created')
  } catch (error) {
    console.error(error.response.data)
    toast.error('Lỗi khi tạo reward')
  } finally {
    loading.value = false
  }
}

const emit = defineEmits(['created'])
</script>

<template>
  <form @submit.prevent="submitForm" class="space-y-6">
    <!-- Thông tin cơ bản -->
    <div class="bg-white dark:bg-dark-surface p-6 rounded-lg shadow-sm space-y-4">
      <h3 class="text-lg font-medium text-gray-900 dark:text-dark-text mb-4">
        Thông tin cơ bản
      </h3>
      
      <FormControl 
        v-model="form.name" 
        label="Tên reward" 
        required
        placeholder="Nhập tên reward..."
        class="dark:bg-dark-surface"
      />
      
      <FormControl 
        v-model="form.description" 
        label="Mô tả" 
        type="textarea"
        placeholder="Nhập mô tả chi tiết về reward..."
        class="dark:bg-dark-surface"
      />
    </div>

    <!-- Thông tin item -->
    <div class="bg-white dark:bg-dark-surface p-6 rounded-lg shadow-sm space-y-4">
      <h3 class="text-lg font-medium text-gray-900 dark:text-dark-text mb-4">
        Thông tin item
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-dark-text-secondary mb-2">
            Loại item
          </label>
          <select 
            v-model="form.item_type" 
            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-dark-text shadow-sm focus:border-primary-500 focus:ring-primary-500"
          >
            <option value="product">Sản phẩm</option>
            <option value="service">Dịch vụ</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-dark-text-secondary mb-2">
            Chọn {{ form.item_type === 'product' ? 'sản phẩm' : 'dịch vụ' }}
          </label>
          <select 
            v-model="form.item_id" 
            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-dark-text shadow-sm focus:border-primary-500 focus:ring-primary-500"
            required
          >
            <option value="">Chọn {{ form.item_type === 'product' ? 'sản phẩm' : 'dịch vụ' }}</option>
            <template v-if="form.item_type === 'product'">
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </template>
            <template v-else>
              <option v-for="service in services" :key="service.id" :value="service.id">
                {{ service.service_name }}
              </option>
            </template>
          </select>
        </div>
      </div>
    </div>

    <!-- Điều kiện và giới hạn -->
    <div class="bg-white dark:bg-dark-surface p-6 rounded-lg shadow-sm space-y-4">
      <h3 class="text-lg font-medium text-gray-900 dark:text-dark-text mb-4">
        Điều kiện và giới hạn
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <FormControl 
          v-model="form.points_required" 
          type="number" 
          label="Điểm yêu cầu"
          placeholder="Nhập số điểm cần để đổi reward..."
          required 
          class="dark:bg-dark-surface"
        />
        <FormControl 
          v-model="form.quantity_available" 
          type="number"
          placeholder="Nhập số lượng reward có sẵn..."
          label="Số lượng có sẵn"
          class="dark:bg-dark-surface"
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <FormControl 
          v-model="form.start_date" 
          type="date" 
          label="Ngày bắt đầu"
          class="dark:bg-dark-surface"
        />
        <FormControl 
          v-model="form.end_date" 
          type="date" 
          label="Ngày kết thúc"
          class="dark:bg-dark-surface"
        />
      </div>
    </div>

    <div class="flex justify-end pt-4">
      <BaseButton 
        type="submit" 
        :loading="loading" 
        :icon="mdiPlus" 
        color="success"
        class="w-full md:w-auto"
      >
        Tạo Reward
      </BaseButton>
    </div>
  </form>
</template> 