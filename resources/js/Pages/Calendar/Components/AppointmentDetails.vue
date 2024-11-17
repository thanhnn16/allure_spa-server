<script setup>
import { ref, computed, onMounted } from 'vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import { Head, router } from '@inertiajs/vue3'
import { mdiAlert, mdiDelete, mdiPencil } from '@mdi/js'
import axios from 'axios'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'

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
    'service': 'Thực hiện dịch vụ',
    'service_package': 'Thực hiện combo',
    'consultation': 'Tư vấn',
    'others': 'Khác'
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


const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('vi-VN', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const isModalActive = ref(false)
const cancellationNote = ref('')
const isSubmitting = ref(false)

const handleCancelAppointment = async () => {
    if (!cancellationNote.value.trim()) {
        notification.value = {
            type: 'danger',
            message: 'Vui lòng nhập lý do hủy lịch'
        }
        return
    }

    try {
        isSubmitting.value = true
        const response = await axios.put(`/api/appointments/${props.appointment.id}/cancel`, {
            note: cancellationNote.value
        })

        if (response.data.success) {
            notification.value = {
                type: 'success',
                message: 'Hủy lịch hẹn thành công'
            }
            isModalActive.value = false
            // Reload sau 1 giây
            setTimeout(() => {
                router.reload()
            }, 1000)
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi hủy lịch hẹn'
        }
    } finally {
        isSubmitting.value = false
    }
}

// Cập nhật computed property để xử lý thông tin người hủy

// Thêm các helpers mới

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price || 0);
}

