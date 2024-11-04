<script setup>
import { ref, reactive, computed, defineComponent, h } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import {
    mdiAccount, mdiPackageVariant, mdiReceipt, mdiGift, mdiClose, mdiDelete,
    mdiAlert, mdiPencil, mdiTicketPercent, mdiCamera
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

const props = defineProps({
    user: Object,
    upcomingBirthdays: Number
})

const tabs = [
    { id: 'personal', label: 'Thông tin cá nhân', icon: mdiAccount },
    { id: 'treatments', label: 'Liệu trình', icon: mdiPackageVariant },
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
const addressForm = reactive({
    province: '',
    district: '',
    address: '',
    address_type: 'home',
    is_default: false,
    is_temporary: false
})

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

const closeModalOnOutsideClick = (event, modalRef) => {
    if (event.target === modalRef) {
        if (modalRef.id === 'editModal') {
            closeEditModal()
        } else if (modalRef.id === 'deleteModal') {
            closeDeleteModal()
        }
    }
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

const deleteUser = async () => {
    try {
        const response = await axios.delete(`/users/${props.user.id}`)
        notification.value = { type: 'success', message: response.data.message }
        window.location.href = '/users'
    } catch (error) {
        notification.value = { type: 'danger', message: error.response?.data?.message || 'Có lỗi xảy ra khi xóa người dùng' }
    }
    closeDeleteModal()
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

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('vi-VN');
};

const formatVoucherType = (type) => {
    switch (type) {
        case 'percentage':
            return 'Phần trăm';
        case 'fixed':
            return 'Giá trị cố định';
        default:
            return 'Không xác định';
    }
};

const formatVoucherValue = (type, value) => {
    if (type === 'percentage') {
        return `${value}%`;
    } else if (type === 'fixed') {
        return formatCurrency(value);
    }
    return value;
};

const isVoucherActive = (voucher) => {
    const now = new Date()
    const endDate = new Date(voucher.end_date)
    return endDate > now && voucher.pivot.remaining_uses > 0
}

const formatInvoiceStatus = (status) => {
    switch (status) {
        case 'completed':
            return 'Đã hoàn thành';
        case 'pending':
            return 'Đang xử lý';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return 'Không xác định';
    }
};

const formatPaymentMethod = (method) => {
    switch (method) {
        case 'cash':
            return 'Tiền mặt';
        case 'card':
            return 'Thẻ';
        case 'transfer':
            return 'Chuyển khoản';
        default:
            return 'Khác';
    }
};

const isTreatmentActive = (treatment) => {
    return treatment.completed_sessions < treatment.total_sessions;
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
        address: '',
        address_type: 'home',
        is_default: false,
        is_temporary: false
    })
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

const handleAvatarError = (event) => {
    const target = event.target
    target.style.display = 'none'
    target.parentElement.querySelector('div').style.display = 'flex'
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
</script>
<template>
    <LayoutAuthenticated>

        <Head title="Chi tiết khách hàng" />
        <SectionMain>
            <!-- Header Section -->
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <!-- Avatar section -->
                        <div class="relative">
                            <UserAvatar 
                                :fullName="safeUser.full_name" 
                                :avatarUrl="safeUser.avatar_url"
                                size="lg" />
                            
                            <!-- Upload button overlay -->
                            <label class="absolute bottom-0 right-0 bg-blue-500 dark:bg-blue-600 
                                rounded-full p-1.5 cursor-pointer hover:bg-blue-600 dark:hover:bg-blue-700 
                                transition-colors shadow-md">
                                <input type="file" @change="handleAvatarUpload" accept="image/*" class="hidden">
                                <BaseIcon :path="mdiCamera" class="w-4 h-4 text-white" />
                            </label>
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold dark:text-white">{{ safeUser.full_name }}</h1>
                            <p class="text-gray-600 dark:text-gray-400">{{ safeUser.phone_number }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <BaseButton label="Chỉnh sửa" color="info" @click="openEditModal" :icon="mdiPencil"
                            class="shadow-md hover:shadow-lg transition-shadow" />
                        <BaseButton label="Xóa" color="danger" @click="openDeleteModal" :icon="mdiDelete"
                            class="shadow-md hover:shadow-lg transition-shadow" />
                    </div>
                </div>
            </div>

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
            <div v-if="activeTab === 'personal'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <CardBox class="!p-6 dark:bg-slate-900">
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b dark:border-slate-700 pb-2 dark:text-white">
                            Thông tin cơ bản
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <InfoItem v-for="(item, index) in basicInfo" :key="index" :label="item.label"
                                :value="item.value" class="dark:text-gray-300" />
                        </div>
                    </div>
                </CardBox>

                <CardBox class="!p-6 dark:bg-slate-900">
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b dark:border-slate-700 pb-2 dark:text-white">
                            Thông tin bổ sung
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <InfoItem v-for="(item, index) in additionalInfo" :key="index" :label="item.label"
                                :value="item.value" class="dark:text-gray-300" />
                        </div>
                        <div class="mt-4">
                            <label class="font-medium dark:text-white">Ghi chú:</label>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">
                                {{ safeUser.note || 'Không có ghi chú' }}
                            </p>
                        </div>
                    </div>
                </CardBox>

                <CardBox class="!p-6 dark:bg-slate-900">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center border-b dark:border-slate-700 pb-2">
                            <h3 class="text-lg font-semibold dark:text-white">Địa chỉ</h3>
                            <BaseButton label="Thêm địa chỉ" color="info" @click="openAddAddressModal"
                                class="shadow-md hover:shadow-lg transition-shadow" />
                        </div>

                        <div v-if="safeUser.addresses?.length" class="space-y-4">
                            <div v-for="address in safeUser.addresses" :key="address.id"
                                class="p-4 border rounded-lg dark:border-slate-700 relative"
                                :class="{ 'border-blue-500 dark:border-blue-400': address.is_default }">
                                <div class="flex justify-between">
                                    <div class="space-y-1">
                                        <p class="font-medium dark:text-white">{{ address.address }}</p>
                                        <p class="text-gray-600 dark:text-gray-400">
                                            {{ address.district }}, {{ address.province }}
                                        </p>
                                        <div class="flex space-x-2 text-sm">
                                            <span v-if="address.is_default" class="text-blue-600 dark:text-blue-400">
                                                Địa chỉ mặc định
                                            </span>
                                            <span v-if="address.is_temporary"
                                                class="text-yellow-600 dark:text-yellow-400">
                                                Địa chỉ tạm thời
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ formatAddressType(address.address_type) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <BaseButton color="info" :icon="mdiPencil" small
                                            @click="editAddress(address)" />
                                        <BaseButton color="danger" :icon="mdiDelete" small
                                            @click="deleteAddress(address)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                            Chưa có địa chỉ nào được thêm
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Vouchers Tab -->
            <div v-if="activeTab === 'vouchers'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="voucher in safeUser.vouchers" :key="voucher.id"
                    class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border-l-4"
                    :class="isVoucherActive(voucher) ? 'border-green-500' : 'border-gray-300 dark:border-gray-600'">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center space-x-2">
                            <BaseIcon :path="mdiTicketPercent" class="w-6 h-6 text-blue-500 dark:text-blue-400" />
                            <h4 class="text-lg font-semibold dark:text-white">{{ voucher.code }}</h4>
                        </div>
                        <span class="px-2 py-1 text-sm rounded-full" :class="isVoucherActive(voucher)
                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                            : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'">
                            {{ isVoucherActive(voucher) ? 'Đang hoạt động' : 'Hết hạn' }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ formatVoucherValue(voucher.type, voucher.value) }}
                        </p>
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span>Còn lại: {{ voucher.pivot.remaining_uses }}/{{ voucher.pivot.total_uses }}</span>
                            <span>HSD: {{ formatDate(voucher.end_date) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                            <div class="bg-blue-500 dark:bg-blue-400 h-2 rounded-full"
                                :style="`width: ${(voucher.pivot.remaining_uses / voucher.pivot.total_uses) * 100}%`">
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!safeUser.vouchers?.length"
                    class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                    Không có voucher nào
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
                                            <input v-model="addressForm.province" type="text" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500" placeholder="Nhập tỉnh/thành phố">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Quận/Huyện *
                                            </label>
                                            <input v-model="addressForm.district" type="text" required class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                                    focus:ring-blue-500" placeholder="Nhập quận/huyện">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Địa chỉ chi tiết *
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