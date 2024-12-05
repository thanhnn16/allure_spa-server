<script setup>
import { ref, onMounted, watch } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { mdiSend, mdiDelete, mdiPlus } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import NotificationList from './Partials/NotificationList.vue'
import axios from 'axios'
import UserGroups from './Partials/UserGroups.vue'

const form = useForm({
    title: '',
    content: '',
    type: 'system',
    target_users: 'all',
    user_ids: [],
    group_id: null,
    translations: {
        vi: {
            title: '',
            content: ''
        },
        ja: {
            title: '',
            content: ''
        }
    }
})

const types = [
    { value: 'system', label: 'Thông báo hệ thống' },
    { value: 'promotion', label: 'Khuyến mãi' },
    { value: 'appointment', label: 'Lịch hẹn' },
    { value: 'order', label: 'Đơn hàng' }
]

const targetOptions = [
    { value: 'all', label: 'Tất cả người dùng' },
    { value: 'specific', label: 'Người dùng cụ thể' },
    { value: 'group', label: 'Nhóm người dùng' }
]

const users = ref([])
const selectedUsers = ref([])
const loading = ref(false)
const showSuccessMessage = ref(false)
const errorMessage = ref('')
const groups = ref([])
const selectedGroup = ref(null)

const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/users')
        users.value = response.data.data
    } catch (error) {
        console.error('Lỗi khi tải danh sách người dùng:', error)
    }
}

const fetchGroups = async () => {
    try {
        const response = await axios.get('/api/user-groups')
        groups.value = response.data
    } catch (error) {
        console.error('Lỗi khi tải nhóm:', error)
    }
}

const handleTargetChange = () => {
    // Reset các giá trị khi đổi đối tượng nhận
    form.user_ids = []
    form.group_id = null
    selectedUsers.value = []
    selectedGroup.value = null
    
    // Tải dữ liệu cần thiết dựa trên lựa chọn
    if (form.target_users === 'specific') {
        fetchUsers()
    } else if (form.target_users === 'group') {
        fetchGroups()
    }
}

// Thay đổi watch cho selectedUsers
watch(selectedUsers, (newValue) => {
    if (Array.isArray(newValue)) {
        form.user_ids = newValue
    }
}, { deep: true })

// Thay đổi watch cho selectedGroup 
watch(selectedGroup, (newValue) => {
    form.group_id = newValue
}, { deep: true })

