<template>
    <div v-if="show"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
        @click.self="close" @keydown.esc="close" tabindex="0">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full m-4">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">{{ modalTitle }}</h2>

                <form @submit.prevent="submitAppointment">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="user" class="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
                            <input type="text" v-model="userSearch" @input="searchUsers" placeholder="Tìm kiếm khách hàng"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <ul v-if="userResults.length > 0"
                                class="mt-1 bg-white border border-gray-300 rounded-md shadow-sm max-h-40 overflow-y-auto">
                                <li v-for="user in userResults" :key="user.id" @click="selectUser(user)"
                                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                                    {{ user.full_name }} ({{ user.phone_number }})
                                </li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label for="staff" class="block text-sm font-medium text-gray-700 mb-1">Nhân viên phụ trách</label>
                            <select v-model="form.staff_id" id="staff"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn nhân viên</option>
                                <option v-for="staffMember in staffList" :key="staffMember.id" :value="staffMember.id">
                                    {{ staffMember.full_name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="user_treatment_package" class="block text-sm font-medium text-gray-700 mb-1">Gói
                                điều trị</label>
                            <select v-model="form.user_treatment_package_id" id="user_treatment_package"
                                :disabled="isTreatmentSelected"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn gói điều trị</option>
                                <option v-for="treatmentPackage in userTreatmentPackages" :key="treatmentPackage.id"
                                    :value="treatmentPackage.id">
                                    {{ treatmentPackage.treatment.treatment_name }} - Còn lại: {{
                                        treatmentPackage.remaining_sessions }} buổi
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="treatment" class="block text-sm font-medium text-gray-700 mb-1">Liệu trình</label>
                            <select v-model="form.treatment_id" id="treatment"
                                :disabled="isUserTreatmentPackageSelected"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn liệu trình</option>
                                <option v-for="treatment in treatments" :key="treatment.id" :value="treatment.id">
                                    {{ treatment.treatment_name }} - {{ formatPrice(treatment.price) }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="appointment_type" class="block text-sm font-medium text-gray-700 mb-1">Loại lịch
                                hẹn</label>
                            <select v-model="form.appointment_type" id="appointment_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn loại lịch hẹn</option>
                                <option value="facial">Facial</option>
                                <option value="massage">Massage</option>
                                <option value="hair_removal">Hair Removal</option>
                                <option value="consultation">Tư vấn</option>
                                <option value="weight_loss">Giảm béo</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select v-model="form.status" id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="pending">Đang chờ</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between mb-4 space-y-4 md:space-y-0">
                        <div class="w-full md:w-2/5 pr-2">
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt
                                đầu</label>
                            <input type="datetime-local" v-model="form.start_date" id="start_date"
                                :disabled="form.is_all_day"
                                :min="`${form.start_date.split('T')[0]}T08:00`"
                                :max="`${form.start_date.split('T')[0]}T18:00`"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="w-full md:w-2/5 px-2">
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                            <input type="datetime-local" v-model="form.end_date" id="end_date"
                                :disabled="form.is_all_day"
                                :min="`${form.end_date.split('T')[0]}T08:00`"
                                :max="`${form.end_date.split('T')[0]}T18:00`"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="w-full md:w-1/5 pl-2 flex items-center">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.is_all_day"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Cả ngày</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.note" id="note" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div v-if="errorMessage" class="text-red-500 mt-2">
                        {{ errorMessage }}
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Lưu
                        </button>
                        <button @click="close"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    show: Boolean,
    appointment: Object,
})

const emit = defineEmits(['close', 'save', 'appointmentAdded'])

const userSearch = ref('')
const userResults = ref([])
const selectedUser = ref(null)
const form = useForm({
    user_id: '',
    appointment_type: '',
    staff_id: null,
    treatment_id: '',
    start_date: '',
    end_date: '',
    is_all_day: false,
    status: 'pending',
    note: '',
    user_treatment_package_id: null,
})

const modalTitle = computed(() => {
    return props.appointment && props.appointment.id ? 'Chỉnh sửa lịch hẹn' : 'Thêm lịch hẹn mới'
})

const treatments = ref([])

const userTreatmentPackages = ref([])

const isUserTreatmentPackageSelected = computed(() => !!form.user_treatment_package_id)
const isTreatmentSelected = computed(() => !!form.treatment_id)

const errorMessage = ref('')

const staffList = ref([])

watch(() => props.appointment, (newAppointment) => {
    if (newAppointment) {
        let startDate = new Date(newAppointment.start)
        let endDate = new Date(newAppointment.end)

        startDate = adjustTimeToBusinessHours(startDate)
        endDate = adjustTimeToBusinessHours(endDate)

        // Reset the form
        form.reset()

        // Update form values individually
        Object.keys(newAppointment).forEach(key => {
            if (form[key] !== undefined) {
                form[key] = newAppointment[key]
            }
        })

        // Set date fields separately
        form.start_date = formatDateTimeForInput(startDate)
        form.end_date = formatDateTimeForInput(endDate)
        form.is_all_day = newAppointment.allDay
    } else {
        resetForm()
    }
}, { immediate: true })

function formatDateTimeForInput(dateTimeString) {
    if (!dateTimeString) return ''
    const date = new Date(dateTimeString)
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset())
    return date.toISOString().slice(0, 16)
}

