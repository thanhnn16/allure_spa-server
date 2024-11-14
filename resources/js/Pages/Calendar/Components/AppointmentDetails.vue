<script setup>
import { ref, computed, onMounted } from 'vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import { Head, router } from '@inertiajs/vue3'
import { mdiCalendar, mdiAccount, mdiClockOutline, mdiNoteText, mdiAlert, mdiDelete, mdiPencil, mdiArrowLeft } from '@mdi/js'
import axios from 'axios'

const props = defineProps({
    appointment: {
        type: Object,
        required: true
    }
})

const notification = ref(null)

const statusColors = {
    'pending': 'warning',
    'confirmed': 'success',
    'cancelled': 'danger',
    'completed': 'info'
}

const statusLabels = {
    'pending': 'Chờ xác nhận',
    'confirmed': 'Đã xác nhận',
    'cancelled': 'Đã hủy',
    'completed': 'Hoàn thành'
}

const appointmentTypeLabels = {
    'consultation': 'Tư vấn',
    'treatment': 'Điều trị',
    'follow_up': 'Tái khám',
    'others': 'Khác'
}

const formatDateTime = (date, time) => {
    const dateObj = new Date(date)
    const day = dateObj.getDate().toString().padStart(2, '0')
    const month = (dateObj.getMonth() + 1).toString().padStart(2, '0')
    const year = dateObj.getFullYear()
    return `${day}/${month}/${year} ${time}`
}

const handleStatusChange = async (newStatus) => {
    try {
        const response = await axios.put(`/api/appointments/${props.appointment.id}`, {
            status: newStatus
        })
        
        if (response.data.success) {
            notification.value = {
                type: 'success',
                message: 'Cập nhật trạng thái thành công'
            }
            // Reload the page after a short delay
            setTimeout(() => {
                router.reload()
            }, 1000)
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật trạng thái'
        }
    }
}

const handleDelete = async () => {
    if (!confirm('Bạn có chắc chắn muốn xóa lịch hẹn này?')) return

    try {
        const response = await axios.delete(`/api/appointments/${props.appointment.id}`)
        if (response.data.success) {
            router.visit('/appointments')
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi xóa lịch hẹn'
        }
    }
}

const handleGoBack = () => {
    router.visit('/appointments')
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date)
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Chi tiết lịch hẹn" />
        
        <SectionMain>
            <!-- Header Section -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="p-2 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Chi tiết lịch hẹn #{{ appointment.id }}</h1>
                            <p class="text-gray-500">Được tạo ngày {{ formatDate(appointment.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <BaseButton
                            :icon="mdiArrowLeft"
                            label="Quay lại"
                            color="white"
                            class="border border-gray-300 hover:bg-gray-50"
                            @click="handleGoBack"
                        />
                        <BaseButton
                            :icon="mdiPencil"
                            label="Chỉnh sửa"
                            color="info"
                            @click="router.visit(`/appointments/${appointment.id}/edit`)"
                        />
                        <BaseButton
                            :icon="mdiDelete"
                            label="Xóa"
                            color="danger"
                            @click="handleDelete"
                        />
                    </div>
                </div>
            </div>

            <!-- Notification Bar -->
            <NotificationBar
                v-if="notification"
                :color="notification.type"
                :icon="mdiAlert"
                class="mb-6"
            >
                {{ notification.message }}
            </NotificationBar>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Appointment Status Card -->
                <CardBox class="relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20">
                        <div :class="`absolute transform rotate-45 bg-${statusColors[appointment.status]} text-white text-xs font-bold py-1 right-[-35px] top-[32px] w-[170px] text-center`">
                            {{ statusLabels[appointment.status] }}
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <span class="mr-2">Thông tin chung</span>
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-gray-500">Loại lịch hẹn</span>
                                <p class="font-medium">{{ appointmentTypeLabels[appointment.appointment_type] }}</p>
                            </div>
                            
                            <div>
                                <span class="text-gray-500">Thời gian</span>
                                <p class="font-medium">
                                    {{ formatDateTime(appointment.appointment_date, appointment.time_slot.start_time) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="appointment.note" class="mt-4">
                            <span class="text-gray-500">Ghi chú</span>
                            <p class="mt-1 text-gray-700 bg-gray-50 p-3 rounded-lg">{{ appointment.note }}</p>
                        </div>
                    </div>
                </CardBox>

                <!-- Customer Info Card -->
                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <span class="mr-2">Thông tin khách hàng</span>
                            <span class="p-1 bg-blue-100 rounded-full">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-gray-500">Họ tên</span>
                                <p class="font-medium">{{ appointment.user.full_name }}</p>
                            </div>
                            
                            <div>
                                <span class="text-gray-500">Số điện thoại</span>
                                <p class="font-medium">{{ appointment.user.phone_number }}</p>
                            </div>
                            
                            <div class="col-span-2">
                                <span class="text-gray-500">Email</span>
                                <p class="font-medium">{{ appointment.user.email }}</p>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <!-- Service Info Card -->
                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <span class="mr-2">Thông tin dịch vụ</span>
                            <span class="p-1 bg-purple-100 rounded-full">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </span>
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <span class="text-gray-500">Dịch vụ</span>
                                <p class="font-medium">{{ appointment.service.service_name }}</p>
                            </div>
                            
                            <div>
                                <span class="text-gray-500">Thời gian thực hiện</span>
                                <p class="font-medium">{{ appointment.service.duration }} phút</p>
                            </div>
                            
                            <div>
                                <span class="text-gray-500">Nhân viên phụ trách</span>
                                <p class="font-medium">{{ appointment.staff.full_name }}</p>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <!-- Status Update Card -->
                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4">Cập nhật trạng thái</h2>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <BaseButton
                                v-for="(label, status) in statusLabels"
                                :key="status"
                                :label="label"
                                :color="statusColors[status]"
                                :disabled="appointment.status === status"
                                class="w-full justify-center"
                                @click="handleStatusChange(status)"
                            />
                        </div>
                    </div>
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
.grid {
    grid-gap: 1.5rem;
}

.card-hover {
    transition: all 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>