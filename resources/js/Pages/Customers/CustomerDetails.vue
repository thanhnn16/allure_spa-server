<script setup>
import { ref, reactive, computed, defineComponent, h, onMounted, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import {
    mdiAccount, mdiPackageVariant, mdiReceipt, mdiGift, mdiDelete,
    mdiAlert, mdiPencil, mdiTicketPercent, mdiCamera,
    mdiCheckCircle,
    mdiProgressClock,
    mdiCalendarClock,
    mdiClockOutline,
    mdiCalendar,
    mdiPlus,
    mdiHome,
    mdiOfficeBuilding,
    mdiMapMarker,
} from '@mdi/js'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { Head } from '@inertiajs/vue3'
import NotificationBar from '@/Components/NotificationBar.vue'
import axios from 'axios'
import UserAvatar from '@/Components/UserAvatar.vue'

import { format, parseISO } from 'date-fns'
import { vi } from 'date-fns/locale'

const props = defineProps({
    user: Object,
    upcomingBirthdays: Number
})

const tabs = [
    { id: 'personal', label: 'Thông tin cá nhân', icon: mdiAccount },
    { id: 'service_combos', label: 'Combo dịch vụ', icon: mdiPackageVariant },
    { id: 'invoices', label: 'Đơn hàng', icon: mdiReceipt },
    { id: 'vouchers', label: 'Vouchers', icon: mdiGift },
]

const InfoItem = defineComponent({
    props: {
        label: String,
        value: [String, Number]
    },
    render() {
        return h('div', [
            h('span', { class: 'text-gray-600 dark:text-gray-400 text-sm' }, this.label),
            h('p', { class: 'font-medium dark:text-white' }, this.value || 'N/A')
        ])
    }
})

const activeTab = ref('personal')
const showEditModal = ref(false)
const editedUser = reactive({ ...props.user })
const notification = ref(null)
const showDeleteModal = ref(false)
const showAddressModal = ref(false)
const editingAddress = ref(null)
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const addressForm = reactive({
    province: '',
    district: '',
    ward: '',
    address: '',
    address_type: 'home',
    is_default: false,
    is_temporary: false
})

const showAssignVoucherModal = ref(false)
const availableVouchers = ref([])
const voucherForm = reactive({
    voucher_id: '',
    total_uses: 1
})

const showCreateVoucherForm = ref(false)
const newVoucherForm = reactive({
    code: '',
    discount_type: 'percentage',
    discount_value: 0,
    min_order_value: 0,
    max_discount_amount: 0,
    usage_limit: 1,
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    is_unlimited: false,
    uses_per_user: 1,
    status: 'active'
})

const showVoucherDetailModal = ref(false)
const selectedVoucher = ref(null)

const openEditModal = () => {
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
}

const openDeleteModal = () => {
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
}


const updateUser = async () => {
    try {
        const response = await axios.put(`/users/${props.user.id}`, editedUser)
        Object.assign(props.user, response.data.user)
        showEditModal.value = false
        notification.value = { type: 'success', message: response.data.message }
    } catch (error) {
        notification.value = { type: 'danger', message: error.response?.data?.message || 'Có lỗi xảy ra' }
    }
}


const formattedDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`;
};

const formatGender = computed(() => {
    switch (props.user.gender) {
        case 'male':
            return 'Nam';
        case 'female':
            return 'Nữ';
        default:
            return 'Khác';
    }
});

const safeUser = computed(() => props.user || {});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};


const basicInfo = computed(() => [
    { label: 'Họ và tên', value: safeUser.value.full_name },
    { label: 'Số điện thoại', value: safeUser.value.phone_number },
    { label: 'Email', value: safeUser.value.email },
    { label: 'Giới tính', value: formatGender.value },
    { label: 'Ngày sinh', value: formattedDate(safeUser.value.date_of_birth) },
    { label: 'Trạng thái', value: safeUser.value.deleted_at ? 'Đã bị xóa' : 'Đang hoạt động' }
]);

const additionalInfo = computed(() => [
    { label: 'Điểm tích lũy', value: safeUser.value.point },
    { label: 'Số lần mua hàng', value: safeUser.value.purchase_count },
    { label: 'Ngày tạo', value: formattedDate(safeUser.value.created_at) },
    { label: 'Ngày chỉnh sửa', value: formattedDate(safeUser.value.updated_at) }
]);

const openAddAddressModal = () => {
    editingAddress.value = null
    resetAddressForm()
    showAddressModal.value = true
}

const editAddress = (address) => {
    editingAddress.value = address
    Object.assign(addressForm, address)
    showAddressModal.value = true
}

const closeAddressModal = () => {
    showAddressModal.value = false
    resetAddressForm()
}

const resetAddressForm = () => {
    Object.assign(addressForm, {
        province: '',
        district: '',
        ward: '',
        address: '',
        address_type: 'home',
        is_default: false,
        is_temporary: false
    })
    districts.value = []
    wards.value = []
}

const submitAddress = async () => {
    try {
        const data = { ...addressForm }
        if (editingAddress.value) {
            await axios.put(`/addresses/${editingAddress.value.id}`, data)
        } else {
            data.user_id = props.user.id
            await axios.post('/addresses', data)
        }

        // Refresh user data
        const response = await axios.get(`/users/${props.user.id}`)
        Object.assign(props.user, response.data.data)

        closeAddressModal()
        notification.value = {
            type: 'success',
            message: `Đã ${editingAddress.value ? 'cập nhật' : 'thêm'} địa chỉ thành công`
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'C lỗi xảy ra'
        }
    }
}

const deleteAddress = async (address) => {
    if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) return

    try {
        await axios.delete(`/addresses/${address.id}`)
        const response = await axios.get(`/users/${props.user.id}`)
        Object.assign(props.user, response.data.data)
        notification.value = {
            type: 'success',
            message: 'Đã xóa địa chỉ thành công'
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi xóa địa chỉ'
        }
    }
}

const formatAddressType = (type) => {
    switch (type) {
        case 'home':
            return 'Nhà riêng';
        case 'work':
            return 'Nơi làm việc';
        case 'shipping':
            return 'Địa chỉ giao hàng';
        case 'others':
            return 'Khác';
        default:
            return type;
    }
}


const handleAvatarUpload = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append('avatar', file)

    try {
        const response = await axios.post('/api/user/avatar', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        // Update user data with new avatar
        if (response.data.data.user) {
            Object.assign(props.user, response.data.data.user)
        }

        notification.value = {
            type: 'success',
            message: 'Avatar đã được cập nhật thành công'
        }
    } catch (error) {
        console.error('Upload error:', error)
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi tải lên avatar'
        }
    }
}


const closeAssignVoucherModal = () => {
    showAssignVoucherModal.value = false;
    showCreateVoucherForm.value = false;
    resetVoucherForm();
};

const assignVoucher = async () => {
    try {
        await axios.post('/vouchers/assign-to-user', {
            user_id: props.user.id,
            ...voucherForm
        })

        // Refresh vouchers list
        const response = await axios.get(`/vouchers/user/${props.user.id}/vouchers`)
        props.user.vouchers = response.data.data

        closeAssignVoucherModal()
        notification.value = {
            type: 'success',
            message: 'Đã gán voucher thành công'
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi gán voucher'
        }
    }
}

const createAndAssignVoucher = async () => {
    try {
        // Validate form data
        if (!newVoucherForm.code || !newVoucherForm.discount_value) {
            throw new Error('Vui lòng điền đầy đủ thông tin voucher');
        }

        // First create the voucher
        const createResponse = await axios.post('/vouchers', {
            ...newVoucherForm,
            description: `Voucher created for user ${props.user.full_name}`,
        });

        if (!createResponse.data.data?.id) {
            throw new Error('Không thể tạo voucher');
        }

        // Then assign it to the user

        // Refresh vouchers list
        const userVouchersResponse = await axios.get(`/vouchers/user/${props.user.id}/vouchers`);
        if (userVouchersResponse.data.data) {
            props.user.vouchers = userVouchersResponse.data.data;
        }

        showCreateVoucherForm.value = false;
        closeAssignVoucherModal();
        notification.value = {
            type: 'success',
            message: 'Đã tạo và gán voucher thành công'
        };
    } catch (error) {
        console.error('Error creating/assigning voucher:', error);
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || error.message || 'Có lỗi xảy ra khi tạo và gán voucher'
        };
    }
};

const resetVoucherForm = () => {
    Object.assign(newVoucherForm, {
        code: '',
        discount_type: 'percentage',
        discount_value: 0,
        min_order_value: 0,
        max_discount_amount: 0,
        usage_limit: 1,
        start_date: new Date().toISOString().split('T')[0],
        end_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        is_unlimited: false,
        uses_per_user: 1,
        status: 'active'
    });
    voucherForm.total_uses = 1;
};

const returnVoucher = async (voucherId) => {
    if (!confirm('Bạn có chắc muốn trả lại voucher này?')) return

    try {
        await axios.post('/api/vouchers/return', {
            user_id: props.user.id,
            voucher_id: voucherId
        })

        // Refresh vouchers list
        const response = await axios.get(`/vouchers/user/${props.user.id}/vouchers`)
        props.user.vouchers = response.data.data

        notification.value = {
            type: 'success',
            message: 'Đã trả lại voucher thành công'
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi trả lại voucher'
        }
    }
}

// Update getUserVouchers method
const getUserVouchers = async () => {
    try {
        const response = await axios.get(`/vouchers/user/${props.user.id}/vouchers`);
        if (response.data.data) {
            props.user.vouchers = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching vouchers:', error);
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Không thể tải danh sách voucher'
        };
    }
};

const getUserServicePackages = async () => {
    try {
        const response = await axios.get(`/api/users/${props.user.id}/service-packages`);
        if (response.data.data) {
            props.user.userServicePackages = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching service packages:', error);
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Không thể tải danh sách liệu trình'
        };
    }
};

// Update onMounted to fetch both vouchers and service packages
onMounted(() => {
    if (props.user?.id) {
        getUserVouchers();
        getUserServicePackages();
        loadProvinces(); // Thêm dòng này
    }
    console.log('user', props.user);
    console.log('userServicePackages', props.user.userServicePackages);
});

// Add isAsideLgActive ref
const isAsideLgActive = ref(true)

// Thêm watch để theo dõi thay đổi của availableVouchers
watch(availableVouchers, (newValue) => {
    console.log('availableVouchers changed:', newValue);
});

const loadAvailableVouchers = async () => {
    try {
        const response = await axios.get('/vouchers');
        console.log('Available vouchers response:', response.data);
        if (response.data.success) {
            availableVouchers.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading vouchers:', error);
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Không thể tải danh sách voucher'
        };
    }
};

const openAssignVoucherModal = async () => {
    showAssignVoucherModal.value = true;
    await loadAvailableVouchers();
};

const formatOrderStatus = (status) => {
    switch (status) {
        case 'pending':
            return 'Chờ xử lý';
        case 'processing':
            return 'Đang xử lý';
        case 'completed':
            return 'Hoàn thành';
        case 'cancelled':
            return 'Đ�� hủy';
        default:
            return status;
    }
};

const cancelOrder = async (orderId) => {
    if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) return;

    try {
        const response = await axios.delete(`/api/orders/${orderId}/cancel`);

        // Refresh user data to get updated orders
        const userResponse = await axios.get(`/users/${props.user.id}`);
        Object.assign(props.user, userResponse.data.data);

        notification.value = {
            type: 'success',
            message: 'Đã hủy đơn hàng thành công'
        };
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi hủy đơn hàng'
        };
    }
};

const userServicePackages = computed(() => {
    if (!safeUser.value?.userServicePackages) {
        return [];
    }

    return safeUser.value.userServicePackages
        .filter(p => p && (p.status === 'active' || p.status === 'pending'))
        .sort((a, b) => {
            if (a.status === 'pending' && b.status !== 'pending') return -1;
            if (a.status !== 'pending' && b.status === 'pending') return 1;
            return new Date(a.expiry_date) - new Date(b.expiry_date);
        });
});

const completedPackages = computed(() => {
    return safeUser.value.userServicePackages?.filter(p => p.status === 'completed') || [];
});

const completedTreatments = computed(() => {
    return completedPackages.value.length;
});

const activeTreatments = computed(() => {
    return userServicePackages.value.length;
});

const nextTreatmentDate = computed(() => {
    const nextAppointment = userServicePackages.value
        .map(p => p.next_appointment)
        .filter(Boolean)
        .sort((a, b) => new Date(a.appointment_date) - new Date(b.appointment_date))[0];

    return nextAppointment ? formattedDate(nextAppointment.appointment_date) : null;
});

const formatPackageStatus = (status) => {
    switch (status) {
        case 'active':
            return 'Đang thực hiện';
        case 'pending':
            return 'Chờ bắt đầu';
        case 'completed':
            return 'Hoàn thành';
        case 'expired':
            return 'Đã hết hạn';
        default:
            return status;
    }
};

const openVoucherDetailModal = (voucher) => {
    selectedVoucher.value = voucher
    showVoucherDetailModal.value = true
}

const closeVoucherDetailModal = () => {
    showVoucherDetailModal.value = false
    selectedVoucher.value = null
}

const toggleVoucherStatus = async (voucherId) => {
    try {
        const response = await axios.post(`/api/vouchers/${voucherId}/toggle-status`)
        if (response.data.success) {
            // Update the voucher status in the list
            const voucherIndex = props.user.vouchers.findIndex(v => v.id === voucherId)
            if (voucherIndex !== -1) {
                props.user.vouchers[voucherIndex] = response.data.data
            }
            // Update selected voucher if modal is open
            if (selectedVoucher.value?.id === voucherId) {
                selectedVoucher.value = response.data.data
            }

            notification.value = {
                type: 'success',
                message: response.data.message
            }
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật trạng thái voucher'
        }
    }
}

const showAddTreatmentModal = ref(false)
const treatmentForm = reactive({
    staff_user_id: '',
    start_time: '',
    end_time: '',
    result: '',
    notes: ''
})

const openAddTreatmentModal = async (servicePackage) => {
    selectedPackage.value = servicePackage
    showAddTreatmentModal.value = true
    await loadStaffList()

    // Reset form
    Object.assign(treatmentForm, {
        staff_user_id: '',
        start_time: '',
        end_time: '',
        result: '',
        notes: ''
    })
}

const closeAddTreatmentModal = () => {
    showAddTreatmentModal.value = false
}

const submitTreatmentSession = async () => {
    try {
        const response = await axios.post('/api/treatment-sessions', {
            ...treatmentForm,
            user_service_package_id: selectedPackage.value.id
        })

        // Refresh user data to get updated service packages
        const userResponse = await axios.get(`/users/${props.user.id}`)
        Object.assign(props.user, userResponse.data.data)

        closeAddTreatmentModal()
        notification.value = {
            type: 'success',
            message: 'Thêm lịch sử dịch vụ thành công'
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi thêm lịch sử dịch vụ'
        }
    }
}

const staffList = ref([])

// Add this method to load staff list when opening modal
const loadStaffList = async () => {
    try {
        const response = await axios.get('/api/users/get-staff-list')
        staffList.value = response.data.data
    } catch (error) {
        console.error('Error loading staff:', error)
        notification.value = {
            type: 'danger',
            message: 'Không thể tải danh sách nhân viên'
        }
    }
}

// Add these formatting functions
const formatDateTime = (datetime) => {
    if (!datetime) return ''
    return format(parseISO(datetime), 'HH:mm - dd/MM/yyyy', { locale: vi })
}

const formatDate = (date) => {
    if (!date) return ''
    return format(parseISO(date), 'dd/MM/yyyy', { locale: vi })
}

const formatTime = (time) => {
    if (!time) return ''
    // Handle time string in HH:mm:ss format
    const [hours, minutes] = time.split(':')
    return `${hours}:${minutes}`
}

// Expose these functions to template
defineExpose({
    formatDateTime,
    formatDate,
    formatTime
})

// Thêm watch để handle is_default và is_temporary
watch(() => addressForm.is_default, (newValue) => {
    if (newValue) {
        addressForm.is_temporary = false
    }
})

watch(() => addressForm.is_temporary, (newValue) => {
    if (newValue) {
        addressForm.is_default = false
    }
})

// Thêm methods để load địa chỉ hành chính
const loadProvinces = async () => {
    try {
        const response = await axios.get('https://oapi.vn/api/provinces')
        provinces.value = response.data.data
    } catch (error) {
        console.error('Error loading provinces:', error)
    }
}

const loadDistricts = async (provinceCode) => {
    if (!provinceCode) {
        districts.value = []
        wards.value = []
        return
    }
    try {
        const response = await axios.get(`https://oapi.vn/api/districts/${provinceCode}`)
        districts.value = response.data.data
        addressForm.district = ''
        addressForm.ward = ''
    } catch (error) {
        console.error('Error loading districts:', error)
    }
}

