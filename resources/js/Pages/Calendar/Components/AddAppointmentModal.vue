<template>
    <div v-if="show"
        class="fixed inset-0 bg-gray-600/50 dark:bg-gray-900/80 overflow-y-auto h-full w-full flex justify-center items-center z-50"
        @click.self="close" @keydown.esc="close" tabindex="0">
        <div class="relative bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-4xl w-full m-4">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4 dark:text-white">{{ modalTitle }}</h2>

                <form @submit.prevent="validateAndSubmit">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4 relative">
                            <label for="user" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Khách hàng</label>
                            <input type="text" v-model="userSearch" @input="searchUsers"
                                placeholder="Tìm kiếm khách hàng"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <ul v-if="userResults.length > 0"
                                class="absolute z-10 mt-1 w-full bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm max-h-40 overflow-y-auto">
                                <li v-for="user in userResults" :key="user.id" @click="selectUser(user)"
                                    class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-slate-600 cursor-pointer dark:text-gray-200">
                                    {{ user.full_name }} ({{ user.phone_number }})
                                </li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label for="staff" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nhân viên phụ trách</label>
                            <select v-model="form.staff_id" id="staff"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option v-for="staffMember in staffList" :key="staffMember.id" :value="staffMember.id">
                                    {{ staffMember.full_name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="user_treatment_package" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gói điều trị</label>
                            <select v-model="form.user_treatment_package_id" id="user_treatment_package"
                                :disabled="isServiceSelected"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn gói điều trị</option>
                                <option v-for="treatmentPackage in userTreatmentPackages" :key="treatmentPackage.id"
                                    :value="treatmentPackage.id">
                                    {{ treatmentPackage.treatment ? treatmentPackage.treatment.name : 'Unknown Treatment' }}
                                    - Còn lại: {{ treatmentPackage.remaining_sessions }} buổi
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="service" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dịch vụ</label>
                            <select v-model="form.service_id" id="service" :disabled="isUserTreatmentPackageSelected"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn dịch vụ</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">
                                    {{ service.name || service.service_name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="appointment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Loại lịch hẹn
                            </label>
                            <select v-model="form.appointment_type" id="appointment_type"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option v-for="(label, value) in appointmentTypes" 
                                        :key="value" 
                                        :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trạng thái</label>
                            <select v-model="form.status" id="status"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="pending">Đang chờ</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="slots" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Số lượng slot
                                <span class="text-xs text-gray-500">(Tối đa 2 slot/khung giờ)</span>
                            </label>
                            <select v-model="form.slots" id="slots"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option :value="1">1 slot</option>
                                <option :value="2">2 slot</option>
                            </select>
                            <p v-if="selectedTimeSlot" class="mt-1 text-sm text-gray-500">
                                Số slot còn trống: {{ getAvailableSlots }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between mb-4 space-y-4 md:space-y-0">
                        <div class="w-full md:w-1/2 pr-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ngày hẹn</label>
                            <input type="date" v-model="form.appointment_date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="w-full md:w-1/2 pl-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Khung giờ</label>
                            <select v-model="form.time_slot_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn khung giờ</option>
                                <option v-for="slot in timeSlots" :key="slot.id" :value="slot.id"
                                    :disabled="!slot.available">
                                    {{ slot.displayText }}
                                    {{ !slot.available ? '(Đã đầy)' : '' }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ghi chú</label>
                        <textarea v-model="form.note" id="note" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="submit" :disabled="!isFormValid" :class="[
                            'px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 dark:ring-offset-slate-800',
                            isFormValid
                                ? 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500'
                                : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                        ]">
                            {{ isFormValid ? 'Lưu' : 'Vui lòng điền đầy đủ thông tin' }}
                        </button>
                        <button @click="close" type="button"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:ring-offset-slate-800">
                            Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';

// Khai báo props và emit
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

// Định nghĩa constants
const APPOINTMENT_TYPES = {
    FACIAL: 'facial',
    MASSAGE: 'massage',
    WEIGHT_LOSS: 'weight_loss',
    HAIR_REMOVAL: 'hair_removal',
    CONSULTATION: 'consultation',
    OTHERS: 'others'
};

// Định nghĩa mapping
const CATEGORY_NAME_MAPPING = {
    'Chăm sóc da mặt': { value: APPOINTMENT_TYPES.FACIAL, label: 'Chăm sóc da mặt' },
    'Massage': { value: APPOINTMENT_TYPES.MASSAGE, label: 'Massage' },
    'Giảm béo': { value: APPOINTMENT_TYPES.WEIGHT_LOSS, label: 'Giảm béo' },
    'Triệt lông': { value: APPOINTMENT_TYPES.HAIR_REMOVAL, label: 'Triệt lông' }
};

// Khai báo các ref và state
const userSearch = ref('')
const userResults = ref([])
const selectedUser = ref(null)
const errors = ref({})
const services = ref([])
const timeSlots = ref([])
const selectedService = ref(null)
const userTreatmentPackages = ref([])
const staffList = ref([])
const errorMessage = ref('')
const appointments = ref(props.appointments)

// Định nghĩa hàm resetForm trước khi sử dụng trong watch
const resetForm = () => {
    form.value = {
        user_id: '',
        service_id: '',
        staff_id: staffList.value.length > 0 ? staffList.value[0].id : '',
        appointment_date: props.selectedTimeSlot?.date || '',
        time_slot_id: props.selectedTimeSlot?.timeSlotId || '',
        appointment_type: 'consultation',
        status: 'pending',
        note: '',
        slots: 1
    };
    selectedUser.value = null;
    userSearch.value = '';
};

// Khởi tạo form
const form = ref({
    user_id: '',
    service_id: '',
    staff_id: '',
    appointment_date: props.selectedTimeSlot?.date || '',
    time_slot_id: props.selectedTimeSlot?.timeSlotId || '',
    appointment_type: 'consultation',
    status: 'pending',
    note: '',
    slots: 1
});

// Computed properties
const modalTitle = computed(() => {
    return props.appointments && props.appointments.length > 0 ? 'Chỉnh sửa lịch hẹn' : 'Thêm lịch hẹn mới'
})

const isUserTreatmentPackageSelected = computed(() => !!form.value.user_treatment_package_id)
const isServiceSelected = computed(() => !!form.value.service_id)

// Computed property cho appointment types
const appointmentTypes = computed(() => {
    if (form.value.service_id) {
        // Tìm label tương ứng với appointment type hiện tại
        const currentTypeLabel = Object.values(CATEGORY_NAME_MAPPING)
            .find(mapping => mapping.value === form.value.appointment_type)?.label || 'Khác';

        const baseTypes = [
            {
                value: form.value.appointment_type,
                label: currentTypeLabel // Sử dụng label đã tìm được
            },
            { value: APPOINTMENT_TYPES.CONSULTATION, label: 'Tư vấn' },
            { value: APPOINTMENT_TYPES.OTHERS, label: 'Khác' }
        ];

        // Remove duplicates based on value
        return baseTypes.filter((type, index, self) =>
            index === self.findIndex(t => t.value === type.value)
        );
    }

    return [
        { value: APPOINTMENT_TYPES.CONSULTATION, label: 'Tư vấn' },
        { value: APPOINTMENT_TYPES.OTHERS, label: 'Khác' }
    ];
});

// Watch cho service_id
watch(() => form.value.service_id, async (newServiceId) => {
    form.value.user_treatment_package_id = '';

    if (newServiceId) {
        try {
            // Fetch service details including category
            const response = await axios.get(`/api/services/${newServiceId}`);
            const service = response.data.data;

            // Set appointment type based on service category
            if (service.category?.service_category_name) {
                const categoryName = service.category.service_category_name;
                const appointmentType = CATEGORY_NAME_MAPPING[categoryName]?.value || APPOINTMENT_TYPES.OTHERS;
                form.value.appointment_type = appointmentType;
            } else {
                form.value.appointment_type = APPOINTMENT_TYPES.OTHERS;
            }
        } catch (error) {
            console.error('Error fetching service details:', error);
            form.value.appointment_type = APPOINTMENT_TYPES.OTHERS;
        }

        if (form.value.appointment_date) {
            fetchTimeSlots();
        }
    } else {
        form.value.appointment_type = APPOINTMENT_TYPES.CONSULTATION;
    }
}, { immediate: true });

// Fetch time slots
const fetchTimeSlots = async () => {
    try {
        const response = await axios.get('/api/time-slots', {
            params: {
                date: form.value.appointment_date,
                service_id: form.value.service_id
            }
        })

        if (response.data && response.data.data) {
            timeSlots.value = response.data.data.map(slot => ({
                ...slot,
                available: slot.current_bookings < slot.max_bookings,
                displayText: `${slot.start_time.substring(0, 5)} - ${slot.end_time.substring(0, 5)} (${slot.current_bookings}/${slot.max_bookings})`
            }))
        }
    } catch (error) {
        console.error('Error fetching time slots:', error)
        timeSlots.value = []
    }
};

// Thêm watch để tự động fetch time slots khi ngày hoặc dịch vụ thay đổi
watch([() => form.value.appointment_date, () => form.value.service_id], () => {
    if (form.value.appointment_date) {
        fetchTimeSlots();
    }
});

// Watch service_id để cập nhật appointment_type
watch(() => form.value.service_id, (newServiceId) => {
    if (newServiceId) {
        const service = services.value.find(s => s.id === newServiceId);
        if (service) {
            // Tự động set appointment_type theo service_type của service
            form.value.appointment_type = service.service_type;
        }
    } else {
        // Reset về consultation khi không chọn service
        form.value.appointment_type = APPOINTMENT_TYPES.CONSULTATION;
    }
});


watch(() => props.appointments, (newAppointments) => {
    if (newAppointments && newAppointments.length > 0) {
        resetForm();
        Object.keys(newAppointments[0]).forEach(key => {
            if (form.value[key] !== undefined) {
                form.value[key] = newAppointments[0][key]
            }
        })
        form.value.start_date = formatDateTimeForInput(newAppointments[0].start)
        form.value.end_date = formatDateTimeForInput(newAppointments[0].end)
    } else {
        resetForm()
    }
}, { immediate: true })

// Cập nhật watch cho selectedTimeSlot
watch(() => props.selectedTimeSlot, (newTimeSlot) => {
    if (newTimeSlot && newTimeSlot.date) {
        nextTick(() => {
            form.value = {
                ...form.value,
                appointment_date: newTimeSlot.date,
                time_slot_id: newTimeSlot.timeSlotId
            };
        });
    }
}, { immediate: true });

function formatDate(date) {
    return new Date(date).toISOString().split('T')[0];
}

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

const close = () => {
    resetForm();
    emit('close');
};

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
    selectedUser.value = user;
    form.value.user_id = user.id;
    userResults.value = [];
    userSearch.value = user.full_name;
    fetchUserTreatmentPackages(user.id);
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

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

onMounted(() => {
    fetchServices()
    fetchStaffList()
    document.addEventListener('keydown', handleKeyDown)
    if (staffList.value.length > 0) {
        form.staff_id = staffList.value[0].id
    }
    fetchTimeSlots();
    fetchStaffList();
})

const fetchServices = async () => {
    try {
        const response = await axios.get('/api/services/appointment');
        if (response.data && response.data.data) {
            services.value = response.data.data.map(service => ({
                ...service,
                service_category: service.service_category || null
            }));
        }
    } catch (error) {
        console.error('Error fetching services:', error);
        services.value = [];
    }
};

function fetchStaffList() {
    axios.get('/api/users/get-staff-list')
        .then(response => {
            if (response.data && response.data.data) {
                staffList.value = response.data.data;
                if (!form.value.staff_id && staffList.value.length > 0) {
                    form.value.staff_id = staffList.value[0].id;
                }
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
    errors.value = {};
    let isValid = true;

    // Kiểm tra user_id
    if (!form.value.user_id) {
        errors.value.user_id = ['Vui lòng chọn khách hàng'];
        isValid = false;
    }

    // Kiểm tra staff_id
    if (!form.value.staff_id) {
        errors.value.staff_id = ['Vui lòng chọn nhân viên phụ trách'];
        isValid = false;
    }

    // Kiểm tra service_id hoặc user_treatment_package_id
    if (!form.value.service_id && !form.value.user_treatment_package_id) {
        errors.value.service = ['Vui lòng chọn dịch vụ hoặc gói điều trị'];
        isValid = false;
    }

    // Kiểm tra time_slot_id
    if (!form.value.time_slot_id) {
        errors.value.time_slot_id = ['Vui lòng chọn khung giờ'];
        isValid = false;
    }

    // Kiểm tra tính khả dụng của time slot
    if (form.value.time_slot_id) {
        const selectedSlot = timeSlots.value.find(slot =>
            slot.id === form.value.time_slot_id
        );

        if (selectedSlot && !selectedSlot.available) {
            errors.value.time_slot_id = ['Khung giờ này đã đầy'];
            isValid = false;
        }
    }

    // Kiểm tra slots
    if (!form.value.slots || form.value.slots < 1 || form.value.slots > 2) {
        errors.value.slots = ['Số lượng slot phải từ 1-2'];
        isValid = false;
    }

    // Kiểm tra số slot có vượt quá slot trống không
    if (form.value.time_slot_id) {
        const selectedSlot = timeSlots.value.find(slot => slot.id === form.value.time_slot_id);
        if (selectedSlot) {
            const availableSlots = selectedSlot.max_bookings - selectedSlot.booked_slots;
            if (form.value.slots > availableSlots) {
                errors.value.slots = [`Chỉ còn ${availableSlots} slot trống`];
                isValid = false;
            }
        }
    }

    return isValid;
}

function validateAndSubmit() {
    if (validateForm()) {
        const formData = {
            user_id: form.value.user_id,
            staff_id: form.value.staff_id,
            service_id: form.value.service_id || null,
            user_treatment_package_id: form.value.user_treatment_package_id || null,
            appointment_date: form.value.appointment_date,
            time_slot_id: form.value.time_slot_id,
            appointment_type: form.value.appointment_type,
            status: form.value.status,
            note: form.value.note || '',
            slots: form.value.slots
        };

        submitAppointment(formData);
    }
}

function submitAppointment(formData) {
    axios.post(route('appointments.store'), {
        user_id: formData.user_id,
        staff_id: formData.staff_id,
        service_id: formData.service_id,
        appointment_date: formData.appointment_date,
        time_slot_id: formData.time_slot_id,
        appointment_type: formData.appointment_type,
        status: formData.status,
        note: formData.note,
        slots: formData.slots
    })
        .then(response => {
            // Emit cả save và appointmentAdded events
            emit('save', formData);
            emit('appointmentAdded');
            props.closeModal();
        })
        .catch(error => {
            console.error('Error creating appointment:', error.response ? error.response.data : error);
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
            }
            // Thêm emit error event nếu cần
            // emit('error', error.response?.data?.errors || 'Có lỗi xảy ra khi tạo lịch hẹn');
        });
}

// Add this after other watch statements
watch(() => services.value, (newServices) => {
}, { deep: true });

const isFormValid = computed(() => {
    return Boolean(
        form.value.user_id &&
        form.value.service_id &&
        form.value.appointment_date &&
        form.value.time_slot_id &&
        form.value.slots >= 1 &&
        form.value.slots <= 2
    );
});

// Thêm watch cho các giá trị quan trọng
watch(() => form.value, (newValue) => {
}, { deep: true });

const getAvailableSlots = computed(() => {
    if (!form.value.time_slot_id || !timeSlots.value.length) return 0;
    
    const selectedSlot = timeSlots.value.find(slot => slot.id === form.value.time_slot_id);
    if (!selectedSlot) return 0;

    // Kiểm tra nếu có available_slots trong dữ liệu
    if ('available_slots' in selectedSlot) {
        return selectedSlot.available_slots;
    }

    // Tính toán slots còn trống từ max_bookings và booked_slots
    const maxBookings = selectedSlot.max_bookings || 2; // Mặc định là 2 nếu không có
    const bookedSlots = selectedSlot.booked_slots || 0;
    
    return Math.max(0, maxBookings - bookedSlots);
});
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