const getInitials = (name) => {
    if (!name) return '';
    return name.split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

// Cập nhật computed properties

const getStatusIcon = (status) => {
    const icons = {
        pending: 'mdiClockOutline',
        confirmed: 'mdiCheckCircleOutline',
        cancelled: 'mdiCloseCircleOutline',
        completed: 'mdiCheckCircleOutline'
    }
    return icons[status] || 'mdiInformationOutline'
}

// Thêm transition cho status updates
const isStatusUpdating = ref(false)
const handleStatusChange = async (newStatus) => {
    isStatusUpdating.value = true
    try {
        const response = await axios.put(`/api/appointments/${props.appointment.id}`, {
            status: newStatus
        })

        if (response.data.success) {
            notification.value = {
                type: 'success',
                message: 'Cập nhật trạng thái thành công'
            }
            setTimeout(() => {
                router.reload()
            }, 1000)
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật trạng thái'
        }
    } finally {
        isStatusUpdating.value = false
    }
}


onMounted(() => {
    console.log('Appointment Data:', props.appointment);
});

// Thêm computed property để format thời gian
const appointmentDateTime = computed(() => {
    const start = new Date(props.appointment.start)
    const end = new Date(props.appointment.end)
    return {
        date: formatDate(start),
        time: `${start.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })} - 
               ${end.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}`
    }
})
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Chi tiết lịch hẹn" />

        <SectionMain :breadcrumbs="[
            { label: 'Lịch hẹn', href: route('appointments.index') },
            { label: 'Chi tiết lịch hẹn' }
        ]">
            <!-- Header Section -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="p-2 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Chi tiết lịch hẹn #{{ appointment.id }}</h1>
                            <p class="text-gray-500">Được tạo ngày {{ formatDate(appointment.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <BaseButton :icon="mdiPencil" label="Chỉnh sửa" color="info"
                            @click="router.visit(`/appointments/${appointment.id}/edit`)" />
                        <BaseButton :icon="mdiDelete" label="Xóa" color="danger" @click="handleDelete" />
                    </div>
                </div>
            </div>

            <!-- Notification Bar -->
            <NotificationBar v-if="notification" :color="notification.type" :icon="mdiAlert" class="mb-6">
                {{ notification.message }}
            </NotificationBar>

            <!-- Status Badge Section -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div :class="`px-4 py-2 rounded-full text-sm font-semibold ${statusColors[appointment.status.toLowerCase()] === 'success' ? 'bg-green-100 text-green-800' :
                            statusColors[appointment.status.toLowerCase()] === 'warning' ? 'bg-yellow-100 text-yellow-800' :
                                statusColors[appointment.status.toLowerCase()] === 'danger' ? 'bg-red-100 text-red-800' :
                                    'bg-blue-100 text-blue-800'
                            }`">
                            {{ statusLabels[appointment.status.toLowerCase()] || 'Không xác định' }}
                        </div>
                        <span class="text-gray-500">
                            Cập nhật lần cuối: {{ formatDate(appointment.updated_at) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Thêm phần hiển thị thông tin hủy lịch vào sau phần trạng thái -->
            <div v-if="appointment.status.toLowerCase() === 'cancelled'"
                class="mb-6 p-4 bg-red-50 rounded-lg border border-red-100">
                <h3 class="font-semibold text-red-700 mb-3">Thông tin hủy lịch</h3>
                <div class="space-y-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600 font-medium">Người hủy:</span>
                            <p>{{ appointment.cancelled_by?.full_name || 'Không xác định' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">Thời gian hủy:</span>
                            <p>{{ formatDate(appointment.cancelled_at) }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-600 font-medium">Lý do hủy:</span>
                        <p class="mt-1 p-2 bg-white/50 rounded">
                            {{ appointment.cancellation_note || 'Không có lý do' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Appointment Info Card -->
                <CardBox class="relative overflow-hidden">
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <span class="mr-2">Thông tin chung</span>
                        </h2>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Loại lịch hẹn -->
                            <div>
                                <span class="text-gray-500">Loại lịch hẹn</span>
                                <p class="font-medium">
                                    {{ appointmentTypeLabels[appointment.appointment_type] || 'Không xác định' }}
                                </p>
                            </div>

                            <!-- Trạng thái -->
                            <div>
                                <span class="text-gray-500">Trạng thái</span>
                                <div class="mt-1">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                                        ${statusColors[appointment.status.toLowerCase()] === 'success' ? 'bg-green-100 text-green-800' :
                                            statusColors[appointment.status.toLowerCase()] === 'warning' ? 'bg-yellow-100 text-yellow-800' :
                                                statusColors[appointment.status.toLowerCase()] === 'danger' ? 'bg-red-100 text-red-800' :
                                                    'bg-blue-100 text-blue-800'}`">
                                        {{ statusLabels[appointment.status.toLowerCase()] || 'Không xác định' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Thời gian lịch hẹn -->
                            <div class="col-span-2">
                                <span class="text-gray-500">Thời gian lịch hẹn</span>
                                <div class="mt-1 space-y-1">
                                    <p class="font-medium">
                                        Ngày: {{ formatDate(appointment.appointment_date) }}
                                    </p>
                                    <p class="font-medium">
                                        Giờ: {{ appointment.time_slot?.start_time }} - {{
                                            appointment.time_slot?.end_time }}
                                    </p>
                                </div>
                            </div>

                            <!-- Số lượng slot -->
                            <div>
                                <span class="text-gray-500">Số lượng slot</span>
                                <p class="font-medium">{{ appointment.slots }}</p>
                            </div>

                            <!-- Khung giờ -->
                            <div>
                                <span class="text-gray-500">Số lượng tối đa</span>
                                <p class="font-medium">
                                    {{ appointment.time_slot?.max_bookings || 0 }} lượt/khung giờ
                                </p>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div v-if="appointment.note" class="mt-4">
                            <span class="text-gray-500">Ghi chú</span>
                            <p class="mt-1 p-3 bg-gray-50 rounded-lg text-gray-700">
                                {{ appointment.note }}
                            </p>
                        </div>
                    </div>
                </CardBox>

                <!-- Service Info Card -->
                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <span class="mr-2">Thông tin dịch vụ</span>
                            <span class="p-1 bg-purple-100 rounded-full">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </span>
                        </h2>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Tên dịch vụ -->
                            <div class="col-span-2">
                                <span class="text-gray-500">Dịch vụ</span>
                                <p class="font-medium">{{ appointment.service?.service_name }}</p>
                            </div>

                            <!-- Thời gian thực hiện -->
                            <div>
                                <span class="text-gray-500">Thời gian thực hiện</span>
                                <p class="font-medium">{{ appointment.service?.duration || 0 }} phút</p>
                            </div>

                            <!-- Giá dịch vụ -->
                            <div>
                                <span class="text-gray-500">Giá dịch vụ</span>
                                <p class="font-medium">{{ formatPrice(appointment.service?.single_price) }}</p>
                            </div>

                            <!-- Nhân viên phụ trách -->
                            <div class="col-span-2">
                                <span class="text-gray-500">Nhân viên phụ trách</span>
                                <div class="flex items-center mt-1">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                        <span class="text-sm font-medium">
                                            {{ getInitials(appointment.staff?.full_name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ appointment.staff?.full_name }}</p>
                                        <p class="text-sm text-gray-500">{{ appointment.staff?.email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Mô tả dịch vụ nếu có -->
                            <div v-if="appointment.service?.description" class="col-span-2">
                                <span class="text-gray-500">Mô tả dịch vụ</span>
                                <p class="mt-1 text-gray-700">{{ appointment.service.description }}</p>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <!-- Status Update Card -->
                <CardBox>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4">Cập nhật trạng thái</h2>

                        <div class="grid grid-cols-2 gap-3">
                            <BaseButton v-for="(label, status) in statusLabels" :key="status" :label="label"
                                :color="statusColors[status]" :disabled="appointment.status === status ||
                                    (appointment.status === 'cancelled' && status !== 'cancelled') ||
                                    (appointment.status === 'completed' && status !== 'completed')"
                                :outline="appointment.status !== status" class="w-full justify-center"
                                @click="status === 'cancelled' ? isModalActive = true : handleStatusChange(status)" />
                        </div>
                    </div>
                </CardBox>
            </div>
        </SectionMain>

        <!-- Cancel Modal -->
        <CardBoxModal v-model="isModalActive" title="Hủy lịch hẹn" button="danger" buttonLabel="Xác nhận hủy"
            :hasCancel="true" @confirm="handleCancelAppointment">
            <FormField label="Lý do hủy" help="Vui lòng nhập lý do hủy lịch">
                <FormControl v-model="cancellationNote" type="textarea" placeholder="Nhập lý do hủy lịch..."
                    :rows="4" />
            </FormField>
        </CardBoxModal>
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

/* Thêm styles cho status badges */
.status-badge {
    @apply px-3 py-1 rounded-full text-sm font-medium;
}

.status-badge-pending {
    @apply bg-yellow-100 text-yellow-800;
}

.status-badge-confirmed {
    @apply bg-green-100 text-green-800;
}

.status-badge-cancelled {
    @apply bg-red-100 text-red-800;
}

.status-badge-completed {
    @apply bg-blue-100 text-blue-800;
}

/* Thêm styles mới */
.status-badge {
    @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium transition-colors duration-200;
}

.status-badge-icon {
    @apply w-4 h-4 mr-1;
}

.appointment-card {
    @apply bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200;
}

.info-label {
    @apply text-gray-500 text-sm mb-1;
}

.info-value {
    @apply font-medium text-gray-900;
}

.grid-section {
    @apply bg-white rounded-lg p-6 shadow-sm;
}

/* Animation cho status update */
.status-updating {
    @apply opacity-50 pointer-events-none;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Thêm styles mới cho badges */
.status-badge {
    @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium transition-colors duration-200;
}

.appointment-info {
    @apply space-y-4 p-4 rounded-lg border border-gray-100;
}

.info-grid {
    @apply grid grid-cols-2 gap-4;
}

.info-label {
    @apply text-gray-500 text-sm;
}

.info-value {
    @apply font-medium text-gray-900 mt-1;
}
</style>