function formatDateForAPI(dateTimeString) {
    if (!dateTimeString) return ''
    const date = new Date(dateTimeString)
    return date.toISOString()
}

function adjustTimeToBusinessHours(date) {
    let hours = date.getHours()
    if (hours < 8) {
        date.setHours(8, 0, 0, 0)
    } else if (hours >= 18) {
        date.setHours(17, 59, 0, 0)
    }
    return date
}

function resetForm() {
    userSearch.value = ''
    userResults.value = []
    selectedUser.value = null
    form.reset()
}

function searchUsers() {
    if (userSearch.value.length > 2) {
        axios.get(`/api/users/search?query=${userSearch.value}`)
            .then(response => {
                userResults.value = response.data
            })
    } else {
        userResults.value = []
    }
}

function selectUser(user) {
    selectedUser.value = user
    form.user_id = user.id
    userResults.value = []
    userSearch.value = user.full_name
    fetchUserTreatmentPackages(user.id)
}

function fetchUserTreatmentPackages(userId) {
    axios.get(`/api/user-treatment-packages/${userId}`)
        .then(response => {
            userTreatmentPackages.value = response.data
        })
        .catch(error => {
            console.error('Error fetching user treatment packages:', error)
            userTreatmentPackages.value = [] // Set to empty array if there's an error
        })
}

function close() {
    emit('close')
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

onMounted(() => {
    fetchTreatments()
    fetchStaffList()
    document.addEventListener('keydown', handleKeyDown)
})

function fetchTreatments() {
    axios.get('/api/treatments')
        .then(response => {
            treatments.value = response.data
        })
        .catch(error => {
            console.error('Error fetching treatments:', error)
        })
}

function fetchStaffList() {
    axios.get('/api/users/get-staff-list')
        .then(response => {
            staffList.value = response.data.staff
        })
        .catch(error => {
            console.error('Error fetching staff list:', error)
        })
}

function handleKeyDown(e) {
    if (e.key === 'Escape' && props.show) {
        close()
    }
}

watch(() => form.is_all_day, (newValue) => {
    if (newValue) {
        const startDate = new Date(form.start_date)
        const endDate = new Date(form.end_date)
        startDate.setHours(0, 0, 0, 0)
        endDate.setHours(23, 59, 59, 999)
        form.start_date = formatDateTimeForInput(startDate)
        form.end_date = formatDateTimeForInput(endDate)
    }
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown)
})

function submitAppointment() {
    errorMessage.value = '' // Reset error message
    form.post(route('appointments.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash.success) {
                alert(page.props.flash.success)
                emit('close')
                emit('appointmentAdded')
            } else if (page.props.errors) {
                errorMessage.value = Object.values(page.props.errors).join(', ')
            }
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors).join(', ')
        }
    })
}
</script>