const loadWards = async (districtCode) => {
    if (!districtCode) {
        wards.value = []
        return
    }
    try {
        const response = await axios.get(`https://oapi.vn/api/wards/${districtCode}`)
        wards.value = response.data.data
        addressForm.ward = ''
    } catch (error) {
        console.error('Error loading wards:', error)
    }
}
</script>
<template>
    <LayoutAuthenticated>

        <Head title="Chi tiết khách hàng" />
        <SectionMain :is-aside-lg-active="isAsideLgActive">
            <!-- Notification -->
            <NotificationBar v-if="notification" :color="notification.type" :icon="mdiAlert">
                {{ notification.message }}
            </NotificationBar>

            <!-- Tabs -->
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-4 mb-6">
                <div class="flex space-x-2">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        class="px-4 py-2 rounded-md transition-all duration-200" :class="[
                            activeTab === tab.id
                                ? 'bg-blue-500 text-white shadow-md'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                        ]">
                        <div class="flex items-center space-x-2">
                            <BaseIcon :path="tab.icon" class="w-5 h-5" />
                            <span>{{ tab.label }}</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Personal Info Tab -->
            <div v-if="activeTab === 'personal'" class="space-y-6">
                <!-- Customer Overview Card -->
                <CardBox class="!p-6 dark:bg-slate-900">
                    <div class="flex flex-col md:flex-row md:items-start md:space-x-6">
                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center space-y-3 mb-6 md:mb-0">
                            <div class="relative">
                                <UserAvatar :fullName="safeUser.full_name" :avatarUrl="safeUser.avatar_url"
                                    size="2xl" />
                                <label class="absolute bottom-0 right-0 bg-blue-500 dark:bg-blue-600 
                                    rounded-full p-2 cursor-pointer hover:bg-blue-600 dark:hover:bg-blue-700 
                                    transition-colors shadow-md">
                                    <input type="file" @change="handleAvatarUpload" accept="image/*" class="hidden">
                                    <BaseIcon :path="mdiCamera" class="w-5 h-5 text-white" />
                                </label>
                            </div>
                            <div class="text-center">
                                <h2 class="text-xl font-bold dark:text-white">{{ safeUser.full_name }}</h2>
                                <p class="text-gray-600 dark:text-gray-400">{{ safeUser.phone_number }}</p>
                            </div>
                        </div>

                        <!-- Customer Stats -->
                        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <BaseIcon :path="mdiPackageVariant"
                                        class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                    <span class="text-sm text-blue-600 dark:text-blue-400">Liệu trình</span>
                                </div>
                                <p class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300">
                                    {{ activeTreatments }}
                                </p>
                                <p class="text-sm text-blue-600 dark:text-blue-400">Đang thực hiện</p>
                            </div>

                            <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <BaseIcon :path="mdiCheckCircle"
                                        class="w-6 h-6 text-green-600 dark:text-green-400" />
                                    <span class="text-sm text-green-600 dark:text-green-400">Hoàn thành</span>
                                </div>
                                <p class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">
                                    {{ completedTreatments }}
                                </p>
                                <p class="text-sm text-green-600 dark:text-green-400">Liệu trình</p>
                            </div>

                            <div class="bg-purple-50 dark:bg-purple-900/30 p-4 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <BaseIcon :path="mdiTicketPercent"
                                        class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                    <span class="text-sm text-purple-600 dark:text-purple-400">Điểm</span>
                                </div>
                                <p class="mt-2 text-2xl font-bold text-purple-700 dark:text-purple-300">
                                    {{ safeUser.point || 0 }}
                                </p>
                                <p class="text-sm text-purple-600 dark:text-purple-400">Tích lũy</p>
                            </div>

                            <div class="bg-orange-50 dark:bg-orange-900/30 p-4 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <BaseIcon :path="mdiReceipt" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                                    <span class="text-sm text-orange-600 dark:text-orange-400">Đơn hàng</span>
                                </div>
                                <p class="mt-2 text-2xl font-bold text-orange-700 dark:text-orange-300">
                                    {{ safeUser.purchase_count || 0 }}
                                </p>
                                <p class="text-sm text-orange-600 dark:text-orange-400">Tổng số</p>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <!-- Basic Info & Additional Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <CardBox class="!p-6 dark:bg-slate-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold dark:text-white">Thông tin cơ bản</h3>
                            <BaseButton label="Chỉnh sửa" color="info" @click="openEditModal" :icon="mdiPencil" small />
                        </div>
                        <div class="space-y-4">
                            <div v-for="(item, index) in basicInfo" :key="index"
                                class="flex items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ item.label }}</p>
                                    <p class="font-medium dark:text-white">{{ item.value }}</p>
                                </div>
                            </div>
                        </div>
                    </CardBox>

                    <CardBox class="!p-6 dark:bg-slate-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold dark:text-white">Thông tin bổ sung</h3>
                        </div>
                        <div class="space-y-4">
                            <div v-for="(item, index) in additionalInfo" :key="index"
                                class="flex items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ item.label }}</p>
                                    <p class="font-medium dark:text-white">{{ item.value }}</p>
                                </div>
                            </div>
                            <div class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Ghi chú</p>
                                <p class="font-medium dark:text-white mt-1">{{ safeUser.note || 'Không có ghi chú' }}
                                </p>
                            </div>
                        </div>
                    </CardBox>
                </div>

                <!-- Addresses Section -->
                <CardBox class="!p-6 dark:bg-slate-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold dark:text-white">Địa chỉ</h3>
                        <BaseButton label="Thêm địa chỉ" color="info" @click="openAddAddressModal" :icon="mdiPlus"
                            small />
                    </div>

                    <div v-if="safeUser.addresses?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="address in safeUser.addresses" :key="address.id"
                            class="p-4 rounded-lg border dark:border-slate-700 hover:shadow-md transition-shadow relative"
                            :class="{ 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/30': address.is_default }">
                            <div class="flex justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-start space-x-2">
                                        <BaseIcon :path="address.address_type === 'home' ? mdiHome : mdiOfficeBuilding"
                                            class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                        <div>
                                            <p class="font-medium dark:text-white">{{ address.address }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ address.district }}, {{ address.province }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-if="address.is_default"
                                            class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                                            Mặc định
                                        </span>
                                        <span v-if="address.is_temporary"
                                            class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full">
                                            Tạm thời
                                        </span>
                                        <span
                                            class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 rounded-full">
                                            {{ formatAddressType(address.address_type) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <BaseButton color="info" :icon="mdiPencil" small @click="editAddress(address)" />
                                    <BaseButton color="danger" :icon="mdiDelete" small
                                        @click="deleteAddress(address)" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <BaseIcon :path="mdiMapMarker" class="w-12 h-12 mx-auto mb-3 opacity-50" />
                        <p>Chưa có địa chỉ nào được thêm</p>
                    </div>
                </CardBox>
            </div>

            <!-- Vouchers Tab -->
            <div v-if="activeTab === 'vouchers'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium">Danh sách voucher</h3>
                    <BaseButton label="Gán voucher" color="info" @click="openAssignVoucherModal"
                        :icon="mdiTicketPercent" />
                </div>

                <!-- Vouchers list -->
                <div v-if="!safeUser.vouchers?.length" class="text-center py-8 text-gray-500">
                    Chưa có voucher nào được gán
                </div>
                <div v-else v-for="voucher in safeUser.vouchers" :key="voucher.id"
                    class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-4 border-l-4"
                    :class="voucher.is_active ? 'border-green-500' : 'border-gray-300 dark:border-gray-600'">

                    <!-- Voucher header -->
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-medium text-lg">{{ voucher.code }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ voucher.description || 'Không có mô tả' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-lg text-primary-600">
                                {{ voucher.formatted_discount }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Còn lại: {{ voucher.remaining_uses }} lần sử dụng
                            </p>
                        </div>
                    </div>

                    <!-- Voucher details -->
                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Đơn ti thiểu:
                                <span class="font-medium">{{ voucher.min_order_value_formatted }}</span>
                            </p>
                            <p class="text-gray-600 dark:text-gray-400">
                                Giảm tối đa:
                                <span class="font-medium">{{ voucher.max_discount_amount_formatted }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-600 dark:text-gray-400">
                                Bắt đầu:
                                <span class="font-medium">{{ voucher.start_date_formatted }}</span>
                            </p>
                            <p class="text-gray-600 dark:text-gray-400">
                                Kết thúc:
                                <span class="font-medium">{{ voucher.end_date_formatted }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-4 flex justify-end space-x-2">
                        <BaseButton label="Chi tiết" color="info" small @click="openVoucherDetailModal(voucher)" />
                        <BaseButton v-if="voucher.remaining_uses > 0" label="Trả lại voucher" color="danger" small
                            @click="returnVoucher(voucher.id)" />
                    </div>
                </div>

                <!-- Voucher Detail Modal -->
                <TransitionRoot appear :show="showVoucherDetailModal" as="template">
                    <Dialog as="div" @close="closeVoucherDetailModal" class="relative z-50">
                        <div class="fixed inset-0 overflow-y-auto">
                            <div class="flex min-h-full items-center justify-center p-4">
                                <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl 
                                    bg-white dark:bg-slate-900 p-6 shadow-xl transition-all">
                                    <DialogTitle as="h3" class="text-lg font-medium leading-6 
                                        text-gray-900 dark:text-white mb-4">
                                        Chi tiết Voucher
                                    </DialogTitle>

                                    <div v-if="selectedVoucher" class="space-y-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium dark:text-white">{{ selectedVoucher.code }}</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ selectedVoucher.description || 'Không có mô tả' }}
                                                </p>
                                            </div>
                                            <div :class="{
                                                'px-2 py-1 text-xs font-medium rounded-full': true,
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': selectedVoucher.status === 'active',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': selectedVoucher.status === 'inactive'
                                            }">
                                                {{ selectedVoucher.status === 'active' ? 'Đang kích hoạt' : 'Đã vô hiệu'
                                                }}
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Loại giảm giá</p>
                                                <p class="font-medium dark:text-white">
                                                    {{ selectedVoucher.discount_type === 'percentage' ? 'Phần trăm' :
                                                        'Số tiền cố định' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Giá trị giảm</p>
                                                <p class="font-medium dark:text-white">{{
                                                    selectedVoucher.formatted_discount }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Đơn tối thiểu</p>
                                                <p class="font-medium dark:text-white">{{
                                                    selectedVoucher.min_order_value_formatted }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Giảm tối đa</p>
                                                <p class="font-medium dark:text-white">{{
                                                    selectedVoucher.max_discount_amount_formatted }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Ngày bắt đầu</p>
                                                <p class="font-medium dark:text-white">{{
                                                    selectedVoucher.start_date_formatted }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Ngày kết thúc</p>
                                                <p class="font-medium dark:text-white">{{
                                                    selectedVoucher.end_date_formatted }}</p>
                                            </div>
                                        </div>

                                        <div class="pt-4 border-t dark:border-gray-700">
                                            <div class="flex justify-between space-x-3">
                                                <BaseButton type="button"
                                                    :label="selectedVoucher.status === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'"
                                                    :color="selectedVoucher.status === 'active' ? 'danger' : 'success'"
                                                    @click="toggleVoucherStatus(selectedVoucher.id)" />
                                                <BaseButton type="button" label="Đóng" color="white"
                                                    @click="closeVoucherDetailModal" />
                                            </div>
                                        </div>
                                    </div>
                                </DialogPanel>
                            </div>
                        </div>
                    </Dialog>
                </TransitionRoot>
            </div>

            <!-- Orders Tab -->
            <div v-if="activeTab === 'invoices'" class="space-y-6">
                <!-- Header -->
                <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-medium dark:text-white">Lịch sử đơn hàng</h3>
                </div>

                <!-- Orders List -->
                <div v-if="safeUser.orders?.length" class="space-y-4">
                    <div v-for="order in safeUser.orders" :key="order.id"
                        class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden">
                        <!-- Order Header -->
                        <div class="p-4 border-b dark:border-slate-700 flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Mã đơn hàng: #{{ order.id }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ formattedDate(order.created_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span :class="{
                                    'px-2 py-1 text-xs font-medium rounded-full': true,
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': order.status === 'pending',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.status === 'processing',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.status === 'completed',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': order.status === 'cancelled'
                                }">
                                    {{ formatOrderStatus(order.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="p-4 space-y-3">
                            <div v-for="item in order.order_items" :key="item.id"
                                class="flex justify-between items-center py-2">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <img v-if="item.product?.image_url" :src="item.product.image_url"
                                            :alt="item.product?.name" class="w-12 h-12 object-cover rounded-md">
                                        <div v-else
                                            class="w-12 h-12 bg-gray-200 dark:bg-slate-700 rounded-md flex items-center justify-center">
                                            <BaseIcon :path="mdiPackageVariant"
                                                class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium dark:text-white">
                                            {{ item.product?.name || item.service?.name || 'Sản phẩm đã xóa' }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ formatCurrency(item.price) }} x {{ item.quantity }}
                                        </p>
                                    </div>
                                </div>
                                <p class="font-medium dark:text-white">
                                    {{ formatCurrency(item.price * item.quantity) }}
                                </p>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="p-4 bg-gray-50 dark:bg-slate-800 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Tạm tính</span>
                                <span class="dark:text-white">{{ formatCurrency(order.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Giảm giá</span>
                                <span class="text-green-600 dark:text-green-400">
                                    -{{ formatCurrency(order.discount_amount) }}
                                </span>
                            </div>
                            <div class="flex justify-between font-medium pt-2 border-t dark:border-slate-700">
                                <span class="dark:text-white">Tổng cộng</span>
                                <span class="text-lg text-primary-600 dark:text-primary-400">
                                    {{ formatCurrency(order.total_amount - order.discount_amount) }}
                                </span>
                            </div>
                        </div>

                        <!-- Order Actions -->
                        <div v-if="order.status === 'pending'"
                            class="p-4 border-t dark:border-slate-700 flex justify-end space-x-3">
                            <BaseButton label="Hủy đơn" color="danger" @click="cancelOrder(order.id)"
                                :loading="order.loading" />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-8 text-center">
                    <BaseIcon :path="mdiReceipt" class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500" />
                    <h3 class="mt-4 text-lg font-medium dark:text-white">
                        Chưa có đơn hàng nào
                    </h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Khách hàng chưa thực hiện đơn hàng nào.
                    </p>
                </div>
            </div>

            <!-- Edit Modal -->
            <TransitionRoot appear :show="showEditModal" as="template">
                <Dialog as="div" @close="closeEditModal" class="relative z-50">
                    <!-- ... modal backdrop ... -->
                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                                enter-to="opacity-100 scale-100" leave="duration-200 ease-in"
                                leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                                <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl 
                                    bg-white dark:bg-slate-900 p-6 shadow-xl transition-all">
                                    <DialogTitle as="h3" class="text-lg font-medium leading-6 
                                        text-gray-900 dark:text-white mb-4">
                                        Chỉnh sửa thông tin khách hàng
                                    </DialogTitle>

                                    <form @submit.prevent="updateUser" class="space-y-4">
                                        <!-- Form fields với dark mode classes -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Họ và tên
                                                </label>
                                                <input v-model="editedUser.full_name" class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                        dark:bg-slate-800 dark:text-white shadow-sm 
                                                        focus:border-blue-500 focus:ring-blue-500" required>
                                            </div>
                                            <!-- Thêm dark mode classes tương tự cho các input khác -->
                                        </div>
                                    </form>
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>

            <!-- Address Modal -->
            <TransitionRoot appear :show="showAddressModal" as="template">
                <Dialog as="div" @close="closeAddressModal" class="relative z-50">
                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl 
                                bg-white dark:bg-slate-900 p-6 shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 
                                    text-gray-900 dark:text-white mb-4">
                                    {{ editingAddress ? 'Sửa địa chỉ' : 'Thêm địa chỉ mới' }}
                                </DialogTitle>

                                <form @submit.prevent="submitAddress" class="space-y-4">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Tỉnh/Thành phố *
                                            </label>
                                            <select v-model="addressForm.province" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500">
                                                <option value="">-- Chọn tỉnh/thành phố --</option>
                                                <option v-for="province in provinces" :key="province.code"
                                                    :value="province.code">
                                                    {{ province.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Quận/Huyện *
                                            </label>
                                            <select v-model="addressForm.district" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500">
                                                <option value="">-- Chọn quận/huyện --</option>
                                                <option v-for="district in districts" :key="district.code"
                                                    :value="district.code">
                                                    {{ district.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Phường/Xã *
                                            </label>
                                            <select v-model="addressForm.ward" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500">
                                                <option value="">-- Chọn phường/xã --</option>
                                                <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                                                    {{ ward.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Đa chỉ chi tiết *
                                            </label>
                                            <input v-model="addressForm.address" type="text" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500" placeholder="Số nhà, tên đường, phường/xã">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Loại địa chỉ
                                            </label>
                                            <select v-model="addressForm.address_type" class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500">
                                                <option value="home">Nhà riêng</option>
                                                <option value="work">Nơi làm việc</option>
                                                <option value="shipping">Địa chỉ giao hàng</option>
                                                <option value="others">Khác</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center">
                                                <input type="checkbox" v-model="addressForm.is_default" id="is_default"
                                                    class="rounded border-gray-300 dark:border-gray-600 
                                                        text-blue-600 focus:ring-blue-500">
                                                <label for="is_default"
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                    Đặt làm địa chỉ mặc định
                                                </label>
                                            </div>

                                            <div class="flex items-center">
                                                <input type="checkbox" v-model="addressForm.is_temporary"
                                                    id="is_temporary" class="rounded border-gray-300 dark:border-gray-600 
                                                        text-blue-600 focus:ring-blue-500">
                                                <label for="is_temporary"
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                    Địa chỉ tạm thời
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                                        <BaseButton type="button" label="Hủy" color="white"
                                            @click="closeAddressModal" />
                                        <BaseButton type="submit" :label="editingAddress ? 'Cập nhật' : 'Thêm mới'"
                                            color="info" />
                                    </div>
                                </form>
                            </DialogPanel>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>

            <!-- Assign Voucher Modal -->
            <TransitionRoot appear :show="showAssignVoucherModal" as="template">
                <Dialog as="div" @close="closeAssignVoucherModal" class="relative z-50">
                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl 
                                bg-white dark:bg-slate-900 p-6 shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 
                                    text-gray-900 dark:text-white mb-4">
                                    Gán voucher cho khách hàng
                                </DialogTitle>

                                <div v-if="!showCreateVoucherForm">
                                    <form @submit.prevent="assignVoucher" class="space-y-4">
                                        <!-- Existing voucher selection -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Chọn Voucher có sẵn
                                            </label>
                                            <select v-model="voucherForm.voucher_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                focus:ring-blue-500">
                                                <option value="">-- Chọn voucher --</option>
                                                <option v-for="voucher in availableVouchers" :key="voucher.id"
                                                    :value="voucher.id">
                                                    {{ voucher.code }} -
                                                    {{ voucher.discount_type === 'percentage' ?
                                                        `${voucher.discount_value}%` :
                                                        formatCurrency(voucher.discount_value) }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Số lần sử dụng
                                            </label>
                                            <input v-model.number="voucherForm.total_uses" type="number" min="1"
                                                required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                focus:ring-blue-500">
                                        </div>

                                        <div class="flex justify-between space-x-3 pt-4">
                                            <BaseButton type="button" label="Tạo voucher mới" color="success"
                                                @click="showCreateVoucherForm = true" />
                                            <div class="flex space-x-2">
                                                <BaseButton type="button" label="Hủy" color="white"
                                                    @click="closeAssignVoucherModal" />
                                                <BaseButton type="submit" label="Xác nhận" color="info"
                                                    :disabled="!voucherForm.voucher_id" />
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Create new voucher form -->
                                <div v-else>
                                    <form @submit.prevent="createAndAssignVoucher" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Mã voucher *
                                            </label>
                                            <input v-model="newVoucherForm.code" type="text" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Loại giảm giá *
                                            </label>
                                            <select v-model="newVoucherForm.discount_type" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                focus:ring-blue-500">
                                                <option value="percentage">Phần trăm</option>
                                                <option value="fixed">Số tiền cố định</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Giá trị giảm *
                                            </label>
                                            <input v-model.number="newVoucherForm.discount_value" type="number" required
                                                min="0" class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Giá trị đơn hàng tối thiểu
                                            </label>
                                            <input v-model.number="newVoucherForm.min_order_value" type="number" min="0"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Giảm giá tối đa
                                            </label>
                                            <input v-model.number="newVoucherForm.max_discount_amount" type="number"
                                                min="0" class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                focus:ring-blue-500">
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Ngày bắt đầu *
                                                </label>
                                                <input v-model="newVoucherForm.start_date" type="date" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500">
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Ngày kết thúc *
                                                </label>
                                                <input v-model="newVoucherForm.end_date" type="date" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500">
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center">
                                                <input type="checkbox" v-model="newVoucherForm.is_unlimited"
                                                    id="is_unlimited" class="rounded border-gray-300 dark:border-gray-600 
                                                    text-blue-600 focus:ring-blue-500">
                                                <label for="is_unlimited"
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                    Không giới hạn số lần sử dụng
                                                </label>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Số lần sử dụng cho mỗi người dùng *
                                            </label>
                                            <input v-model.number="newVoucherForm.uses_per_user" type="number" required
                                                min="1" class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                focus:ring-blue-500">
                                        </div>

                                        <div class="flex justify-between space-x-3 pt-4">
                                            <BaseButton type="button" label="Quay lại" color="white"
                                                @click="showCreateVoucherForm = false" />
                                            <BaseButton type="submit" label="Tạo và gán" color="success" />
                                        </div>
                                    </form>
                                </div>
                            </DialogPanel>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>

            <!-- Treatments Tab -->
            <div v-if="activeTab === 'service_combos'" class="space-y-6">
                <!-- Active Treatments -->
                <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b dark:border-slate-700">
                        <h3 class="text-lg font-medium dark:text-white">Liệu trình đang thực hiện</h3>
                    </div>

                    <div v-if="userServicePackages?.length" class="divide-y dark:divide-slate-700 p-4 ">
                        <div v-for="servicePackage in userServicePackages" :key="servicePackage.id">
                            <div class="flex justify-between items-start mb-4 ">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h4 class="font-medium dark:text-white">{{ servicePackage.service_name }}</h4>
                                        <span :class="{
                                            'px-2 py-0.5 text-xs font-medium rounded-full': true,
                                            [`bg-${servicePackage.package_type.color}-100 text-${servicePackage.package_type.color}-800`]: true,
                                            [`dark:bg-${servicePackage.package_type.color}-900 dark:text-${servicePackage.package_type.color}-200`]: true
                                        }">
                                            {{ servicePackage.package_type.name }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Hết hạn: {{ servicePackage.formatted_expiry_date || 'Không giới hạn' }}
                                    </p>
                                </div>
                                <BaseButton v-if="servicePackage.remaining_sessions > 0" color="info"
                                    label="Thêm buổi dịch vụ" :icon="mdiPlus"
                                    @click="openAddTreatmentModal(servicePackage)" />
                            </div>

                            <!-- Progress Bar - Di chuyển vào trong vòng lặp -->
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4">
                                <div class="bg-blue-600 h-2.5 rounded-full"
                                    :style="{ width: `${servicePackage.progress_percentage}%` }"
                                    :class="{ 'bg-green-600': servicePackage.progress_percentage >= 100 }">
                                </div>
                            </div>

                            <!-- Sessions Info -->
                            <div class="grid grid-cols-3 gap-4 text-sm mb-4">
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400">Tổng số buổi</p>
                                    <p class="font-medium dark:text-white">{{ servicePackage.total_sessions }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400">Đã sử dụng</p>
                                    <p class="font-medium dark:text-white">{{ servicePackage.used_sessions }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400">Còn lại</p>
                                    <p class="font-medium dark:text-white">{{ servicePackage.remaining_sessions }}</p>
                                </div>
                            </div>

                            <!-- Next Appointment -->
                            <div v-if="servicePackage.next_session_date"
                                class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <BaseIcon :path="mdiCalendarClock"
                                        class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-200">
                                            Buổi dịch vụ tiếp theo
                                        </p>
                                        <p class="text-sm text-blue-800 dark:text-blue-300">
                                            {{ servicePackage.next_session_date }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Next Appointment -->
                            <div v-if="servicePackage.next_appointment_details"
                                class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-100 dark:border-blue-800">
                                <div class="flex items-start space-x-3">
                                    <BaseIcon :path="mdiCalendarClock"
                                        class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-200">
                                            Lịch hẹn sắp tới
                                        </p>
                                        <div class="mt-1 space-y-1">
                                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                                {{ servicePackage.next_appointment_details.date }}
                                            </p>
                                            <div class="flex items-center text-sm text-blue-700 dark:text-blue-400">
                                                <BaseIcon :path="mdiClockOutline" class="w-4 h-4 mr-1" />
                                                {{ servicePackage.next_appointment_details.time.start }} -
                                                {{ servicePackage.next_appointment_details.time.end }}
                                            </div>
                                            <div v-if="servicePackage.next_appointment_details.staff"
                                                class="flex items-center text-sm text-blue-700 dark:text-blue-400">
                                                <BaseIcon :path="mdiAccount" class="w-4 h-4 mr-1" />
                                                Thực hiện bởi: {{
                                                    servicePackage.next_appointment_details.staff.full_name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Treatment History Section -->
                            <div v-if="servicePackage.treatment_sessions?.length" class="mt-4">
                                <div class="border-t dark:border-slate-700 pt-4">
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-3">
                                        Lịch sử dịch vụ
                                    </h5>
                                    <div class="space-y-3">
                                        <div v-for="session in servicePackage.treatment_sessions" :key="session.id"
                                            class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="text-sm text-gray-900 dark:text-white">
                                                        Buổi #{{ servicePackage.total_sessions -
                                                            servicePackage.treatment_sessions.indexOf(session) }}
                                                    </div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        <div class="flex items-center space-x-2">
                                                            <BaseIcon :path="mdiCalendar" class="w-4 h-4" />
                                                            <span>{{ formatDateTime(session.start_time) }}</span>
                                                        </div>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <BaseIcon :path="mdiAccount" class="w-4 h-4" />
                                                            <span>Thực hiện: {{ session.staff?.full_name || 'N/A'
                                                                }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-if="session.result"
                                                    class="text-sm text-gray-600 dark:text-gray-400 text-right">
                                                    <span class="font-medium">Kết quả:</span> {{ session.result }}
                                                </div>
                                            </div>
                                            <div v-if="session.notes"
                                                class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Ghi chú:</span> {{ session.notes }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-6 text-center text-gray-600 dark:text-gray-400">
                        Chưa có liệu trình nào đang thực hiện
                    </div>
                </div>
            </div>

            <!-- Add Treatment Modal -->
            <TransitionRoot appear :show="showAddTreatmentModal" as="template">
                <Dialog as="div" @close="closeAddTreatmentModal" class="relative z-50">
                    <div class="fixed inset-0 bg-black/30" />

                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl 
                                bg-white dark:bg-slate-900 p-6 shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 
                                    text-gray-900 dark:text-white mb-4">
                                    Thêm buổi dịch vụ mới
                                </DialogTitle>

                                <form @submit.prevent="submitTreatmentSession" class="space-y-4">
                                    <!-- Staff selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nhân viên thực hiện *
                                        </label>
                                        <select v-model="treatmentForm.staff_user_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                            dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                            focus:ring-blue-500">
                                            <option value="">-- Chọn nhân viên --</option>
                                            <option v-for="staff in staffList" :key="staff.id" :value="staff.id">
                                                {{ staff.full_name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Start time -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Thời gian bắt đầu *
                                        </label>
                                        <input v-model="treatmentForm.start_time" type="datetime-local" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                            dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                            focus:ring-blue-500">
                                    </div>

                                    <!-- End time -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Thời gian kết thúc *
                                        </label>
                                        <input v-model="treatmentForm.end_time" type="datetime-local" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                            dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                            focus:ring-blue-500">
                                    </div>

                                    <!-- Result -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Kết quả
                                        </label>
                                        <textarea v-model="treatmentForm.result" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                            dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                            focus:ring-blue-500"></textarea>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Ghi chú
                                        </label>
                                        <textarea v-model="treatmentForm.notes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                            dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                            focus:ring-blue-500"></textarea>
                                    </div>

                                    <div class="flex justify-end space-x-3 pt-4">
                                        <BaseButton type="button" label="Hủy" color="white"
                                            @click="closeAddTreatmentModal" />
                                        <BaseButton type="submit" label="Thêm" color="info" />
                                    </div>
                                </form>
                            </DialogPanel>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
/* Custom scrollbar cho dark mode */
.dark ::-webkit-scrollbar-track {
    background: #1e293b;
}

.dark ::-webkit-scrollbar-thumb {
    background: #475569;
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Add these styles */
.avatar-upload-button {
    transition: opacity 0.2s;
}

.avatar-upload-button:hover {
    opacity: 0.9;
}
</style>