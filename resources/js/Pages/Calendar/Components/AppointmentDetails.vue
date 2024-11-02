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
            // Refresh the page to get updated data
            router.reload()
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
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Chi tiết lịch hẹn" />
        
        <SectionMain>
            <div class="mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Chi tiết lịch hẹn #{{ appointment.id }}</h1>
                    <div class="flex gap-2">
                        <BaseButton
                            :icon="mdiArrowLeft"
                            label="Quay lại"
                            color="white"
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

            <NotificationBar
                v-if="notification"
                :color="notification.type"
                :icon="mdiAlert"
            >
                {{ notification.message }}
            </NotificationBar>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4">Thông tin chung</h2>
                        
                        <div class="flex items-center space-x-2">
                            <span class="font-medium">Trạng thái:</span>
                            <span 
                                class="px-3 py-1 rounded-full text-sm font-medium"
                                :class="{
                                    'bg-yellow-100 text-yellow-800': appointment.status === 'pending',
                                    'bg-green-100 text-green-800': appointment.status === 'confirmed',
                                    'bg-red-100 text-red-800': appointment.status === 'cancelled',
                                    'bg-blue-100 text-blue-800': appointment.status === 'completed'
                                }"
                            >
                                {{ statusLabels[appointment.status] }}
                            </span>
                        </div>

                        <div>
                            <span class="font-medium">Loại lịch hẹn:</span>
                            <span class="ml-2">{{ appointmentTypeLabels[appointment.appointment_type] }}</span>
                        </div>

                        <div>
                            <span class="font-medium">Thời gian:</span>
                            <span class="ml-2">
                                {{ formatDateTime(appointment.appointment_date, appointment.time_slot.start_time) }}
                                -
                                {{ appointment.time_slot.end_time }}
                            </span>
                        </div>

                        <div v-if="appointment.note">
                            <span class="font-medium">Ghi chú:</span>
                            <p class="mt-1 text-gray-600">{{ appointment.note }}</p>
                        </div>
                    </div>
                </CardBox>

                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4">Thông tin khách hàng</h2>
                        
                        <div>
                            <span class="font-medium">Họ tên:</span>
                            <span class="ml-2">{{ appointment.user.full_name }}</span>
                        </div>

                        <div>
                            <span class="font-medium">Số điện thoại:</span>
                            <span class="ml-2">{{ appointment.user.phone_number }}</span>
                        </div>

                        <div>
                            <span class="font-medium">Email:</span>
                            <span class="ml-2">{{ appointment.user.email }}</span>
                        </div>
                    </div>
                </CardBox>

                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4">Thông tin dịch vụ</h2>
                        
                        <div>
                            <span class="font-medium">Dịch vụ:</span>
                            <span class="ml-2">{{ appointment.service.service_name }}</span>
                        </div>

                        <div>
                            <span class="font-medium">Thời gian thực hiện:</span>
                            <span class="ml-2">{{ appointment.service.duration }} phút</span>
                        </div>

                        <div>
                            <span class="font-medium">Nhân viên phụ trách:</span>
                            <span class="ml-2">{{ appointment.staff.full_name }}</span>
                        </div>
                    </div>
                </CardBox>

                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4">Cập nhật trạng thái</h2>
                        
                        <div class="flex flex-wrap gap-2">
                            <BaseButton
                                v-for="(label, status) in statusLabels"
                                :key="status"
                                :label="label"
                                :color="statusColors[status]"
                                :disabled="appointment.status === status"
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
/* Add any custom styles if needed */
.flex.gap-2 {
    gap: 0.5rem;
}
</style>