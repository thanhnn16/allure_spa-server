<template>
    <div v-if="show"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
        @click.self="close" @keydown.esc="close" tabindex="0">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full m-4">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">{{ modalTitle }}</h2>

                <form @submit.prevent="validateAndSubmit">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4 relative">
                            <label for="user" class="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
                            <input type="text" v-model="userSearch" @input="searchUsers" placeholder="Tìm kiếm khách hàng"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <ul v-if="userResults.length > 0"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-sm max-h-40 overflow-y-auto">
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
                                    {{ treatmentPackage.treatment ? treatmentPackage.treatment.name : 'Unknown Treatment' }} - Còn lại: {{
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
                                    {{ treatment.name }} - {{ formatPrice(treatment.price) }} - {{ treatment.duration }} phút
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="appointment_type" class="block text-sm font-medium text-gray-700 mb-1">Loại lịch
                                hẹn</label>
                            <select v-model="form.appointment_type" id="appointment_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option v-for="type in appointmentTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
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
                        <div class="w-full md:w-1/2 pr-2">
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt
                                đầu</label>
                            <input type="datetime-local" v-model="form.start_date" id="start_date"
                                :disabled="form.is_all_day"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="w-full md:w-1/2 pl-2">
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                            <input type="datetime-local" v-model="form.end_date" id="end_date"
                                :disabled="form.is_all_day"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.note" id="note" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div v-if="errors" class="text-red-500 mt-2">
                        <p v-for="(error, field) in errors" :key="field">{{ error[0] }}</p>
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
    appointments: {
        type: Array,
        default: () => []
    },
    closeModal: {
        type: Function,
        required: true
    },
    selectedTimeSlot: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close', 'save', 'appointmentAdded'])

const userSearch = ref('')
const userResults = ref([])
const selectedUser = ref(null)
const errors = ref({})

const form = useForm({
    user_id: '',
    staff_id: '',
    treatment_id: '',
    user_treatment_package_id: null,
    start_date: '',
    end_date: '',
    status: 'pending',
    note: '',
    appointment_type: '',
})

const modalTitle = computed(() => {
    return props.appointments && props.appointments.length > 0 ? 'Chỉnh sửa lịch hẹn' : 'Thêm lịch hẹn mới'
})

const treatments = ref([])

const userTreatmentPackages = ref([])

const isUserTreatmentPackageSelected = computed(() => !!form.user_treatment_package_id)
const isTreatmentSelected = computed(() => !!form.treatment_id)

const errorMessage = ref('')

const staffList = ref([])

const appointmentTypes = [
    { value: 'facial', label: 'Chăm sóc da mặt' },
    { value: 'massage', label: 'Massage' },
    { value: 'weight_loss', label: 'Giảm cân' },
    { value: 'hair_removal', label: 'Triệt lông' },
    { value: 'consultation', label: 'Tư vấn' },
    { value: 'others', label: 'Khác' },
];

const appointments = ref(props.appointments)

// Log appointments for debugging
console.log('Appointments:', appointments.value)

watch(() => props.appointments, (newAppointments) => {
    if (newAppointments && newAppointments.length > 0) {
        // Reset the form
        form.reset()

        // Update form values individually
        Object.keys(newAppointments[0]).forEach(key => {
            if (form[key] !== undefined) {
                form[key] = newAppointments[0][key]
            }
        })

        // Set date fields separately
        form.start_date = formatDateTimeForInput(newAppointments[0].start)
        form.end_date = formatDateTimeForInput(newAppointments[0].end)
    } else {
        resetForm()
    }
}, { immediate: true })

// Add a new watch for selectedTimeSlot
watch(() => props.selectedTimeSlot, (newTimeSlot) => {
    if (newTimeSlot && newTimeSlot.start && newTimeSlot.end) {
        form.start_date = formatDateTimeForInput(newTimeSlot.start)
        form.end_date = formatDateTimeForInput(newTimeSlot.end)
    }
}, { immediate: true })

function formatDateTimeForInput(dateTime) {
    if (!dateTime) return ''
    let date = new Date(dateTime)
    
    // Format to YYYY-MM-DDTHH:mm
    return date.getFullYear() +
        '-' + pad(date.getMonth() + 1) +
        '-' + pad(date.getDate()) +
        'T' + pad(date.getHours()) +
        ':' + pad(date.getMinutes())
}

function formatDateForAPI(dateTimeString) {
    if (!dateTimeString) return ''
    let date = new Date(dateTimeString)
    return date.toISOString()
}

function pad(number) {
    return number < 10 ? '0' + number : number
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
                if (response.data && response.data.data) {
                    userResults.value = response.data.data;
                } else {
                    userResults.value = [];
                    console.warn('Unexpected response format:', response.data);
                }
            })
            .catch(error => {
                console.error('Error searching users:', error.response ? error.response.data : error.message);
                userResults.value = [];
            });
    } else {
        userResults.value = [];
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
            userTreatmentPackages.value = response.data.data || [];
        })
        .catch(error => {
            console.error('Error fetching user treatment packages:', error);
            userTreatmentPackages.value = []; // Set to empty array if there's an error
        });
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
    console.log('Appointments in AppointmentModal:', appointments.value)
})

