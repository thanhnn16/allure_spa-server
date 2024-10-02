<template>
    <LayoutAuthenticated>

        <Head title="Quản lý sản phẩm" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" title="Danh sách sản phẩm" main>
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
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block mb-2">Danh mục</label>
                            <select v-model="form.category" class="w-full px-4 py-2 border rounded-md">
                                <option value="">Tất cả danh mục</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.category_name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2">Thương hiệu</label>
                            <select v-model="form.brand" class="w-full px-4 py-2 border rounded-md">
                                <option value="">Tất cả thương hiệu</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                    {{ brand.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <BaseButton :icon="mdiFilter" label="Áp dụng bộ lọc" @click="applyFilters" />
                </div>
                <div class="mt-4">
                    <input v-model="form.search" type="text" placeholder="Tìm kiếm sản phẩm..."
                        class="w-full px-4 py-2 mb-4 border rounded-md">
                    <select v-model="form.per_page" @change="handlePerPageChange" class="px-8 py-2 border rounded-md">
                        <option :value="10">Xem 10 mỗi trang</option>
                        <option :value="25">Xem 25 mỗi trang</option>
                        <option :value="50">Xem 50 mỗi trang</option>
                    </select>
                </div>
                <table class="w-full border-collapse border mt-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">Ảnh</th>
                            <th class="border p-2">Tên sản phẩm</th>
                            <th class="border p-2">Danh mục</th>
                            <th class="border p-2">Thương hiệu</th>
                            <th class="border p-2">Giá</th>
                            <th class="border p-2">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                            <td class="border p-2">
                                <img :src="product.image_url" alt="Product image"
                                    class="w-16 h-16 object-cover rounded">
                            </td>
                            <td class="border p-2">{{ product.product_name }}</td>
                            <td class="border p-2">{{ product.category.category_name }}</td>
                            <td class="border p-2">{{ product.brand.name }}</td>
                            <td class="border p-2">{{ product.price }}</td>
                            <td class="border p-2">
                                <BaseButton label="Xem chi tiết" color="info" small />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardBox>

            <div v-if="products.links" class="mt-6">
                <TablePagination :links="products.links" />
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<script setup>
import { mdiPackageVariantClosed, mdiPlus, mdiTableBorder, mdiFilter } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { Head } from "@inertiajs/vue3"
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import TablePagination from '@/Pages/Customers/Components/TablePagination.vue'

const props = defineProps({
    products: Object,
    categories: Array,
    brands: Array,
    filters: Object,
})

const showFilters = ref(false)

const form = useForm({
    search: props.filters?.search || '',
    category: props.filters?.category || '',
    brand: props.filters?.brand || '',
    per_page: props.filters?.per_page || 10,
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
    form.brand = ''
    applyFilters()
}

const handlePerPageChange = () => {
    applyFilters()
}
</script>