<template>
    <div v-if="show"
        class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/90 overflow-y-auto h-full w-full flex justify-center items-center z-50 backdrop-blur-sm"
        @click.self="close" @keydown.esc="close" tabindex="0">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-4xl w-full m-4 transform transition-all">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ modalTitle }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Điền thông tin để {{ modalTitle.toLowerCase() }}
                </p>
            </div>

            <form @submit.prevent="validateAndSubmit" class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <!-- User Search with improved UI -->
                    <div class="relative">
                        <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                            Khách hàng <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" v-model="userSearch" @input="searchUsers"
                                placeholder="Tìm theo tên hoặc số điện thoại"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                            <div v-if="userResults.length > 0"
                                class="absolute z-20 mt-1 w-full bg-white dark:bg-slate-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <div v-for="user in userResults" :key="user.id" @click="selectUser(user)"
                                    class="p-3 hover:bg-gray-50 dark:hover:bg-slate-600 cursor-pointer border-b last:border-0 dark:border-gray-600">
                                    <div class="font-medium dark:text-gray-200">{{ user.full_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.phone_number }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Selection with Avatar -->
                    <div>
                        <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                            Nhân viên phụ trách <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.staff_id"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                            <option v-for="staff in staffList" :key="staff.id" :value="staff.id">
                                {{ staff.full_name }}
                            </option>
                        </select>
                    </div>

                    <!-- Service Selection with improved UI -->
                    <div class="col-span-2 grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                                Gói điều trị
                                <span v-if="isServiceSelected" class="text-sm text-gray-500">(Đã chọn dịch vụ)</span>
                            </label>
                            <select v-model="form.user_treatment_package_id" :disabled="isServiceSelected"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                                <option value="">Chọn gói điều trị</option>
                                <option v-for="pkg in userTreatmentPackages" :key="pkg.id" :value="pkg.id">
                                    {{ pkg.treatment?.name || 'Unknown' }} ({{ pkg.remaining_sessions }} buổi)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                                Dịch vụ
                                <span v-if="isUserTreatmentPackageSelected" class="text-sm text-gray-500">(Đã chọn
                                    gói)</span>
                            </label>
                            <select v-model="form.service_id" :disabled="isUserTreatmentPackageSelected"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                                <option value="">Chọn dịch vụ</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">
                                    {{ service.name || service.service_name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Time Slot Selection with Visual Calendar -->
                    <div class="col-span-2 grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                                Ngày hẹn <span class="text-red-500">*</span>
                            </label>
                            <input type="date" v-model="form.appointment_date"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                                Khung giờ <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.time_slot_id"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                                <option value="">Chọn khung giờ</option>
                                <option v-for="slot in timeSlots" :key="slot.id" :value="slot.id"
                                    :disabled="!slot.available" :class="{ 'text-gray-400': !slot.available }">
                                    {{ slot.displayText }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="col-span-2 grid grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">Loại lịch hẹn</label>
                            <select v-model="form.appointment_type"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                                <option v-for="type in appointmentTypes" 
                                    :key="type.value" 
                                    :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Slots field -->
                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                                Số lượng slot <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                v-model="form.slots"
                                min="1"
                                :max="maxAvailableSlots"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                            <span v-if="maxAvailableSlots" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Còn trống: {{ maxAvailableSlots }} slot
                            </span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">Trạng thái</label>
                            <select v-model="form.status"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                                <option value="pending">Đang chờ</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium mb-2 dark:text-gray-300">Ghi chú</label>
                        <textarea v-model="form.note" rows="3"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200"
                            placeholder="Thêm ghi chú cho lịch hẹn..."></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-end space-x-4">
                    <button @click="close" type="button"
                        class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                        Đóng
                    </button>
                    <button type="submit" :disabled="!isFormValid" :class="[
                        'px-6 py-2.5 rounded-lg font-medium transition-all duration-200',
                        isFormValid
                            ? 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:ring-offset-slate-800'
                            : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                    ]">
                        {{ isFormValid ? 'Lưu' : 'Vui lòng điền đầy đủ thông tin' }}
                    </button>
                </div>
            </form>
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
    FACIAL: { value: 'facial', label: 'Chăm sóc da mặt' },
    MASSAGE: { value: 'massage', label: 'Massage' },
    WEIGHT_LOSS: { value: 'weight_loss', label: 'Giảm béo' },
    HAIR_REMOVAL: { value: 'hair_removal', label: 'Triệt lông' },
    CONSULTATION: { value: 'consultation', label: 'Tư vấn' },
    OTHERS: { value: 'others', label: 'Khác' }
};

// Định nghĩa mapping

// Khai báo các ref và state
const userSearch = ref('')
const userResults = ref([])
const selectedUser = ref(null)
const errors = ref({})
const services = ref([])
const timeSlots = ref([])
const userTreatmentPackages = ref([])
const staffList = ref([])

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

// Thay đổi computed property cho appointmentTypes
const appointmentTypes = computed(() => {
    let types = [
        { value: 'consultation', label: 'Tư vấn' },
        { value: 'others', label: 'Khác' }
    ];

    if (form.value.service_id) {
        const service = services.value.find(s => s.id === form.value.service_id);
        if (service?.category?.service_category_name) {
            // Kiểm tra xem category đã tồn tại trong types chưa
            const categoryExists = types.some(type =>
                type.value === service.category.service_category_name
                    .toLowerCase()
                    .replace(/ /g, '_')
            );

            // Nếu chưa tồn tại thì thêm vào đầu mảng
            if (!categoryExists) {
                types.unshift({
                    value: service.category.service_category_name
                        .toLowerCase()
                        .replace(/ /g, '_'),
                    label: service.category.service_category_name
                });
            }
        }
    }

    return types;
});

// Cập nhật watch cho service_id
watch(() => form.value.service_id, async (newServiceId) => {
    form.value.user_treatment_package_id = '';

    if (newServiceId) {
        const service = services.value.find(s => s.id === newServiceId);
        if (service?.category?.service_category_name) {
            // Tự động set appointment_type theo category của service
            form.value.appointment_type = service.category.service_category_name
                .toLowerCase()
                .replace(/ /g, '_');
        } else {
            form.value.appointment_type = 'others';
        }

        if (form.value.appointment_date) {
            fetchTimeSlots();
        }
    } else {
        form.value.appointment_type = 'consultation';
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
        form.value.appointment_type = APPOINTMENT_TYPES.CONSULTATION.value;
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

    // Kiểm tra s slot có vượt quá slot trống không
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
        .then(() => {
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
watch(() => services.value, () => {
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
watch(() => form.value, () => {
}, { deep: true });

const maxAvailableSlots = computed(() => {
    if (!form.value.time_slot_id) return 0;
    const selectedSlot = timeSlots.value.find(slot => slot.id === form.value.time_slot_id);
    if (!selectedSlot) return 0;
    return selectedSlot.max_bookings - selectedSlot.current_bookings;
});

// Thêm watch cho service_id
watch(() => form.service_id, async (newServiceId) => {
    if (newServiceId) {
        try {
            // Gọi API để lấy thông tin service
            const response = await axios.get(`/api/services/${newServiceId}`);
            if (response.data.data) {
                // Cập nhật appointment_type dựa trên category của service
                form.appointment_type = response.data.data.category?.toLowerCase() || 'others';
            }
        } catch (error) {
            console.error('Error fetching service details:', error);
        }
    }
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