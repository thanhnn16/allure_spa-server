<script setup>
import { mdiMonitorCellphone, mdiTableBorder, mdiTableOff, mdiGithub, mdiAccountPlus, mdiCalendarAccount, mdiTableAccount } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import AllCustomersTable from '@/Pages/Customers/Components/AllCustomersTable.vue'
import CardBox from '@/Components/CardBox.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBoxComponentEmpty from '@/Components/CardBoxComponentEmpty.vue'
import { Head } from "@inertiajs/vue3";
import { computed, ref, watch, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AddCustomerModal from '@/Pages/Customers/Components/AddCustomerModal.vue'
import { useMainStore } from '@/Stores/main'
import { storeToRefs } from 'pinia'
import TablePagination from '@/Pages/Customers/Components/TablePagination.vue'
import { mdiFilter, mdiAccountMultiple, mdiCalendarRange, mdiCake, mdiStar, mdiCartOutline, mdiCalendarClock } from '@mdi/js'
import ImportCustomersModal from '@/Pages/Customers/Components/ImportCustomersModal.vue'

const mainStore = useMainStore()
const { users } = storeToRefs(mainStore)

const props = defineProps({
    users: Object,
    filters: Object,
    upcomingBirthdays: Number
})

const showingUpcomingBirthdays = ref(props.filters?.upcoming_birthdays || false)
const showingDeletedUsers = ref(props.filters?.show_deleted || false)
const showFilters = ref(false)

const form = useForm({
    search: props.filters?.search || '',
    per_page: props.filters?.per_page || 10,
    upcoming_birthdays: showingUpcomingBirthdays.value,
    show_deleted: showingDeletedUsers.value,
    gender: props.filters?.gender || '',
    age_range: props.filters?.age_range || '',
    point_range: props.filters?.point_range || '',
    purchase_count_range: props.filters?.purchase_count_range || '',
    created_at_range: props.filters?.created_at_range || ''
})

const debouncedSearch = ref(null)

watch(() => form.search, (value) => {
    clearTimeout(debouncedSearch.value)
    debouncedSearch.value = setTimeout(() => {
        router.get(route('users.index'), {
            search: form.search,
            per_page: form.per_page
        }, { preserveState: true })
    }, 300)
})

const handlePerPageChange = () => {
    router.get(route('users.index'), {
        search: form.search,
        per_page: form.per_page
    }, { preserveState: true })
}

const hasUsers = computed(() => {
    console.log('hasUsers:', props.users?.data?.length > 0)
    return props.users?.data?.length > 0
})

const showAddCustomerModal = ref(false)

const handleCustomerAdded = () => {
    mainStore.fetchUsers()
}

const toggleUpcomingBirthdays = () => {
    form.upcoming_birthdays = !form.upcoming_birthdays
    showingUpcomingBirthdays.value = form.upcoming_birthdays
    applyFilters()
}

const toggleDeletedUsers = () => {
    form.show_deleted = !form.show_deleted
    showingDeletedUsers.value = form.show_deleted
    console.log('Toggled deleted users:', form.show_deleted)
    applyFilters()
}

const toggleFilters = () => {
    showFilters.value = !showFilters.value
}

const applyFilters = () => {
    router.get(route('users.index'), form, { preserveState: true })
}

const resetFilters = () => {
    form.gender = ''
    form.age_range = ''
    form.point_range = ''
    form.purchase_count_range = ''
    form.created_at_range = ''
    applyFilters()
}

const birthdayNotification = computed(() => {
    if (showingUpcomingBirthdays.value) {
        return 'Bạn đang xem các khách hàng có sinh nhật trong 15 ngày tới'
    } else {
        return `Có ${props.upcomingBirthdays} khách hàng có sinh nhật trong 15 ngày tới`
    }
})

const showImportCustomersModal = ref(false)

const handleCustomersImported = () => {
    mainStore.fetchUsers()
}
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Quản lý khách hàng" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiTableAccount" title="Danh sách khách hàng" main>
                <BaseButton :icon="mdiAccountPlus" label="Thêm khách hàng mới" rounded-full small
                    @click="showAddCustomerModal = true" />
                <BaseButton :icon="mdiTableBorder" label="Nhập từ Excel" rounded-full small
                    @click="showImportCustomersModal = true" />
            </SectionTitleLineWithButton>
            <NotificationBar color="info" :icon="mdiCalendarAccount">
                {{ birthdayNotification }}
                <BaseButton v-if="!showingUpcomingBirthdays" label="Xem ngay" small @click="toggleUpcomingBirthdays" />
                <BaseButton v-else label="Hiển thị tất cả" small @click="toggleUpcomingBirthdays" />
            </NotificationBar>

            <CardBox class="mb-6 px-4 py-4" has-table>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-4">
                        <BaseButton :icon="mdiFilter" label="Bộ lọc" @click="toggleFilters" />
                        <BaseButton v-if="showFilters" label="Đặt lại bộ lọc" @click="resetFilters" />
                    </div>
                </div>
                <div v-if="showFilters">
                    <div class="mb-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="showingDeletedUsers" @change="toggleDeletedUsers"
                                class="sr-only peer">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Hiển thị người dùng
                                đã xóa</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2">Giới tính</label>
                            <select v-model="form.gender" class="w-full px-4 py-2 border rounded-md">
                                <option value="">Tất cả</option>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2">Độ tuổi</label>
                            <select v-model="form.age_range" class="w-full px-4 py-2 border rounded-md">
                                <option value="">Tất cả</option>
                                <option value="0-18">0-18</option>
                                <option value="19-30">19-30</option>
                                <option value="31-50">31-50</option>
                                <option value="51-100">51+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2">Điểm tích lũy</label>
                            <select v-model="form.point_range" class="w-full px-4 py-2 border rounded-md">
                                <option value="">Tất cả</option>
                                <option value="0-100">0-100</option>
                                <option value="101-500">101-500</option>
                                <option value="501-1000">501-1000</option>
                                <option value="1001-10000">1001+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2">Số lần mua hàng</label>
                            <select v-model="form.purchase_count_range" class="w-full px-4 py-2 border rounded-md">
                                <option value="">Tất cả</option>
                                <option value="0-1">0-1</option>
                                <option value="2-5">2-5</option>
                                <option value="6-10">6-10</option>
                                <option value="11-100">11+</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2">Ngày tạo tài khoản</label>
                        <input type="date" v-model="form.created_at_range" class="w-full px-4 py-2 border rounded-md">
                    </div>
                    <BaseButton :icon="mdiFilter" label="Áp dụng bộ lọc" @click="applyFilters" />
                </div>
                <div class="mt-4">
                    <input v-model="form.search" type="text" placeholder="Tìm kiếm theo tên hoặc số điện thoại"
                        class="w-full px-4 py-2 mb-4 border rounded-md">
                    <select v-model="form.per_page" @change="handlePerPageChange" class="px-8 py-2 border rounded-md">
                        <option :value="10">Xem 10 mỗi trang</option>
                        <option :value="25">Xem 25 mỗi trang</option>
                        <option :value="50">Xem 50 mỗi trang</option>
                    </select>
                </div>
                <AllCustomersTable :users="users.data" :checkable="true" />
            </CardBox>

            <div v-if="props.users?.links" class="mt-6">
                <TablePagination :links="props.users.links" />
            </div>

            <AddCustomerModal v-model="showAddCustomerModal" @customer-added="handleCustomerAdded" />
            <ImportCustomersModal :show="showImportCustomersModal" @close="showImportCustomersModal = false"
                @imported="handleCustomersImported" />
        </SectionMain>
    </LayoutAuthenticated>
</template>