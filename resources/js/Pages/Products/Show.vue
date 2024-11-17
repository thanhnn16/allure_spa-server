<template>
    <LayoutAuthenticated>

        <Head :title="'Chi tiết sản phẩm: ' + product.name" />
        <SectionMain>
            <div class="flex justify-between items-center mb-6">
                <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" :title="'Chi tiết sản phẩm'" main />
            </div>

            <CardBox class="mb-6 dark:bg-dark-surface">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-lg p-4">
                            <Carousel v-if="product.media && product.media.length > 0" :items="processedMedia"
                                :settings="carouselSettings" class="rounded-lg overflow-hidden">
                            </Carousel>
                            <img v-else src="/images/placeholder-product.jpg" :alt="product.name"
                                class="w-full h-auto rounded-lg shadow">
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <BaseButton :icon="mdiImage" label="Quản lý ảnh" color="success"
                                class="flex-grow md:flex-grow-0" @click="showManageImagesModal = true" />
                            <BaseButton :icon="mdiPencil" label="Chỉnh sửa" color="info"
                                class="flex-grow md:flex-grow-0" @click="showEditModal = true" />
                            <BaseButton :icon="mdiTranslate" label="Quản lý bản dịch" color="warning"
                                class="flex-grow md:flex-grow-0" @click="showTranslationsModal = true" />
                            <BaseButton :icon="mdiDelete" label="Xóa" color="danger" class="flex-grow md:flex-grow-0"
                                @click="showDeleteModal = true" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-dark-surface rounded-xl shadow-lg p-6 space-y-6">
                        <div class="border-b dark:border-dark-border pb-4">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ product.name }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">
                                {{ product.category ? product.category.category_name : 'Không có danh mục' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-dark-bg rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Giá hiện tại</h4>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-500">
                                    {{ formatPrice(product.price) }}
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-dark-bg rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Số lượng</h4>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ product.quantity }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <template v-for="(value, key) in productDetails" :key="key">
                                <div v-if="product[key]" class="border-b dark:border-dark-border pb-4">
                                    <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">{{ value }}</h4>
                                    <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ product[key] }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </CardBox>

            <CardBox class="mb-6 dark:bg-dark-surface">
                <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Lịch sử giá</h3>
                <div class="overflow-x-auto">
                    <template v-if="sortedPriceHistory && sortedPriceHistory.length > 0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-border">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-dark-border">
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Giá</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Ngày bắt đầu</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Ngày kết thúc</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(history) in sortedPriceHistory" :key="history.id"
                                    class="border-b border-gray-100 hover:bg-gray-50 transition-colors dark:border-dark-border dark:hover:bg-dark-bg"
                                    :class="{ 'bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30': !history.effective_to }">
                                    <td class="px-6 py-4">
                                        <span :class="{ 'font-semibold text-green-600': !history.effective_to }">
                                            {{ formatPrice(history.price) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ formatDateTime(history.effective_from) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ history.effective_to ? formatDateTime(history.effective_to) : '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="!history.effective_to"
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                                            Đang áp dụng
                                        </span>
                                        <span v-else
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                            Đã kết thúc
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                    <p v-else class="text-gray-500 italic">Chưa có dữ liệu lịch sử giá.</p>
                </div>
            </CardBox>

            <CardBox class="mb-6 dark:bg-dark-surface">
                <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Thuộc tính sản phẩm</h3>
                <template v-if="product.attributes && product.attributes.length > 0">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-border">
                        <thead class="bg-gray-50 dark:bg-dark-bg">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tên thuộc tính</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Giá trị</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-dark-surface dark:divide-dark-border">
                            <tr v-for="attribute in product.attributes" :key="attribute.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ attribute.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ attribute.pivot.attribute_value }}</td>
                            </tr>
                        </tbody>
                    </table>
                </template>
                <p v-else class="text-gray-500 italic">Chưa có dữ liệu thuộc tính sản phẩm.</p>
            </CardBox>
        </SectionMain>

        <EditProductModal v-model="showEditModal" :product="product" :categories="categories" @close="closeEditModal"
            @product-updated="handleProductUpdated" />

        <DeleteConfirmModal v-if="showDeleteModal" :product="product" @close="showDeleteModal = false"
            @product-deleted="handleProductDeleted" />

        <ManageImagesModal v-model="showManageImagesModal" :product="product" @updated="handleImagesUpdated" />

        <TranslationsModal v-model="showTranslationsModal" :product="product"
            @translations-updated="handleTranslationsUpdated" />
    </LayoutAuthenticated>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import EditProductModal from './Components/EditProductModal.vue'
import DeleteConfirmModal from './Components/DeleteConfirmModal.vue'
import Carousel from '@/Components/Carousel.vue'
import ManageImagesModal from './Components/ManageImagesModal.vue'
import TranslationsModal from './Components/TranslationsModal.vue'
import { mdiPackageVariantClosed, mdiArrowLeft, mdiPencil, mdiDelete, mdiImage, mdiTranslate } from '@mdi/js'

const props = defineProps({
    product: Object,
    categories: Array,
})

const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showManageImagesModal = ref(false)
const showTranslationsModal = ref(false)

const carouselSettings = {
    itemsToShow: 1,
    snapAlign: 'center',
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}


const closeEditModal = () => {
    showEditModal.value = false
}

const handleProductUpdated = () => {
    closeEditModal()
    router.reload({ only: ['product'] })
}

const handleProductDeleted = () => {
    showDeleteModal.value = false
    router.visit(route('products.index'))
}

const processedMedia = computed(() => {
    return props.product.media.map(item => ({
        ...item,
        file_path: item.file_path.startsWith('http') ? item.file_path : `/storage/${item.file_path}`
    }))
})

const handleImagesUpdated = () => {
    router.reload({ only: ['product'] })
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const sortedPriceHistory = computed(() => {
    if (!props.product.price_history) return []
    return [...props.product.price_history].sort((a, b) => {
        return new Date(b.effective_from) - new Date(a.effective_from)
    })
})

const handleTranslationsUpdated = () => {
    router.reload({ only: ['product'] })
}

const productDetails = {
    brand_description: 'Mô tả thương hiệu',
    usage: 'Cách sử dụng',
    benefits: 'Lợi ích',
    key_ingredients: 'Thành phần chính',
    ingredients: 'Thành phần đầy đủ',
    directions: 'Hướng dẫn sử dụng',
    storage_instructions: 'Hướng dẫn bảo quản',
    product_notes: 'Ghi chú sản phẩm'
}

onMounted(() => {
    console.log('Chi tiết sản phẩm:', props.product)
    if (props.product.media && props.product.media.length > 0) {
        console.log('Đường dẫn hình ảnh gốc:', props.product.media[0].file_path)
        console.log('Đường dẫn hình ảnh đã xử lý:', processedMedia.value[0].file_path)
    }
})
</script>

<style scoped>
/* Add these styles if you want to remove the default table borders completely */
table {
    border-collapse: collapse;
}

th,
td {
    border: none;
}
</style>
