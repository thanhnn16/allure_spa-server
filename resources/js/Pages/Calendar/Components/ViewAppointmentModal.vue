<template>
    <div v-if="show"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
        @click="close">
        <div v-if="isLoading" class="text-white">
            <div class="text-center">Đang tải...</div>
        </div>
        <div v-else class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full m-4" @click.stop>
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Chi tiết lịch hẹn</h2>
                <form @submit.prevent="handleSubmit">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Khách hàng -->
                        <div class="mb-4 relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
                            <input type="text" :value="formattedUserName" disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                        </div>

                        <!-- Nhân viên phụ trách -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nhân viên phụ trách</label>
                            <select v-model="form.staff_user_id" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }">
                                <option v-for="staff in staffList" :key="staff.id" :value="staff.id">
                                    {{ staff.full_name }}
                                </option>
                            </select>
                        </div>

                        <!-- Dịch vụ -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dịch vụ</label>
                            <input type="text" :value="formattedServiceName" disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                        </div>

                        <!-- Ngày hẹn -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày hẹn</label>
                            <input type="date" v-model="form.appointment_date" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }">
                        </div>

                        <!-- Trạng thái -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select v-model="form.status" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }">
                                <option value="pending">Đang chờ</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="completed">Đã hoàn thành</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>

                        <!-- Ghi chú -->
                        <div class="mb-4 col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea v-model="form.note" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }"
                                rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <template v-if="!isEditing">
                            <button type="button" @click="startEditing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Chỉnh sửa
                            </button>
                            <button type="button" @click="handleCancel"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Hủy lịch hẹn
                            </button>
                        </template>
                        <template v-else>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                Lưu thay đổi
                            </button>
                            <button type="button" @click="cancelEditing"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Hủy
                            </button>
                        </template>
                        <button type="button" @click="close"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    appointmentId: {
        type: Number,
        required: true,
        validator: (value) => value > 0
    },
    fetchAppointmentDetails: Function
});

const emit = defineEmits(['close', 'update']);

const isLoading = ref(false);
const isEditing = ref(false);
const form = ref({
    user_id: '',
    staff_user_id: '',
    service_id: '',
    appointment_date: '',
    time_slot_id: '',
    appointment_type: '',
    status: '',
    note: '',
    user: null,
    service: null,
    staff: null
});
const staffList = ref([]);
const services = ref([]);
const timeSlots = ref([]);
const userTreatmentPackages = ref([]);

const isTreatmentSelected = computed(() => !!form.value.service_id);
const isUserTreatmentPackageSelected = computed(() => !!form.value.user_treatment_package_id);

async function loadAppointmentDetails() {
    console.log('Loading appointment details...');
    if (!props.appointmentId || props.appointmentId <= 0) {
        console.log('Invalid appointment ID:', props.appointmentId);
        return;
    }

    isLoading.value = true;
    try {
        console.log('Fetching appointment details for ID:', props.appointmentId);
        const appointmentData = await props.fetchAppointmentDetails(props.appointmentId);
        console.log('Received appointment data:', appointmentData);
        if (appointmentData) {
            form.value = {
                user_id: appointmentData.user_id,
                staff_user_id: appointmentData.staff_user_id,
                service_id: appointmentData.service_id,
                appointment_date: formatDate(appointmentData.appointment_date),
                time_slot_id: appointmentData.time_slot_id,
                appointment_type: appointmentData.appointment_type,
                status: appointmentData.status,
                note: appointmentData.note,
                user: appointmentData.user,
                service: appointmentData.service,
                staff: appointmentData.staff
            };
            console.log('Updated form data:', form.value);
        }
    } catch (error) {
        console.error('Error loading appointment details:', error);
    } finally {
        isLoading.value = false;
    }
}

function formatDate(dateString) {
    if (!dateString) return '';
    return dateString.split('T')[0];
}

const formattedUserName = computed(() => form.value.user?.full_name || 'N/A');
const formattedServiceName = computed(() => form.value.service?.service_name || 'N/A');
const formattedStaffName = computed(() => form.value.staff?.full_name || 'N/A');

function startEditing() {
    isEditing.value = true;
}

function cancelEditing() {
    isEditing.value = false;
    loadAppointmentDetails();
}

async function handleSubmit() {
    try {
        const response = await axios.put(`/api/appointments/${props.appointmentId}/update`, form.value);
        if (response.data.status === 200) {
            emit('update', response.data.data);
            isEditing.value = false;
        }
    } catch (error) {
        console.error('Lỗi khi cập nhật cuộc hẹn:', error);
    }
}

async function handleCancel() {
    if (!confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?')) return;

    try {
        const response = await axios.put(`/api/appointments/${props.appointmentId}/cancel`, {
            note: 'Hủy bởi người dùng'
        });
        if (response.data.status === 200) {
            emit('update', response.data.data);
            close();
        }
    } catch (error) {
        console.error('Lỗi khi hủy lịch hẹn:', error);
    }
}

function close() {
    console.log('Closing modal...');
    isEditing.value = false;
    emit('close');
}

onMounted(() => {
    console.log('ViewAppointmentModal mounted');
    console.log('Appointment ID:', props.appointmentId);
    loadAppointmentDetails();
});

watch(() => props.appointmentId, (newVal, oldVal) => {
    console.log('appointmentId changed:', { newVal, oldVal });
    if (newVal && newVal > 0 && props.show) {
        loadAppointmentDetails();
    }
}, { immediate: true });
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