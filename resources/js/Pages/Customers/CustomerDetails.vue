<script setup>
import { ref, reactive, computed, defineComponent, h } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { mdiAccount, mdiPackageVariant, mdiReceipt, mdiGift, mdiClose, mdiDelete, 
         mdiAlert, mdiPencil, mdiTicketPercent } from '@mdi/js'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { Head } from '@inertiajs/vue3'
import NotificationBar from '@/Components/NotificationBar.vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

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
</script>
<template>
    <LayoutAuthenticated>
        <Head title="Chi tiết khách hàng" />
        <SectionMain>
            <!-- Header Section -->
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <BaseIcon :path="mdiAccount" class="w-8 h-8 text-blue-500 dark:text-blue-400" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold dark:text-white">{{ safeUser.full_name }}</h1>
                            <p class="text-gray-600 dark:text-gray-400">{{ safeUser.phone_number }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <BaseButton
                            label="Chỉnh sửa"
                            color="info"
                            @click="openEditModal"
                            :icon="mdiPencil"
                            class="shadow-md hover:shadow-lg transition-shadow"
                        />
                        <BaseButton
                            label="Xóa"
                            color="danger"
                            @click="openDeleteModal"
                            :icon="mdiDelete"
                            class="shadow-md hover:shadow-lg transition-shadow"
                        />
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
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="px-4 py-2 rounded-md transition-all duration-200"
                        :class="[
                            activeTab === tab.id
                                ? 'bg-blue-500 text-white shadow-md'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                        ]"
                    >
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
                            <InfoItem 
                                v-for="(item, index) in basicInfo" 
                                :key="index"
                                :label="item.label" 
                                :value="item.value"
                                class="dark:text-gray-300"
                            />
                        </div>
                    </div>
                </CardBox>

                <CardBox class="!p-6 dark:bg-slate-900">
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b dark:border-slate-700 pb-2 dark:text-white">
                            Thông tin bổ sung
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <InfoItem 
                                v-for="(item, index) in additionalInfo" 
                                :key="index"
                                :label="item.label" 
                                :value="item.value"
                                class="dark:text-gray-300"
                            />
                        </div>
                        <div class="mt-4">
                            <label class="font-medium dark:text-white">Ghi chú:</label>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">
                                {{ safeUser.note || 'Không có ghi chú' }}
                            </p>
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Vouchers Tab -->
            <div v-if="activeTab === 'vouchers'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="voucher in safeUser.vouchers" 
                    :key="voucher.id" 
                    class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border-l-4"
                    :class="isVoucherActive(voucher) ? 'border-green-500' : 'border-gray-300 dark:border-gray-600'"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center space-x-2">
                            <BaseIcon :path="mdiTicketPercent" class="w-6 h-6 text-blue-500 dark:text-blue-400" />
                            <h4 class="text-lg font-semibold dark:text-white">{{ voucher.code }}</h4>
                        </div>
                        <span class="px-2 py-1 text-sm rounded-full"
                            :class="isVoucherActive(voucher) 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'"
                        >
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
                                :style="`width: ${(voucher.pivot.remaining_uses / voucher.pivot.total_uses) * 100}%`"
                            ></div>
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
                            <TransitionChild
                                as="template"
                                enter="duration-300 ease-out"
                                enter-from="opacity-0 scale-95"
                                enter-to="opacity-100 scale-100"
                                leave="duration-200 ease-in"
                                leave-from="opacity-100 scale-100"
                                leave-to="opacity-0 scale-95"
                            >
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
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Họ và tên
                                                </label>
                                                <input v-model="editedUser.full_name" 
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                                        dark:bg-slate-800 dark:text-white shadow-sm 
                                                        focus:border-blue-500 focus:ring-blue-500" 
                                                    required
                                                >
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
</style>