<script setup>
import { mdiAccountTie, mdiTableBorder, mdiAccountPlus } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import CardBox from '@/Components/CardBox.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { Head } from "@inertiajs/vue3"
import { computed, ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useMainStore } from '@/Stores/main'
import { storeToRefs } from 'pinia'
import TablePagination from '@/Components/TablePagination.vue'
import { mdiFilter } from '@mdi/js'
import AddStaffModal from './Components/AddStaffModal.vue'
import StaffTable from './Components/StaffTable.vue'

const mainStore = useMainStore()

const props = defineProps({
    staff: Object,
    filters: Object,
})

const showingDeletedStaff = ref(props.filters?.show_deleted || false)
const showFilters = ref(false)

const form = useForm({
    search: props.filters?.search || '',
    per_page: props.filters?.per_page || 10,
    show_deleted: showingDeletedStaff.value,
    gender: props.filters?.gender || '',
})

const debouncedSearch = ref(null)

watch(() => form.search, (value) => {
    clearTimeout(debouncedSearch.value)
    debouncedSearch.value = setTimeout(() => {
        router.get(route('staff.index'), {
            search: form.search,
            per_page: form.per_page
        }, { preserveState: true })
    }, 300)
})

const handlePerPageChange = () => {
    router.get(route('staff.index'), {
        search: form.search,
        per_page: form.per_page
    }, { preserveState: true })
}

const hasStaff = computed(() => {
    return props.staff?.data?.length > 0
})

const staffData = computed(() => props.staff?.data || [])

const showAddStaffModal = ref(false)

const handleStaffAdded = () => {
    router.reload()
}

const toggleDeletedStaff = () => {
    form.show_deleted = !form.show_deleted
    showingDeletedStaff.value = form.show_deleted
    applyFilters()
}

const toggleFilters = () => {
    showFilters.value = !showFilters.value
}

const applyFilters = () => {
    router.get(route('staff.index'), form, { preserveState: true })
}

const resetFilters = () => {
    form.gender = ''
    applyFilters()
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Quản lý nhân viên" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiAccountTie" title="Danh sách nhân viên" main>
                <BaseButton :icon="mdiAccountPlus" label="Thêm nhân viên mới" rounded-full small
                    @click="showAddStaffModal = true"
                    class="dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200" />
            </SectionTitleLineWithButton>

            <CardBox class="mb-6 px-4 py-4 dark:bg-slate-900" has-table>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-4">
                        <BaseButton :icon="mdiFilter" label="Bộ lọc" @click="toggleFilters"
                            class="dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200" />
                        <BaseButton v-if="showFilters" label="Đặt lại bộ lọc" @click="resetFilters"
                            class="dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200" />
                    </div>
                </div>

                <div v-if="showFilters">
                    <div class="mb-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="showingDeletedStaff" @change="toggleDeletedStaff"
                                class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 
                                peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer 
                                dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white 
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white 
                                after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 
                                after:transition-all dark:border-slate-600 peer-checked:bg-blue-600">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-slate-300">
                                Hiển thị nhân viên đã xóa
                            </span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2 dark:text-slate-300">Giới tính</label>
                            <select v-model="form.gender" class="w-full px-4 py-2 border rounded-md dark:bg-slate-800 
                                dark:border-slate-700 dark:text-slate-300">
                                <option value="">Tất cả</option>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <input v-model="form.search" type="text" placeholder="Tìm kiếm theo tên hoặc số điện thoại" 
                        class="w-full px-4 py-2 mb-4 border rounded-md dark:bg-slate-800 
                        dark:border-slate-700 dark:text-slate-300 
                        placeholder:dark:text-slate-500">

                    <select v-model="form.per_page" @change="handlePerPageChange" 
                        class="px-10 py-2 border rounded-md dark:bg-slate-800 dark:border-slate-600 
                        dark:text-slate-200 dark:focus:border-slate-500 dark:focus:ring-slate-500">
                        <option :value="10">Xem 10 mỗi trang</option>
                        <option :value="25">Xem 25 mỗi trang</option>
                        <option :value="50">Xem 50 mỗi trang</option>
                    </select>
                </div>

                <div v-if="!hasStaff" class="text-center py-5 dark:text-slate-300">
                    <p>Không có nhân viên</p>
                </div>

                <StaffTable v-if="hasStaff" :items="staffData" :checkable="true" />
            </CardBox>

            <div v-if="props.staff?.links" class="mt-6">
                <TablePagination :links="props.staff.links" />
            </div>

            <AddStaffModal v-model="showAddStaffModal" @staff-added="handleStaffAdded" />
        </SectionMain>
    </LayoutAuthenticated>
</template> 