function fetchTreatments() {
    axios.get('/api/treatments')
        .then(response => {
            if (response.data && response.data.data && response.data.data.data) {
                treatments.value = response.data.data.data
                console.log('Treatments data:', treatments.value)
            } else {
                console.error('Unexpected treatments data structure:', response.data)
                treatments.value = []
            }
        })
        .catch(error => {
            console.error('Error fetching treatments:', error)
            treatments.value = []
        })
}

function fetchStaffList() {
    axios.get('/api/users/get-staff-list')
        .then(response => {
            if (response.data && response.data.data) {
                staffList.value = response.data.data;
            } else {
                staffList.value = [];
                console.warn('Unexpected response format:', response.data);
            }
        })
        .catch(error => {
            console.error('Error fetching staff list:', error.response ? error.response.data : error.message);
            staffList.value = [];
        });
}

function handleKeyDown(e) {
    if (e.key === 'Escape' && props.show) {
        close()
    }
}

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown)
})

function validateForm() {
    errors.value = {}
    
    if (!form.user_id) {
        errors.value.user_id = ['Vui lòng chọn khách hàng.']
    }
    
    if (!form.staff_id) {
        errors.value.staff_id = ['Vui lòng chọn nhân viên phụ trách.']
    }
    
    if (!form.treatment_id && !form.user_treatment_package_id) {
        errors.value.treatment = ['Vui lòng chọn liệu trình hoặc gói điều trị.']
    }
    
    if (!form.start_date) {
        errors.value.start_date = ['Ngày bắt đầu là bắt buộc.']
    }
    
    if (!form.end_date) {
        errors.value.end_date = ['Ngày kết thúc là bắt buộc.']
    } else if (new Date(form.end_date) <= new Date(form.start_date)) {
        errors.value.end_date = ['Ngày kết thúc phải sau ngày bắt đầu.']
    }
    
    if (!form.appointment_type) {
        errors.value.appointment_type = ['Vui lòng chọn loại lịch hẹn.']
    }
    
    return Object.keys(errors.value).length === 0
}

function validateAndSubmit() {
    if (validateForm()) {
        const formData = {
            ...form,
            start_date: formatDateForAPI(form.start_date),
            end_date: formatDateForAPI(form.end_date),
            status: form.status.toLowerCase(),
        };
        console.log('Form data before submission:', formData);
        submitAppointment(formData);
    }
}

function submitAppointment(formData) {
    console.log('Submitting appointment data:', formData);

    axios.post(route('appointments.store'), formData)
        .then(response => {
            console.log('Appointment creation response:', response.data);
            props.closeModal();
            emit('appointmentAdded');
        })
        .catch(error => {
            console.error('Error creating appointment:', error.response ? error.response.data : error);
        });
}

</script>

<style scoped>
.relative {
    position: relative;
}

.absolute {
    position: absolute;
}

.z-10 {
    z-index: 10;
}
</style>