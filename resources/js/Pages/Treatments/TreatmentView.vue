<template>
    <LayoutAuthenticated>

        <Head title="Quản lý liệu trình" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" title="Danh sách liệu trình" main>
                <BaseButton :icon="mdiPlus" label="Tạo liệu trình" color="info" rounded-full small />
                <BaseButton :icon="mdiTableBorder" label="Nhập từ Excel" color="success" rounded-full small />
            </SectionTitleLineWithButton>

            <CardBox class="mb-6 px-4 py-4" has-table>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-4">
                        <BaseButton :icon="mdiFilter" label="Bộ lọc" @click="toggleFilters" />
                        <BaseButton v-if="showFilters" label="Đặt lại bộ lọc" @click="resetFilters" />
                    </div>
                </div>
                <div v-if="showFilters" class="mb-4">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2">Danh mục</label>
                            <select v-model="form.category" class="w-full px-4 py-2 border rounded-md">
                                <option value="">Tất cả danh mục</option>
                                <template v-for="category in categories" :key="category.id">
                                    <option :value="category.id">{{ category.category_name }}</option>
                                    <option v-for="child in category.children" :key="child.id" :value="child.id">
                                        -- {{ child.category_name }}
                                    </option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <BaseButton :icon="mdiFilter" label="Áp dụng bộ lọc" @click="applyFilters" />
                </div>
                <div class="my-4">
                    <input v-model="form.search" type="text" placeholder="Tìm kiếm sản phẩm..."
                        class="w-full px-4 py-2 mb-4 border rounded-md">
                    <select v-model="form.per_page" @change="handlePerPageChange" class="px-8 py-2 border rounded-md">
                        <option :value="10">Xem 10 mỗi trang</option>
                        <option :value="25">Xem 25 mỗi trang</option>
                        <option :value="50">Xem 50 mỗi trang</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 w-24">Ảnh</th>
                                <th @click="sort('treatment_name')" scope="col" class="px-6 py-3 cursor-pointer">
                                    <div class="flex items-center justify-between h-full">
                                        <span class="mr-2">Tên liệu trình</span>
                                        <BaseIcon :path="sortIcon('treatment_name')" size="18" class="flex-shrink-0"
                                            :class="{ 'text-gray-900': form.sort === 'treatment_name', 'text-gray-400': form.sort !== 'treatment_name' }" />
                                    </div>
                                </th>
                                <th @click="sort('category_id')" scope="col" class="px-6 py-3 cursor-pointer">
                                    <div class="flex items-center justify-between h-full">
                                        <span class="mr-2">Danh mục</span>
                                        <BaseIcon :path="sortIcon('category_id')" size="18" class="flex-shrink-0"
                                            :class="{ 'text-gray-900': form.sort === 'category_id', 'text-gray-400': form.sort !== 'category_id' }" />
                                    </div>
                                </th>
                                <th @click="sort('duration')" scope="col" class="px-6 py-3 cursor-pointer">
                                    <div class="flex items-center justify-between h-full">
                                        <span class="mr-2">Thời gian</span>
                                        <BaseIcon :path="sortIcon('duration')" size="18" class="flex-shrink-0"
                                            :class="{ 'text-gray-900': form.sort === 'duration', 'text-gray-400': form.sort !== 'duration' }" />
                                    </div>
                                </th>
                                <th @click="sort('price')" scope="col" class="px-6 py-3 cursor-pointer">
                                    <div class="flex items-center justify-between h-full">
                                        <span class="mr-2">Giá</span>
                                        <BaseIcon :path="sortIcon('price')" size="18" class="flex-shrink-0"
                                            :class="{ 'text-gray-900': form.sort === 'price', 'text-gray-400': form.sort !== 'price' }" />
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 w-32">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="treatment in treatments.data" :key="treatment.id"
                                class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <img :src="treatment.image_url" alt="Treatment image"
                                        class="w-16 h-16 object-cover rounded-md">
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ treatment.treatment_name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ getCategoryPath(treatment.category) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatDuration(treatment.duration) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatPrice(treatment.price) }}
                                </td>
                                <td class="px-6 py-4">
                                    <BaseButton label="Xem chi tiết" color="info" small />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>

            <div v-if="treatments.links" class="mt-6">
                <TablePagination :links="treatments.links" />
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<script setup>
import { mdiPackageVariantClosed, mdiPlus, mdiTableBorder, mdiFilter, mdiSort, mdiSortAscending, mdiSortDescending } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { Head } from "@inertiajs/vue3"
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import TablePagination from '@/Components/TablePagination.vue'

const props = defineProps({
    treatments: Object,
    categories: Array,
    filters: Object,
})

const showFilters = ref(false)

const form = useForm({
    search: props.filters?.search || '',
    category: props.filters?.category || '',
    per_page: props.filters?.per_page || 10,
    sort: props.filters?.sort || '',
    direction: props.filters?.direction || 'asc',
})

const debouncedSearch = ref(null)

watch(() => form.search, (value) => {
    clearTimeout(debouncedSearch.value)
    debouncedSearch.value = setTimeout(() => {
        applyFilters()
    }, 300)
})

const toggleFilters = () => {
    showFilters.value = !showFilters.value
}

const applyFilters = () => {
    router.get(route('treatments.index'), form, { preserveState: true })
}

const resetFilters = () => {
    form.category = ''
    applyFilters()
}

const handlePerPageChange = () => {
    applyFilters()
}

const sort = (column) => {
    if (form.sort === column) {
        form.direction = form.direction === 'asc' ? 'desc' : 'asc'
    } else {
        form.sort = column
        form.direction = 'asc'
    }
    applyFilters()
}

const sortIcon = (column) => {
    if (form.sort !== column) {
        return mdiSort
    }
    return form.direction === 'asc' ? mdiSortAscending : mdiSortDescending
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const formatDuration = (duration) => {
    if (duration === null || duration === undefined) return 'Chưa cập nhật';
    
    const hours = Math.floor(duration / 60);
    const minutes = duration % 60;
    
    let result = '';
    if (hours > 0) {
        result += `${hours}h `;
    }
    if (minutes > 0 || hours === 0) {
        result += `${minutes}m`;
    }
    return result.trim();
}

const getCategoryPath = (category) => {
    if (category.parent) {
        return `${category.parent.category_name} > ${category.category_name}`;
    }
    return category.category_name;
}
</script>

<style scoped>
th {
    height: 3rem;
    vertical-align: middle;
}

th>div {
    min-height: 1.5rem;
    line-height: 1.5rem;
}
</style>