<script setup>
import { ref, onMounted, watch } from 'vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiSync } from '@mdi/js'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
    search: String
})

const toast = useToast()
const history = ref([])
const loading = ref(false)
const pagination = ref({
    current_page: 1,
    total: 0,
    per_page: 10
})

const fetchHistory = async (page = 1) => {
    try {
        loading.value = true
        const response = await axios.get('/rewards/history', {
            params: {
                page,
                search: props.search
            }
        })
        history.value = response.data.data
        pagination.value = {
            current_page: response.data.current_page,
            total: response.data.total,
            per_page: response.data.per_page
        }
    } catch (error) {
        toast.error('Lỗi khi tải lịch sử đổi thưởng')
    } finally {
        loading.value = false
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

watch(() => props.search, (newValue) => {
    fetchHistory(1)
})

onMounted(() => {
    fetchHistory()
})
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Thời gian
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Khách hàng
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Phần thưởng
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Điểm đã dùng
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in history" :key="item.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ formatDate(item.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ item.user.full_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ item.reward_item.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ item.points_used }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <BaseButton v-for="page in Math.ceil(pagination.total / pagination.per_page)" :key="page"
                    :color="page === pagination.current_page ? 'info' : 'white'" @click="fetchHistory(page)">
                    {{ page }}
                </BaseButton>
            </nav>
        </div>
    </div>
</template>