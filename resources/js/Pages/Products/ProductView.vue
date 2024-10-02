<template>
    <LayoutAuthenticated>

        <Head title="Quản lý mỹ phẩm" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" title="Danh sách mỹ phẩm" main>
                <BaseButton :icon="mdiPlus" label="Tạo mỹ phẩm" color="info" rounded-full small />
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
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.category_name }}
                                </option>
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
                                <th @click="sort('product_name')" scope="col" class="px-6 py-3 cursor-pointer">
                                    <div class="flex items-center justify-between h-full">
                                        <span class="mr-2">Tên sản phẩm</span>
                                        <BaseIcon :path="sortIcon('product_name')" size="18" class="flex-shrink-0"
                                            :class="{ 'text-gray-900': form.sort === 'product_name', 'text-gray-400': form.sort !== 'product_name' }" />
                                    </div>
                                </th>
                                <th @click="sort('category_id')" scope="col" class="px-6 py-3 cursor-pointer">
                                    <div class="flex items-center justify-between h-full">
                                        <span class="mr-2">Danh mục</span>
                                        <BaseIcon :path="sortIcon('category_id')" size="18" class="flex-shrink-0"
                                            :class="{ 'text-gray-900': form.sort === 'category_id', 'text-gray-400': form.sort !== 'category_id' }" />
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
                            <tr v-for="product in products.data" :key="product.id"
                                class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <img :src="product.image_url" alt="Product image"
                                        class="w-16 h-16 object-cover rounded-md">
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ product.product_name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ product.category.category_name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatPrice(product.price) }}
                                </td>
                                <td class="px-6 py-4">
                                    <BaseButton label="Xem chi tiết" color="info" small />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>

            <div v-if="products.links" class="mt-6">
                <TablePagination :links="products.links" />
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
    products: Object,
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
    router.get(route('products.index'), form, { preserveState: true })
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