const submit = async () => {
    loading.value = true
    errorMessage.value = ''
    
    try {
        // Chuẩn bị dữ liệu gửi đi
        const formData = {
            ...form.data(),
            user_ids: form.target_users === 'specific' ? form.user_ids : [],
            group_id: form.target_users === 'group' ? form.group_id : null
        }

        // Validate dữ liệu trước khi gửi
        if (form.target_users === 'specific' && (!formData.user_ids || formData.user_ids.length === 0)) {
            throw new Error('Vui lòng chọn ít nhất một người dùng')
        }

        if (form.target_users === 'group' && !formData.group_id) {
            throw new Error('Vui lòng chọn một nhóm người dùng')
        }

        // Gửi request tạo thông báo
        await axios.post('/api/notifications/send', formData)
        
        // Reset form và hiển thị thông báo thành công
        form.reset()
        selectedUsers.value = []
        selectedGroup.value = null
        showSuccessMessage.value = true
        setTimeout(() => {
            showSuccessMessage.value = false
        }, 3000)
    } catch (error) {
        errorMessage.value = error.response?.data?.message || error.message || 'Có lỗi xảy ra khi gửi thông báo'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchUsers()
    fetchGroups()
})
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Quản lý thông báo" />
        <SectionMain>
            <CardBox class="mb-6" has-table>
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Form gửi thông báo -->
                    <div class="lg:w-1/2">
                        <h2 class="text-2xl font-bold mb-6">Gửi thông báo mới</h2>
                        
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Loại thông báo -->
                            <FormField label="Loại thông báo" help="Chọn loại thông báo phù hợp">
                                <FormControl v-model="form.type" :options="types" type="select" />
                            </FormField>

                            <!-- Đối tượng nhận -->
                            <FormField label="Đối tượng nhận" help="Chọn người nhận thông báo">
                                <FormControl 
                                    v-model="form.target_users" 
                                    :options="targetOptions" 
                                    type="select"
                                    @update:modelValue="handleTargetChange"
                                />
                            </FormField>

                            <!-- Chọn người dùng cụ thể -->
                            <div v-if="form.target_users === 'specific'" class="border p-4 rounded-lg">
                                <FormField label="Chọn người dùng" help="Có thể chọn nhiều người dùng">
                                    <FormControl
                                        v-model="selectedUsers"
                                        :options="users.map(u => ({ value: u.id, label: `${u.full_name} (${u.phone_number})` }))"
                                        type="multiselect"
                                        placeholder="Chọn người dùng..."
                                        required
                                    />
                                </FormField>
                                <div v-if="selectedUsers.length > 0" class="mt-2 text-sm text-gray-600">
                                    Đã chọn {{ selectedUsers.length }} người dùng
                                </div>
                            </div>

                            <!-- Chọn nhóm -->
                            <div v-if="form.target_users === 'group'" class="border p-4 rounded-lg">
                                <FormField label="Chọn nhóm" help="Chọn nhóm người dùng để gửi thông báo">
                                    <FormControl
                                        v-model="selectedGroup"
                                        :options="groups.map(g => ({ 
                                            value: g.id, 
                                            label: `${g.name} (${g.user_count || 0} thành viên)` 
                                        }))"
                                        type="select"
                                        placeholder="Chọn nhóm..."
                                        required
                                    />
                                </FormField>
                                <div v-if="selectedGroup" class="mt-2 text-sm text-gray-600">
                                    {{ groups.find(g => g.id === selectedGroup)?.description }}
                                </div>
                            </div>

                            <!-- Tiếng Anh -->
                            <div class="border p-4 rounded-lg space-y-4">
                                <h3 class="font-semibold">Tiếng Anh</h3>
                                <FormField label="Tiêu đề">
                                    <FormControl v-model="form.title" type="text" required />
                                </FormField>
                                <FormField label="Nội dung">
                                    <FormControl v-model="form.content" type="textarea" required />
                                </FormField>
                            </div>

                            <!-- Tiếng Việt -->
                            <div class="border p-4 rounded-lg space-y-4">
                                <h3 class="font-semibold">Tiếng Việt</h3>
                                <FormField label="Tiêu đề">
                                    <FormControl v-model="form.translations.vi.title" type="text" required />
                                </FormField>
                                <FormField label="Nội dung">
                                    <FormControl v-model="form.translations.vi.content" type="textarea" required />
                                </FormField>
                            </div>

                            <!-- Tiếng Nhật -->
                            <div class="border p-4 rounded-lg space-y-4">
                                <h3 class="font-semibold">Tiếng Nhật</h3>
                                <FormField label="Tiêu đề">
                                    <FormControl v-model="form.translations.ja.title" type="text" required />
                                </FormField>
                                <FormField label="Nội dung">
                                    <FormControl v-model="form.translations.ja.content" type="textarea" required />
                                </FormField>
                            </div>

                            <!-- Thông báo -->
                            <div v-if="showSuccessMessage" class="bg-green-100 text-green-700 p-4 rounded-lg">
                                Gửi thông báo thành công!
                            </div>
                            <div v-if="errorMessage" class="bg-red-100 text-red-700 p-4 rounded-lg">
                                {{ errorMessage }}
                            </div>

                            <!-- Nút gửi -->
                            <BaseButtons>
                                <BaseButton
                                    type="submit"
                                    color="info"
                                    :icon="mdiSend"
                                    :loading="loading"
                                    label="Gửi thông báo"
                                />
                            </BaseButtons>
                        </form>
                    </div>

                    <!-- Danh sách thông báo đã gửi -->
                    <div class="lg:w-1/2">
                        <NotificationList />
                    </div>
                </div>
            </CardBox>

            <!-- Thêm component UserGroups -->
            <CardBox class="mb-6">
                <UserGroups />
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
