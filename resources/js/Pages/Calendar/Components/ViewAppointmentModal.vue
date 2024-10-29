<template>
    <div v-if="show"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
        @click.self="close">
        <div v-if="isLoading">Đang tải...</div>
        <div v-else-if="form" class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full m-4">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Chi tiết lịch hẹn</h2>
                <form @submit.prevent="updateAppointment">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4 relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
                            <input type="text" :value="form.user?.full_name || 'Không xác định'" disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nhân viên phụ trách</label>
                            <select v-model="form.staff_user_id" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }">
                                <option v-for="staffMember in staffList" :key="staffMember?.id" :value="staffMember?.id">
                                    {{ staffMember?.full_name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gói điều trị</label>
                            <select v-model="form.user_treatment_package_id" :disabled="!isEditing || isTreatmentSelected"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing || isTreatmentSelected }">
                                <option value="">Chọn gói điều trị</option>
                                <option v-for="treatmentPackage in userTreatmentPackages" :key="treatmentPackage?.id" :value="treatmentPackage?.id">
                                    {{ treatmentPackage?.treatment ? treatmentPackage.treatment.name : 'Unknown Treatment' }} - Còn lại: {{ treatmentPackage?.remaining_sessions }} buổi
                                </option>
                            </select>
                        </div>

                        <div class="mb-4 relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Liệu trình</label>
                            <select v-model="form.treatment_id" :disabled="!isEditing || isUserTreatmentPackageSelected"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing || isUserTreatmentPackageSelected }">
                                <option v-for="treatment in treatments" :key="treatment?.id" :value="treatment?.id">
                                    {{ treatment?.name }} - {{ formatPrice(treatment?.price) }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loại lịch hẹn</label>
                            <select v-model="form.appointment_type" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }">
                                <option v-for="type in appointmentTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select v-model="form.status" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }">
                                <option value="Pending">Đang chờ</option>
                                <option value="Confirmed">Đã xác nhận</option>
                                <option value="Cancelled">Đã hủy</option>
                                <option value="Completed">Đã hoàn thành</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between mb-4 space-y-4 md:space-y-0">
                        <div class="w-full md:w-1/2 pr-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu</label>
                            <input type="datetime-local" v-model="form.start_time" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }">
                        </div>
                        <div class="w-full md:w-1/2 pl-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc</label>
                            <input type="datetime-local" v-model="form.end_time" :disabled="!isEditing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                :class="{ 'bg-gray-100': !isEditing }">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.note" :disabled="!isEditing" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                            :class="{ 'bg-gray-100': !isEditing }"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button v-if="!isEditing" @click="startEditing" type="button"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Chỉnh sửa
                        </button>
                        <button v-if="isEditing" type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Lưu
                        </button>
                        <button v-if="isEditing" @click="cancelEditing" type="button"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Hủy chỉnh sửa
                        </button>
                        <button v-if="!isEditing" @click="markAsCompleted" type="button"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Đánh dấu đã hoàn thành
                        </button>
                        <button v-if="!isEditing" @click="cancelAppointment" type="button"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Hủy lịch hẹn
                        </button>
                        <button @click="close" type="button"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div v-else>Không có dữ liệu cuộc hẹn</div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { parseISO, format } from 'date-fns';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    appointmentId: [Number, String],
    onClose: Function,
});

const emit = defineEmits(['close', 'appointmentUpdated']);

const form = ref({
    id: null,
    user: null,
    service: null,
    staff: null,
    appointment_date: '',
    time_slot_id: null,
    appointment_type: '',
    status: '',
    note: '',
    staff_user_id: '',
    service_id: '',
});

const isEditing = ref(false);
const staffList = ref([]);
const treatments = ref([]);
const appointmentTypes = [
    { value: 'facial', label: 'Chăm sóc da mặt' },
    { value: 'massage', label: 'Massage' },
    { value: 'weight_loss', label: 'Giảm cân' },
    { value: 'hair_removal', label: 'Triệt lông' },
    { value: 'consultation', label: 'Tư vấn' },
    { value: 'others', label: 'Khác' },
];
const isLoading = ref(true);
const userTreatmentPackages = ref([]);

const isUserTreatmentPackageSelected = computed(() => !!form.value.user_treatment_package_id);
const isTreatmentSelected = computed(() => !!form.value.treatment_id);

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price || 0);
}

const fetchUserTreatmentPackages = async (userId) => {
    if (!userId) return;
    try {
        const response = await axios.get(`/api/user-treatment-packages/${userId}`);
        userTreatmentPackages.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching user treatment packages:', error);
        userTreatmentPackages.value = [];
    }
};

const fetchTreatments = async () => {
    try {
        const response = await axios.get('/api/treatments');
        treatments.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching treatments:', error);
        treatments.value = [];
    }
};

const fetchStaffList = async () => {
    try {
        const response = await axios.get('/api/users/get-staff-list');
        staffList.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching staff list:', error);
        staffList.value = [];
    }
};

const fetchAppointmentDetails = async (id) => {
    try {
        const response = await axios.get(`/api/appointments/${id}`);
        if (response.data.status === 200 && response.data.data) {
            return response.data.data;
        } else {
            console.error('Lỗi khi fetch thông tin cuộc hẹn:', response.data.message);
            return null;
        }
    } catch (error) {
        console.error('Lỗi khi fetch thông tin cuộc hẹn:', error.response?.data?.message || error.message);
        return null;
    }
};

watch(() => props.appointmentId, async (newAppointmentId) => {
    if (newAppointmentId) {
        isLoading.value = true;
        try {
            const appointmentData = await fetchAppointmentDetails(newAppointmentId);
            if (appointmentData) {
                form.value = {
                    id: appointmentData.id || null,
                    user: appointmentData.user || null,
                    treatment: appointmentData.treatment || null,
                    staff: appointmentData.staff || null,
                    start_time: appointmentData.start_time || '',
                    end_time: appointmentData.end_time || '',
                    appointment_type: appointmentData.appointment_type || '',
                    status: appointmentData.status || '',
                    note: appointmentData.note || '',
                    staff_user_id: appointmentData.staff_user_id || '',
                    treatment_id: appointmentData.treatment_id || '',
                    user_treatment_package_id: appointmentData.user_treatment_package_id || null,
                };
                
                if (appointmentData.user_id) {
                    await fetchUserTreatmentPackages(appointmentData.user_id);
                }
                
                await fetchStaffList();
                await fetchTreatments();
            }
        } catch (error) {
            console.error('Error fetching appointment details:', error);
        } finally {
            isLoading.value = false;
        }
    }
}, { immediate: true });

function close() {
    emit('close');
}

function startEditing() {
    isEditing.value = true;
}

function cancelEditing() {
    isEditing.value = false;
    // Restore original data
    fetchAppointmentDetails(props.appointmentId);
}

function updateAppointment() {
    // Implementation of updateAppointment
}

onMounted(() => {
    fetchStaffList();
    fetchTreatments();
});

// ... other functions ...

</script>

<style scoped>
.min-h-40 {
    min-height: 40px;
}
</style>