<template>
    <LayoutAuthenticated>
        <Head :title="'Chi tiết sản phẩm: ' + product.name" />
        <SectionMain>
            <div class="flex justify-between items-center mb-6">
                <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" :title="'Chi tiết sản phẩm'" main />
                <BaseButton :icon="mdiArrowLeft" label="Quay lại" color="info" small @click="router.visit(route('products.index'))" />
            </div>

            <CardBox class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <Carousel v-if="product.media && product.media.length > 0" :items="processedMedia" :settings="carouselSettings">
                        </Carousel>
                        <img v-else src="/images/placeholder-product.jpg" :alt="product.name" class="w-full h-auto rounded-lg shadow-lg">
                        <div class="flex justify-between">
                            <div class="space-x-2">
                                <BaseButton :icon="mdiImage" label="Quản lý ảnh" color="success" @click="showManageImagesModal = true" />
                                <BaseButton :icon="mdiPencil" label="Chỉnh sửa" color="info" @click="showEditModal = true" />
                            </div>
                            <BaseButton :icon="mdiDelete" label="Xóa" color="danger" @click="showDeleteModal = true" />
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h3 class="text-2xl font-semibold">{{ product.name }}</h3>
                        <p class="text-gray-600">{{ product.category ? product.category.category_name : 'Không có danh mục' }}</p>
                        <div>
                            <h4 class="font-medium">Giá hiện tại:</h4>
                            <p class="text-2xl font-bold text-green-600">{{ formatPrice(product.price) }}</p>
                        </div>
                        <div>
                            <h4 class="font-medium">Số lượng:</h4>
                            <p>{{ product.quantity }}</p>
                        </div>
                        <div v-if="product.brand_description">
                            <h4 class="font-medium">Mô tả thương hiệu:</h4>
                            <p>{{ product.brand_description }}</p>
                        </div>
                        <div v-if="product.usage">
                            <h4 class="font-medium">Cách sử dụng:</h4>
                            <p>{{ product.usage }}</p>
                        </div>
                        <div v-if="product.benefits">
                            <h4 class="font-medium">Lợi ích:</h4>
                            <p>{{ product.benefits }}</p>
                        </div>
                        <div v-if="product.key_ingredients">
                            <h4 class="font-medium">Thành phần chính:</h4>
                            <p>{{ product.key_ingredients }}</p>
                        </div>
                        <div v-if="product.ingredients">
                            <h4 class="font-medium">Thành phần đầy đủ:</h4>
                            <p>{{ product.ingredients }}</p>
                        </div>
                        <div v-if="product.directions">
                            <h4 class="font-medium">Hướng dẫn sử dụng:</h4>
                            <p>{{ product.directions }}</p>
                        </div>
                        <div v-if="product.storage_instructions">
                            <h4 class="font-medium">Hướng dẫn bảo quản:</h4>
                            <p>{{ product.storage_instructions }}</p>
                        </div>
                        <div v-if="product.product_notes">
                            <h4 class="font-medium">Ghi chú sản phẩm:</h4>
                            <p>{{ product.product_notes }}</p>
                        </div>
                    </div>
                </div>
            </CardBox>

            <CardBox class="mb-6">
                <h3 class="text-xl font-semibold mb-4">Lịch sử giá</h3>
                <div class="overflow-x-auto">
                    <table v-if="product.price_history && product.price_history.length > 0" 
                           class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Giá</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ngày bắt đầu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ngày kết thúc</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(history, index) in sortedPriceHistory" 
                                :key="history.id"
                                class="border-b border-gray-100 hover:bg-gray-50 transition-colors"
                                :class="{'bg-green-50 hover:bg-green-100': !history.effective_to}">
                                <td class="px-6 py-4">
                                    <span :class="{'font-semibold text-green-600': !history.effective_to}">
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
                                          class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Đang áp dụng
                                    </span>
                                    <span v-else 
                                          class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Đã kết thúc
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-gray-500 italic">Chưa có dữ liệu lịch sử giá.</p>
                </div>
            </CardBox>

            <CardBox class="mb-6">
                <h3 class="text-xl font-semibold mb-4">Thuộc tính sản phẩm</h3>
                <table v-if="product.attributes && product.attributes.length > 0" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên thuộc tính</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá trị</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="attribute in product.attributes" :key="attribute.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ attribute.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ attribute.pivot.attribute_value }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-gray-500 italic">Chưa có dữ liệu thuộc tính sản phẩm.</p>
            </CardBox>
        </SectionMain>

        <EditProductModal
            v-model="showEditModal"
            :product="product"
            :categories="categories"
            @close="closeEditModal"
            @product-updated="handleProductUpdated"
        />

        <DeleteConfirmModal
            v-if="showDeleteModal"
            :product="product"
            @close="showDeleteModal = false"
            @product-deleted="handleProductDeleted"
        />

        <ManageImagesModal
            v-model="showManageImagesModal"
            :product="product"
            @updated="handleImagesUpdated"
        />
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
import { mdiPackageVariantClosed, mdiArrowLeft, mdiPencil, mdiDelete, mdiImage } from '@mdi/js'

const props = defineProps({
    product: Object,
    categories: Array,
})

const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showManageImagesModal = ref(false)

const carouselSettings = {
    itemsToShow: 1,
    snapAlign: 'center',
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN')
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

const logImageLoaded = (url) => {
    console.log(`Image loaded successfully: ${url}`)
}

const handleImageError = (event, url) => {
    console.error(`Failed to load image: ${url}`)
    event.target.src = '/images/placeholder-product.jpg'
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

th, td {
    border: none;
}
</style>
