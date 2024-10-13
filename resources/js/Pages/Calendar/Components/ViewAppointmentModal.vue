<template>
    <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full m-4">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Thông tin lịch hẹn</h2>
                <form @submit.prevent="updateAppointment">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
                            <input type="text" v-model="appointment.user.full_name" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nhân viên phụ trách</label>
                            <input type="text" v-model="appointment.staff.full_name" :disabled="!isEditing" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" :class="{ 'bg-gray-100': !isEditing }">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Liệu trình</label>
                            <input type="text" v-model="appointment.treatment.name" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loại lịch hẹn</label>
                            <input type="text" v-model="appointment.appointment_type" :disabled="!isEditing" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" :class="{ 'bg-gray-100': !isEditing }">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                            <input type="datetime-local" v-model="appointment.start_time" :disabled="!isEditing" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" :class="{ 'bg-gray-100': !isEditing }">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                            <input type="datetime-local" v-model="appointment.end_time" :disabled="!isEditing" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" :class="{ 'bg-gray-100': !isEditing }">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select v-model="appointment.status" :disabled="!isEditing" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" :class="{ 'bg-gray-100': !isEditing }">
                                <option value="pending">Đang chờ</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="cancelled">Đã hủy</option>
                                <option value="completed">Đã hoàn thành</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="appointment.note" :disabled="!isEditing" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" :class="{ 'bg-gray-100': !isEditing }"></textarea>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button v-if="!isEditing" type="button" @click="startEditing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Chỉnh sửa
                        </button>
                        <button v-if="isEditing" type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Lưu thay đổi
                        </button>
                        <button v-if="isEditing" type="button" @click="cancelEditing" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Hủy
                        </button>
                        <button type="button" @click="cancelAppointment" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Hủy lịch hẹn
                        </button>
                        <button type="button" @click="close" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    appointment: Object,
});

const emit = defineEmits(['close', 'update']);

const isEditing = ref(false);
const localAppointment = ref({});

watch(() => props.appointment, (newAppointment) => {
    if (newAppointment) {
        localAppointment.value = JSON.parse(JSON.stringify(newAppointment));
    }
}, { immediate: true, deep: true });

function startEditing() {
    isEditing.value = true;
}

function cancelEditing() {
    isEditing.value = false;
    localAppointment.value = JSON.parse(JSON.stringify(props.appointment));
}

function updateAppointment() {
    const updatedData = {
        id: localAppointment.value.id,
        staff_id: localAppointment.value.staff_id,
        start_time: localAppointment.value.start_time,
        end_time: localAppointment.value.end_time,
        status: localAppointment.value.status,
        note: localAppointment.value.note,
        appointment_type: localAppointment.value.appointment_type,
    };

    axios.put(`/api/appointments/${localAppointment.value.id}`, updatedData)
        .then((response) => {
            isEditing.value = false;
            emit('update', response.data.appointment);
        })
        .catch((error) => {
            console.error('Error updating appointment:', error);
        });
}

function cancelAppointment() {
    if (confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?')) {
        localAppointment.value.status = 'cancelled';
        updateAppointment();
    }
}

function close() {
    emit('close');
}
</script>
