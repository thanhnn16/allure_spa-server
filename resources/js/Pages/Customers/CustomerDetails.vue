<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiAccount, mdiPackageVariant, mdiReceipt, mdiGift, mdiClose, mdiDelete, mdiAlert } from '@mdi/js'
import { Head } from '@inertiajs/vue3'
import NotificationBar from '@/Components/NotificationBar.vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3';
const page = usePage();
axios.defaults.headers.common['X-CSRF-TOKEN'] = page.props.csrf_token;

const props = defineProps({
    user: Object,
    upcomingBirthdays: Number
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
        // Chuyển hướng về trang danh sách người dùng
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
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Chi tiết khách hàng" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiAccount" :title="user.full_name" main>
                <div class="flex justify-end">
                    <BaseButton label="Chỉnh sửa" color="info" @click="openEditModal" class="mr-2" />
                    <BaseButton label="Xóa" color="danger" @click="openDeleteModal" :icon="mdiDelete" />
                </div>
            </SectionTitleLineWithButton>

            <NotificationBar v-if="notification" :color="notification.type" :icon="mdiAlert">
                {{ notification.message }}
            </NotificationBar>

            <CardBox class="mb-6">
                <div class="flex space-x-4">
                    <BaseButton :icon="mdiAccount" :label="'Thông tin cá nhân'" @click="activeTab = 'personal'"
                        :color="activeTab === 'personal' ? 'info' : 'white'" />
                    <BaseButton :icon="mdiPackageVariant" :label="'Liệu trình'" @click="activeTab = 'treatments'"
                        :color="activeTab === 'treatments' ? 'info' : 'white'" />
                    <BaseButton :icon="mdiReceipt" :label="'Đơn hàng'" @click="activeTab = 'invoices'"
                        :color="activeTab === 'invoices' ? 'info' : 'white'" />
                    <BaseButton :icon="mdiGift" :label="'Vouchers'" @click="activeTab = 'vouchers'"
                        :color="activeTab === 'vouchers' ? 'info' : 'white'" />
                </div>
            </CardBox>

            <CardBox v-if="activeTab === 'personal'" class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Thông tin cá nhân</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p><strong>Họ và tên:</strong> {{ user.full_name }}</p>
                        <p><strong>Số điện thoại:</strong> {{ user.phone_number }}</p>
                        <p><strong>Email:</strong> {{ user.email }}</p>
                        <p><strong>Giới tính:</strong> {{ formatGender }}</p>
                        <p><strong>Trạng thái:</strong> {{ user.deleted_at ? 'Đã bị xóa' : 'Đang hoạt động' }}</p>
                    </div>
                    <div>
                        <p><strong>Ngày sinh:</strong> {{ formattedDate(user.date_of_birth) }}</p>
                        <p><strong>Điểm tích lũy:</strong> {{ user.point }}</p>
                        <p><strong>Số lần mua hàng:</strong> {{ user.purchase_count }}</p>
                        <p><strong>Ghi chú:</strong> {{ user.note }}</p>
                        <p><strong>Ngày tạo:</strong> {{ formattedDate(user.created_at) }}</p>
                        <p><strong>Ngày chỉnh sửa:</strong> {{ formattedDate(user.updated_at) }}</p>
                    </div>
                </div>
            </CardBox>

            <CardBox v-if="activeTab === 'personal'" class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Địa chỉ</h3>
                <div v-if="user.addresses && user.addresses.length > 0">
                    <div v-for="address in user.addresses" :key="address.id" class="mb-2">
                        <p><strong>{{ address.address_type }}:</strong> {{ address.address }}</p>
                    </div>
                </div>
                <p v-else>Không có dữ liệu địa chỉ.</p>
            </CardBox>

            <CardBox v-if="activeTab === 'treatments'" class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Liệu trình đang sử dụng</h3>
                <div v-if="user.treatment_packages && user.treatment_packages.length > 0">
                    <div v-for="treatmentPackage in user.treatment_packages" :key="treatmentPackage.id" class="mb-4">
                        <p><strong>Tên liệu trình:</strong> {{ treatmentPackage.treatment_combo.treatment.treatment_name
                            }}
                        </p>
                        <p><strong>Loại combo:</strong> {{ treatmentPackage.treatment_combo.combo_type }}</p>
                        <p><strong>Số buổi còn lại:</strong> {{ treatmentPackage.remaining_sessions }}/{{
                            treatmentPackage.total_sessions }}</p>
                        <p><strong>Ngày hết hạn:</strong> {{ new Date(treatmentPackage.expiry_date).toLocaleDateString()
                            }}
                        </p>
                    </div>
                </div>
                <p v-else>Không có dữ liệu liệu trình.</p>
            </CardBox>

            <CardBox v-if="activeTab === 'invoices'" class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Đơn hàng gần đây</h3>
                <div v-if="user.invoices && user.invoices.length > 0">
                    <div v-for="invoice in user.invoices" :key="invoice.id" class="mb-4">
                        <p><strong>Mã đơn hàng:</strong> {{ invoice.invoice_number }}</p>
                        <p><strong>Ngày tạo:</strong> {{ new Date(invoice.created_at).toLocaleDateString() }}</p>
                        <p><strong>Tổng tiền:</strong> {{ invoice.total_amount }}</p>
                        <p><strong>Trạng thái thanh toán:</strong> {{ invoice.payment_status }}</p>
                    </div>
                </div>
                <p v-else>Không có dữ liệu đơn hàng.</p>
            </CardBox>

            <CardBox v-if="activeTab === 'vouchers'" class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Vouchers</h3>
                <div v-if="user.vouchers && user.vouchers.length > 0">
                    <div v-for="voucher in user.vouchers" :key="voucher.id" class="mb-4">
                        <p><strong>Mã voucher:</strong> {{ voucher.code }}</p>
                        <p><strong>Mô tả:</strong> {{ voucher.description }}</p>
                        <p><strong>Giá trị giảm:</strong> {{ voucher.discount_value }}</p>
                        <p><strong>Trạng thái:</strong> {{ voucher.pivot.is_used ? 'Đã sử dụng' : 'Chưa sử dụng' }}</p>
                    </div>
                </div>
                <p v-else>Không có dữ liệu voucher.</p>
            </CardBox>

            <!-- Edit Modal -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                role="dialog" aria-modal="true" id="editModal"
                @click="closeModalOnOutsideClick($event, $event.currentTarget)">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-semibold mb-4">Chỉnh sửa thông tin khách hàng</h3>
                            <form @submit.prevent="updateUser">
                                <div class="mb-4">
                                    <label class="block mb-2">Họ và tên</label>
                                    <input v-model="editedUser.full_name" class="w-full border rounded p-2" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block mb-2">Số điện thoại</label>
                                    <input v-model="editedUser.phone_number" class="w-full border rounded p-2" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block mb-2">Email</label>
                                    <input v-model="editedUser.email" type="email" class="w-full border rounded p-2">
                                </div>
                                <div class="mb-4">
                                    <label class="block mb-2">Giới tính</label>
                                    <select v-model="editedUser.gender" class="w-full border rounded p-2" required>
                                        <option value="male">Nam</option>
                                        <option value="female">Nữ</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block mb-2">Ngày sinh</label>
                                    <input v-model="editedUser.date_of_birth" type="date"
                                        class="w-full border rounded p-2">
                                </div>
                                <div class="mb-4">
                                    <label class="block mb-2">Ghi chú</label>
                                    <textarea v-model="editedUser.note" class="w-full border rounded p-2"></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <BaseButton type="submit" label="Lưu" color="info" class="mr-2" />
                                    <BaseButton label="Hủy" color="danger" @click="closeEditModal" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                role="dialog" aria-modal="true" id="deleteModal"
                @click="closeModalOnOutsideClick($event, $event.currentTarget)">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-semibold mb-4">Xác nhận xóa khách hàng</h3>
                            <p>Bạn có chắc chắn muốn xóa khách hàng này không?</p>
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <BaseButton label="Xóa" color="danger" @click="deleteUser"
                                    class="w-full sm:ml-3 sm:w-auto" />
                                <BaseButton label="Hủy" color="info" @click="closeDeleteModal"
                                    class="mt-3 w-full sm:mt-0 sm:w-auto" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
/* Không cần style scoped nữa vì chúng ta đã sử dụng Tailwind CSS classes */
</style>
