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
                            <select v-model="form.user_service_package_id" :disabled="isServiceSelected"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                                <option value="">Chọn gói điều trị</option>
                                <option v-for="pkg in userTreatmentPackages" :key="pkg.id" :value="pkg.id">
                                    {{ pkg.service?.service_name || pkg.service_name }} ({{ pkg.remaining_sessions }}
                                    buổi)
                                </option>
                            </select>
                            <div v-if="errors.user_service_package_id" class="mt-1 text-sm text-red-600">
                                {{ errors.user_service_package_id[0] }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                                Dịch vụ
                                <span v-if="isUserServicePackageSelected" class="text-sm text-gray-500">(Đã chọn
                                    gói)</span>
                            </label>
                            <select v-model="form.service_id" :disabled="isUserServicePackageSelected"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                                <option value="">Chọn dịch vụ</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">
                                    {{ service.name || service.service_name }}
                                </option>
                            </select>
                            <div v-if="errors.service_id" class="mt-1 text-sm text-red-600">
                                {{ errors.service_id[0] }}
                            </div>
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
                    <div class="col-span-2 grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">Loại lịch hẹn</label>
                            <input type="text" :value="appointmentTypeDisplay" disabled
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white bg-gray-100">
                        </div>

                        <!-- Slots field -->
                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                                Số lượng slot <span class="text-red-500">*</span>
                            </label>
                            <input type="number" v-model="form.slots" min="1" :max="maxAvailableSlots"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                            <div v-if="errors.slots" class="mt-1 text-sm text-red-600">
                                {{ errors.slots[0] }}
                            </div>
                            <span v-if="maxAvailableSlots" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Còn trống: {{ maxAvailableSlots }} slot
                            </span>
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
                    <button type="submit" class="px-6 py-2.5 rounded-lg font-medium transition-all duration-200" :class="{
                        'bg-indigo-600 text-white hover:bg-indigo-700': isFormValid,
                        'bg-gray-300 text-gray-500 cursor-not-allowed': !isFormValid
                    }">
                        {{ isFormValid ? 'Lưu' : getValidationMessage() }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
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
    staff_id: '',
    appointment_date: props.selectedTimeSlot?.date || '',
    time_slot_id: props.selectedTimeSlot?.id || '',
    appointment_type: 'service',
    status: 'confirmed',
    note: '',
    slots: 1,
    service_id: null,
    user_service_package_id: null
});

// Computed properties
const modalTitle = computed(() => {
    return props.appointments && props.appointments.length > 0 ? 'Chỉnh sửa lịch hẹn' : 'Thêm lịch hẹn mới'
})

const isUserServicePackageSelected = computed(() => !!form.value.user_service_package_id)
const isServiceSelected = computed(() => !!form.value.service_id)

// Thay đổi computed property cho appointmentTypes
const appointmentTypes = computed(() => [
    { value: 'service', label: 'Thực hiện dịch vụ' },
    { value: 'service_package', label: 'Thực hiện combo' },
    { value: 'consultation', label: 'Tư vấn' },
    { value: 'others', label: 'Khác' }
]);

// Sửa lại watch cho service_id và user_service_package_id
watch(() => form.value.service_id, (newServiceId) => {
    if (newServiceId) {
        form.value.user_service_package_id = null; // Reset package selection
        form.value.appointment_type = 'service';
    }
});

watch(() => form.value.user_service_package_id, (newPackageId) => {
    if (newPackageId) {
        form.value.service_id = null; // Reset service selection
        form.value.appointment_type = 'service_package';
    }
});

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
            form.value.appointment_type = service.service_type;
        }
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
    let isValid = true;
    errors.value = {};

    // Validate required fields
    if (!form.value.user_id) {
        errors.value.user_id = ['Vui lòng chọn khách hàng'];
        isValid = false;
    }

    if (!form.value.staff_id) {
        errors.value.staff_id = ['Vui lòng chọn nhân viên'];
        isValid = false;
    }

    if (!form.value.appointment_date) {
        errors.value.appointment_date = ['Vui lòng chọn ngày hẹn'];
        isValid = false;
    }

    if (!form.value.time_slot_id) {
        errors.value.time_slot_id = ['Vui lòng chọn khung giờ'];
        isValid = false;
    }

    // Validate service or package selection
    if (!form.value.service_id && !form.value.user_service_package_id) {
        errors.value.service_selection = ['Vui lòng chọn dịch vụ hoặc gói điều trị'];
        isValid = false;
    }

    // Validate slots
    const slots = parseInt(form.value.slots);
    if (!slots || slots < 1 || slots > 2) {
        errors.value.slots = ['Số lượng slot phải từ 1-2'];
        isValid = false;
    }

    return isValid;
}

