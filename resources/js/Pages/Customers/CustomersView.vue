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
import { mdiFilter } from '@mdi/js'

const mainStore = useMainStore()
const { users } = storeToRefs(mainStore)

const props = defineProps({
    users: Object,
    filters: Object,
    upcomingBirthdays: Number
})

const showingUpcomingBirthdays = ref(props.filters?.upcoming_birthdays || false)
const showingDeletedUsers = ref(props.filters?.show_deleted || false)

const form = useForm({
    search: props.filters?.search || '',
    per_page: props.filters?.per_page || 10,
    upcoming_birthdays: showingUpcomingBirthdays.value,
    show_deleted: showingDeletedUsers.value
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

const applyFilters = () => {
    console.log('Applying filters:', form)
    router.get(route('users.index'), {
        search: form.search,
        per_page: form.per_page,
        upcoming_birthdays: form.upcoming_birthdays,
        show_deleted: form.show_deleted
    }, { preserveState: true })
}

const birthdayNotification = computed(() => {
    if (showingUpcomingBirthdays.value) {
        return 'Bạn đang xem các khách hàng có sinh nhật trong 15 ngày tới'
    } else {
        return `Có ${props.upcomingBirthdays} khách hàng có sinh nhật trong 15 ngày tới`
    }
})
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Quản lý khách hàng" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiTableAccount" title="Danh sách khách hàng" main>
                <BaseButton :icon="mdiAccountPlus" label="Thêm khách hàng mới" rounded-full small
                    @click="showAddCustomerModal = true" />
            </SectionTitleLineWithButton>
            <NotificationBar color="info" :icon="mdiCalendarAccount">
                {{ birthdayNotification }}
                <BaseButton v-if="!showingUpcomingBirthdays" label="Xem ngay" small @click="toggleUpcomingBirthdays" />
                <BaseButton v-else label="Hiển thị tất cả" small @click="toggleUpcomingBirthdays" />
            </NotificationBar>

            <CardBox class="mb-6" has-table>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="showingDeletedUsers" @change="toggleDeletedUsers" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Hiển thị người dùng đã xóa</span>
                        </label>
                    </div>
                    <BaseButton :icon="mdiFilter" label="Bộ lọc" @click="applyFilters" />
                </div>
                <input v-model="form.search" type="text" placeholder="Tìm kiếm theo tên hoặc số điện thoại"
                    class="w-full px-4 py-2 border rounded-md">
                <select v-model="form.per_page" @change="handlePerPageChange" class="px-8 py-2 border rounded-md">
                    <option :value="10">Xem 10 mỗi trang</option>
                    <option :value="25">Xem 25 mỗi trang</option>
                    <option :value="50">Xem 50 mỗi trang</option>
                </select>
                <AllCustomersTable :users="allUsers" :checkable="true" />
            </CardBox>

            <div v-if="props.users?.links" class="mt-6">
                <TablePagination :links="props.users.links" />
            </div>

            <AddCustomerModal v-model="showAddCustomerModal" @customer-added="handleCustomerAdded" />
        </SectionMain>
    </LayoutAuthenticated>
</template>
