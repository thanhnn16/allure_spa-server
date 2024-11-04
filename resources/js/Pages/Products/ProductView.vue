<template>
    <LayoutAuthenticated>

        <Head title="Quản lý mỹ phẩm" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" title="Danh sách mỹ phẩm" main>
                <BaseButton :icon="mdiPlus" label="Tạo mỹ phẩm" color="info" rounded-full small
                    @click="showCreateModal = true" />
                <BaseButton :icon="mdiTableBorder" label="Nhập từ Excel" color="success" rounded-full small />
            </SectionTitleLineWithButton>

            <CardBox class="mb-6 px-4 py-4 dark:bg-dark-surface" has-table>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-4">
                        <BaseButton :icon="mdiFilter" label="Bộ lọc" @click="toggleFilters" />
                        <BaseButton v-if="showFilters" label="Đặt lại bộ lọc" @click="resetFilters" />
                    </div>
                </div>
                <div v-if="showFilters" class="mb-4">
                    <div class="mb-4">
                        <label class="block mb-2 dark:text-gray-300">Danh mục</label>
                        <select v-model="form.category" class="w-full px-4 py-2 border rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300">
                            <option value="">Tất cả danh mục</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.category_name }}
                            </option>
                        </select>
                    </div>
                    <BaseButton :icon="mdiFilter" label="Áp dụng bộ lọc" @click="applyFilters" />
                </div>
                <div class="my-4">
                    <input v-model="form.search" type="text" placeholder="Tìm kiếm sản phẩm..."
                        class="w-full px-4 py-2 mb-4 border rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300 dark:placeholder-gray-500">
                    <select v-model="form.per_page" @change="handlePerPageChange" class="px-8 py-2 border rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300">
                        <option :value="10">Xem 10 mỗi trang</option>
                        <option :value="25">Xem 25 mỗi trang</option>
                        <option :value="50">Xem 50 mỗi trang</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-300">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-dark-surface dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-3 w-24">Ảnh</th>
                                <th @click="sort('name')" scope="col" class="px-6 py-3 cursor-pointer">
                                    <div class="flex items-center justify-between h-full">
                                        <span class="mr-2">Tên sản phẩm</span>
                                        <BaseIcon :path="sortIcon('name')" size="18" class="flex-shrink-0"
                                            :class="{ 'text-gray-900': form.sort === 'name', 'text-gray-400': form.sort !== 'name' }" />
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
                                class="bg-white border-b hover:bg-gray-50 dark:bg-dark-surface dark:border-dark-border dark:hover:bg-dark-bg">
                                <td class="px-6 py-4">
                                    <img :src="getProductImage(product)" :alt="product.name"
                                        class="w-16 h-16 object-cover rounded-md">
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                    {{ product.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ product.category ? product.category.category_name : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatPrice(product.price) }}
                                </td>
                                <td class="px-6 py-4">
                                    <BaseButton label="Xem chi tiết" color="info" small
                                        @click="viewProductDetails(product.id)" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>

            <div v-if="products.links" class="mt-6">
                <TablePagination :links="products.links" />
            </div>

            <CreateProductModal 
                v-model:show="showCreateModal"
                :categories="categories" 
                @close="handleModalClose"
                @created="handleProductCreated"
                @error="handleProductError"
                @validationFailed="handleValidationFailed"
            />
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
import { ref, watch, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import TablePagination from '@/Components/TablePagination.vue'
import CreateProductModal from '@/Components/Products/CreateProductModal.vue'
import { useToast } from 'vue-toastification'

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object
})

const showFilters = ref(false)
const showCreateModal = ref(false)
const toast = useToast()

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
    router.get(route('products.index'), form, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

const resetFilters = () => {
    form.reset()
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

const getProductImage = (product) => {
    if (product.media && product.media.length > 0) {
        const media = product.media[0];
        if (media.file_path) {
            return `/storage${media.file_path}`;
        }
    }
    return 'https://via.placeholder.com/150';
}

const products = computed(() => {
    console.log('Products data:', props.products);
    return props.products;
});

const viewProductDetails = (productId) => {
    router.visit(route('products.show', productId))
}

const handleProductCreated = (success) => {
    if (success) {
        showCreateModal.value = false
        toast.success('Tạo sản phẩm thành công!')
        router.reload()
    }
}

const handleProductError = (error) => {
    toast.error('Có lỗi xảy ra khi tạo sản phẩm')
}

const handleModalClose = () => {
    if (!form.processing) {
        showCreateModal.value = false
    }
}

const handleValidationFailed = (errors) => {
    toast.error('Vui lòng kiểm tra lại thông tin nhập vào')
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
