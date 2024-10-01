<template>
    <div v-if="show"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50">
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full m-4">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">{{ modalTitle }}</h2>

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
                    <label for="appointment_type" class="block text-sm font-medium text-gray-700 mb-1">Loại lịch
                        hẹn</label>
                    <select v-model="appointmentData.appointment_type_id" id="appointment_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Chọn loại lịch hẹn</option>
                        <!-- Thêm các option cho loại lịch hẹn ở đây -->
                    </select>
                </div>

                <div class="mb-4">
                    <label for="staff" class="block text-sm font-medium text-gray-700 mb-1">Nhân viên phụ trách</label>
                    <select v-model="appointmentData.staff_id" id="staff"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Chọn nhân viên</option>
                        <!-- Thêm các option cho nhân viên ở đây -->
                    </select>
                </div>

                <div class="mb-4">
                    <label for="order_item" class="block text-sm font-medium text-gray-700 mb-1">Mục đơn hàng</label>
                    <select v-model="appointmentData.order_item_id" id="order_item"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Chọn mục đơn hàng</option>
                        <!-- Thêm các option cho mục đơn hàng ở đây -->
                    </select>
                </div>

                <div class="mb-4">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                    <input type="datetime-local" v-model="appointmentData.start_date" id="start_date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="mb-4">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                    <input type="datetime-local" v-model="appointmentData.end_date" id="end_date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" v-model="appointmentData.is_all_day"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Cả ngày</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select v-model="appointmentData.status" id="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="pending">Đang chờ</option>
                        <option value="confirmed">Đã xác nhận</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea v-model="appointmentData.note" id="note" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="save"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Lưu
                    </button>
                    <button @click="$emit('close')"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    show: Boolean,
    appointment: Object,
})

const emit = defineEmits(['close', 'save'])

const userSearch = ref('')
const userResults = ref([])
const selectedUser = ref(null)
const appointmentData = ref({
    user_id: '',
    appointment_type_id: '',
    staff_id: null,
    order_item_id: '',
    start_date: '',
    end_date: '',
    is_all_day: false,
    status: 'pending',
    note: '',
})

const modalTitle = computed(() => {
    return props.appointment && props.appointment.id ? 'Chỉnh sửa lịch hẹn' : 'Thêm lịch hẹn mới'
})

watch(() => props.appointment, (newAppointment) => {
    if (newAppointment) {
        appointmentData.value = { ...newAppointment }
    } else {
        resetForm()
    }
}, { immediate: true })

function resetForm() {
    userSearch.value = ''
    userResults.value = []
    selectedUser.value = null
    appointmentData.value = {
        user_id: '',
        appointment_type_id: '',
        staff_id: null,
        order_item_id: '',
        start_date: '',
        end_date: '',
        is_all_day: false,
        status: 'pending',
        note: '',
    }
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
    appointmentData.value.user_id = user.id
    userResults.value = []
    userSearch.value = user.full_name
}

function save() {
    emit('save', appointmentData.value)
}
</script>