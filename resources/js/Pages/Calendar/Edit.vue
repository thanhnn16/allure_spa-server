<script setup>
import { ref, computed, onMounted } from 'vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import { Head, router } from '@inertiajs/vue3'
import { mdiAlert } from '@mdi/js'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const props = defineProps({
    appointment: {
        type: Object,
        required: true
    },
    timeSlots: {
        type: Array,
        default: () => []
    }
})

const notification = ref(null)
const isSubmitting = ref(false)
const staffList = ref([])
const services = ref([])
const userTreatmentPackages = ref([])

const formData = ref({
    appointment_date: props.appointment.appointment_date,
    time_slot_id: props.appointment.time_slot_id,
    note: props.appointment.note,
    slots: props.appointment.slots,
    staff_id: props.appointment.staff_user_id,
    service_id: props.appointment.service_id,
    user_service_package_id: props.appointment.user_service_package_id,
    appointment_type: props.appointment.appointment_type,
    status: props.appointment.status
})

// Computed properties
const availableTimeSlots = computed(() => {
    return props.timeSlots.map(slot => ({
        ...slot,
        displayText: `${slot.start_time.substring(0, 5)} - ${slot.end_time.substring(0, 5)} (${slot.current_bookings}/${slot.max_bookings})`
    }))
})

const statusOptions = computed(() => [
    { value: 'pending', label: 'Chờ xác nhận', color: 'bg-yellow-100 text-yellow-800' },
    { value: 'confirmed', label: 'Đã xác nhận', color: 'bg-blue-100 text-blue-800' },
    { value: 'completed', label: 'Hoàn thành', color: 'bg-green-100 text-green-800' },
    { value: 'cancelled', label: 'Đã hủy', color: 'bg-red-100 text-red-800' }
])

const appointmentTypeDisplay = computed(() => {
    const types = {
        'service': 'Dịch vụ đơn lẻ',
        'service_package': 'Gói điều trị',
        'consultation': 'Tư vấn',
        'others': 'Khác'
    }
    return types[formData.value.appointment_type] || 'Không xác định'
})

// Methods
const handleSubmit = async () => {
    try {
        isSubmitting.value = true
        const response = await axios.put(`/api/appointments/${props.appointment.id}`, formData.value)

        if (response.data.success) {
            toast.success('Cập nhật lịch hẹn thành công')
            setTimeout(() => {
                router.visit(`/appointments/${props.appointment.id}`)
            }, 1000)
        }
    } catch (error) {
        toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật lịch hẹn')
    } finally {
        isSubmitting.value = false
    }
}

// Fetch data
const fetchStaffList = async () => {
    try {
        const response = await axios.get('/api/users/get-staff-list')
        staffList.value = response.data.data || []
    } catch (error) {
        console.error('Error fetching staff list:', error)
    }
}

const fetchServices = async () => {
    try {
        const response = await axios.get('/api/services/appointment')
        services.value = response.data.data || []
    } catch (error) {
        console.error('Error fetching services:', error)
    }
}

const fetchUserTreatmentPackages = async () => {
    try {
        const response = await axios.get(`/api/user-treatment-packages/${props.appointment.user_id}`)
        userTreatmentPackages.value = response.data.data || []
    } catch (error) {
        console.error('Error fetching user treatment packages:', error)
    }
}

onMounted(() => {
    fetchStaffList()
    fetchServices()
    fetchUserTreatmentPackages()
})
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Chỉnh sửa lịch hẹn" />

        <SectionMain :breadcrumbs="[
            { label: 'Lịch hẹn', href: route('appointments.index') },
            { label: 'Chi tiết', href: `/appointments/${appointment.id}` },
            { label: 'Chỉnh sửa' }
        ]">
            <!-- Header Card -->
            <CardBox class="mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Chỉnh sửa lịch hẹn #{{ appointment.id }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Cập nhật thông tin lịch hẹn của khách hàng
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ appointment.user?.full_name }}
                            </span>
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span :class="[
                            'px-3 py-1 rounded-full text-sm font-medium',
                            statusOptions.find(s => s.value === appointment.status)?.color
                        ]">
                            {{ statusOptions.find(s => s.value === appointment.status)?.label }}
                        </span>
                    </div>
                </div>
            </CardBox>

            <!-- Main Form -->
            <CardBox>
                <form @submit.prevent="handleSubmit" class="space-y-8">
                    <!-- Basic Info Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Appointment Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Loại lịch hẹn
                            </label>
                            <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                {{ appointmentTypeDisplay }}
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Trạng thái
                            </label>
                            <select v-model="formData.status"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
                                <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Staff -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nhân viên phụ trách
                            </label>
                            <select v-model="formData.staff_id"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
                                <option v-for="staff in staffList" :key="staff.id" :value="staff.id">
                                    {{ staff.full_name }}
                                </option>
                            </select>
                        </div>

                        <!-- Slots -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Số lượng slot
                            </label>
                            <input type="number" v-model="formData.slots" min="1" max="2"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400" />
                        </div>
                    </div>

                    <!-- Time Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Ngày hẹn
                            </label>
                            <input type="date" v-model="formData.appointment_date"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400" />
                        </div>

                        <!-- Time Slot -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Khung giờ
                            </label>
                            <select v-model="formData.time_slot_id"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
                                <option v-for="slot in availableTimeSlots" :key="slot.id" :value="slot.id">
                                    {{ slot.displayText }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Service Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Service -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dịch vụ
                                <span v-if="formData.user_service_package_id" class="text-sm text-gray-500">
                                    (Đã chọn gói)
                                </span>
                            </label>
                            <select v-model="formData.service_id" :disabled="formData.user_service_package_id"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
                                <option value="">Chọn dịch vụ</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">
                                    {{ service.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Treatment Package -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Gói điều trị
                                <span v-if="formData.service_id" class="text-sm text-gray-500">
                                    (Đã chọn dịch vụ)
                                </span>
                            </label>
                            <select v-model="formData.user_service_package_id" :disabled="formData.service_id"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
                                <option value="">Chọn gói điều trị</option>
                                <option v-for="pkg in userTreatmentPackages" :key="pkg.id" :value="pkg.id">
                                    {{ pkg.service?.name }} ({{ pkg.remaining_sessions }} buổi)
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Note Section -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Ghi chú
                        </label>
                        <textarea v-model="formData.note" rows="4"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                            placeholder="Thêm ghi chú cho lịch hẹn..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4">
                        <button type="button"
                            class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                            @click="router.visit(`/appointments/${appointment.id}`)">
                            Hủy
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="isSubmitting">
                            {{ isSubmitting ? 'Đang cập nhật...' : 'Cập nhật' }}
                        </button>
                    </div>
                </form>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>