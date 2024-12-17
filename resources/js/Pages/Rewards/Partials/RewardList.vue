<script setup>
import { ref, onMounted, watch } from 'vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiPlus, mdiPencil, mdiDelete } from '@mdi/js'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
    search: String
})

const toast = useToast()
const rewards = ref([])
const loading = ref(false)

const fetchRewards = async () => {
    try {
        loading.value = true
        const response = await axios.get('/rewards/list')
        rewards.value = response.data
    } catch (error) {
        console.error(error.response.data)
        toast.error('Lỗi khi tải danh sách rewards')
    } finally {
        loading.value = false
    }
}

const formatPoints = (points) => {
    return new Intl.NumberFormat('vi-VN').format(points)
}

watch(() => props.search, (newValue) => {
    // Implement search logic here
})

onMounted(() => {
    fetchRewards()
})
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tên
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Loại
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Điểm yêu cầu
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Số lượng còn lại
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Lượt đổi
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Trạng thái
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="reward in rewards" :key="reward.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ reward.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ reward.item_type === 'product' ? 'Sản phẩm' : 'Dịch vụ' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ formatPoints(reward.points_required) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ reward.quantity_available ?? 'Không giới hạn' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ reward.redemptions_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="[
                            'px-2 py-1 text-xs font-medium rounded-full',
                            reward.is_active
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800'
                        ]">
                            {{ reward.is_active ? 'Đang hoạt động' : 'Không hoạt động' }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>