function validateAndSubmit() {
    if (!isFormValid.value) {
        return;
    }

    // Tạo formData object
    const formData = {
        user_id: form.value.user_id,
        staff_id: form.value.staff_id,
        appointment_date: form.value.appointment_date,
        time_slot_id: form.value.time_slot_id,
        appointment_type: form.value.appointment_type,
        status: 'pending',
        note: form.value.note || '',
        slots: parseInt(form.value.slots) || 1,
        service_id: form.value.service_id || null,
        user_service_package_id: form.value.user_service_package_id || null
    };

    submitAppointment(formData);
}

function submitAppointment(formData) {

    axios.post(route('appointments.store'), formData)
        .then(response => {
            if (response.data.success) {
                emit('appointmentAdded', response.data.data);
                props.closeModal();
                toast.success(response.data.message || 'Đặt lịch hẹn thành công');
            } else {
                if (response.data.errors) {
                    errors.value = response.data.errors;
                }
                toast.error(response.data.message || 'Có lỗi xảy ra khi tạo lịch hẹn');
            }
        })
        .catch(error => {
            console.error('Submit error:', error);
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
            } else {
                errors.value = {
                    general: ['Đã có lỗi xảy ra khi tạo lịch hẹn']
                };
            }
            toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi tạo lịch hẹn');
        });
}

// Add this after other watch statements
watch(() => services.value, () => {
}, { deep: true });

const isFormValid = computed(() => {
    return Boolean(
        form.value.user_id &&
        form.value.staff_id &&
        form.value.appointment_date &&
        form.value.time_slot_id &&
        form.value.slots >= 1 &&
        form.value.slots <= maxAvailableSlots.value &&
        (
            (form.value.service_id && !form.value.user_service_package_id) ||
            (!form.value.service_id && form.value.user_service_package_id)
        )
    );
});

// Thêm watch cho các giá trị quan trọng
watch(() => form.value, () => {
    errors.value = {};
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

// Watch user_service_package_id để cập nhật appointment_type
watch(() => form.value.user_service_package_id, (newPackageId) => {
    if (newPackageId) {
        form.value.appointment_type = 'service_package';
    } else if (!form.value.service_id) {
        form.value.appointment_type = 'consultation';
    }
});

// Thêm computed property để hiển thị tên loại lịch hẹn
const appointmentTypeDisplay = computed(() => {
    switch (form.value.appointment_type) {
        case 'service':
            return 'Dịch vụ đơn lẻ';
        case 'service_package':
            return 'Gói điều trị';
        case 'consultation':
            return 'Tư vấn';
        default:
            return 'Khác';
    }
});

// Thêm watch để tự động set appointment_type
watch([() => form.value.service_id, () => form.value.user_service_package_id],
    ([newServiceId, newPackageId]) => {
        if (newPackageId) {
            form.value.appointment_type = 'service_package';
        } else if (newServiceId) {
            form.value.appointment_type = 'service';
        } else {
            form.value.appointment_type = 'consultation';
        }
    }
);

function getValidationMessage() {
    if (!form.value.user_id) return 'Vui lòng chọn khách hàng';
    if (!form.value.staff_id) return 'Vui lòng chọn nhân viên';
    if (!form.value.appointment_date) return 'Vui lòng chọn ngày hẹn';
    if (!form.value.time_slot_id) return 'Vui lòng chọn khung giờ';
    if (!form.value.slots || form.value.slots < 1 || form.value.slots > 2)
        return 'Số lượng slot không hợp lệ';
    if (!form.value.service_id && !form.value.user_service_package_id)
        return 'Vui lòng chọn dịch vụ hoặc gói điều trị';
    if (form.value.service_id && form.value.user_service_package_id)
        return 'Vui lòng chỉ chọn một trong hai: dịch vụ hoặc gói điều trị';
    return 'Vui lòng điền đầy đủ thông tin';
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