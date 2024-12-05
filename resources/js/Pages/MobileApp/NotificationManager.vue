<script setup>
import { ref, onMounted, watch } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { mdiSend, mdiDelete, mdiPlus, mdiBullhorn, mdiHistory, mdiAccountGroup, mdiClose } from '@mdi/js'
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
const searchQuery = ref('')
const filteredUsers = ref([])
const loading = ref(false)
const showSuccessMessage = ref(false)
const errorMessage = ref('')
const groups = ref([])
const selectedGroup = ref(null)
const activeTab = ref('campaign')
const showForm = ref(false)

const computeFilteredUsers = () => {
    if (!searchQuery.value) {
        filteredUsers.value = users.value
        return
    }
    
    const query = searchQuery.value.toLowerCase()
    filteredUsers.value = users.value.filter(user => 
        user.full_name.toLowerCase().includes(query) ||
        user.phone_number.includes(query)
    )
}

watch(searchQuery, () => {
    computeFilteredUsers()
})

const fetchUsers = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/users')
        console.log('Users fetched:', response.data)
        if (response.data && Array.isArray(response.data.data)) {
            users.value = response.data.data
            filteredUsers.value = [...users.value]
            console.log('Users array:', users.value)
            console.log('FilteredUsers array:', filteredUsers.value)
        } else {
            console.error('Invalid response format:', response.data)
        }
    } catch (error) {
        console.error('Lỗi khi tải danh sách người dùng:', error)
        errorMessage.value = 'Không thể tải danh sách người dùng'
    } finally {
        loading.value = false
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
    console.log('handleTargetChange called, new target:', form.target_users)
    
    // Reset các giá trị
    form.user_ids = []
    form.group_id = null
    selectedUsers.value = []
    selectedGroup.value = null
    searchQuery.value = ''
    
    // Fetch data based on target
    if (form.target_users === 'specific') {
        console.log('Fetching users...')
        fetchUsers()
    } else if (form.target_users === 'group') {
        console.log('Fetching groups...')
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

        // Thực hiện hành đng bổ sung khi gửi ti người dùng cụ thể
        if (form.target_users === 'specific') {
            console.log('Đã gửi thông báo tới người dùng cụ thể:', formData.user_ids)
            // Thêm các xử lý khác tại đây nếu cần
        }
        
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

// Thêm watch cho form.target_users
watch(() => form.target_users, (newValue) => {
    console.log('Target users changed:', newValue)
}, { immediate: true })

// Thêm watch cho users
watch(users, (newValue) => {
    console.log('Users updated:', newValue)
    console.log('Current filteredUsers:', filteredUsers.value)
}, { deep: true })

// Thêm watch cho filteredUsers
watch(filteredUsers, (newValue) => {
    console.log('FilteredUsers updated:', newValue)
}, { deep: true })

const toggleForm = () => {
    showForm.value = !showForm.value
    if (!showForm.value) {
        form.reset()
        selectedUsers.value = []
        selectedGroup.value = null
    }
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Quản lý thông báo" />
        <SectionMain>
            <!-- Tab Navigation -->
            <div class="mb-6">
                <BaseButtons>
                    <BaseButton
                        :color="activeTab === 'campaign' ? 'info' : 'light'"
                        @click="activeTab = 'campaign'"
                        label="Chiến dịch thông báo"
                        :icon="mdiBullhorn"
                        class="transition-colors duration-300"
                    />
                    <BaseButton
                        :color="activeTab === 'history' ? 'info' : 'light'"
                        @click="activeTab = 'history'"
                        label="Lịch sử thông báo"
                        :icon="mdiHistory"
                        class="transition-colors duration-300"
                    />
                    <BaseButton
                        :color="activeTab === 'groups' ? 'info' : 'light'"
                        @click="activeTab = 'groups'"
                        label="Quản lý nhóm"
                        :icon="mdiAccountGroup"
                        class="transition-colors duration-300"
                    />
                </BaseButtons>
            </div>

            <!-- Campaign Tab -->
            <div v-if="activeTab === 'campaign'" class="transition-opacity duration-500">
                <CardBox class="mb-6">
                    <div class="max-w-3xl mx-auto">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold">Tạo chiến dịch thông báo mới</h2>
                            <BaseButton
                                v-if="showForm"
                                color="danger"
                                :icon="mdiClose"
                                @click="toggleForm"
                                small
                            />
                            <BaseButton
                                v-else
                                color="info"
                                :icon="mdiPlus"
                                label="Tạo mới"
                                @click="toggleForm"
                            />
                        </div>

                        <!-- Form content -->
                        <div v-if="showForm" class="space-y-6">
                            <!-- Sắp xếp lại các phần tử form cho hợp lý -->
                            <!-- ... -->
                        </div>
                    </div>
                </CardBox>

                <!-- Campaign List -->
                <CardBox class="mb-6">
                    <div class="overflow-x-auto">
                        <!-- Campaign table/list here -->
                    </div>
                </CardBox>
            </div>

            <!-- History Tab -->
            <div v-if="activeTab === 'history'" class="transition-opacity duration-500">
                <CardBox>
                    <NotificationList />
                </CardBox>
            </div>

            <!-- Groups Tab -->
            <div v-if="activeTab === 'groups'" class="transition-opacity duration-500">
                <CardBox>
                    <UserGroups />
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>
