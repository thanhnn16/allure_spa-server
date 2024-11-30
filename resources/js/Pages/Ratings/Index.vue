<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormControl from '@/Components/FormControl.vue'
import { mdiStar, mdiStarOutline, mdiCheck, mdiClose, mdiImage, mdiMagnify, mdiClose as mdiCloseCircle } from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import ImageGalleryModal from '@/Components/ImageGalleryModal.vue'

const ratings = ref([])
const loading = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)
const filterStatus = ref('')
const searchQuery = ref('')
const showGalleryModal = ref(false)
const selectedImages = ref([])
const selectedImageIndex = ref(0)

const toast = useToast()

const fetchRatings = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/ratings', {
            params: {
                page: currentPage.value,
                status: filterStatus.value,
                search: searchQuery.value
            }
        })
        console.log('Rating response:', response.data)
        console.log('First rating media:', response.data.data.data[0]?.media_urls)
        ratings.value = response.data.data.data
        lastPage.value = response.data.data.last_page
    } catch (error) {
        console.error('Error fetching ratings:', error)
        showNotification('Không thể tải danh sách đánh giá', 'danger')
    }
    loading.value = false
}

const updateRatingStatus = async (ratingId, newStatus) => {
    try {
        await axios.patch(`/api/ratings/${ratingId}/status`, {
            status: newStatus
        });

        showNotification('Cập nhật trạng thái thành công', 'success');
        await fetchRatings();
    } catch (error) {
        console.error('Error updating rating status:', error);
        showNotification('Không thể cập nhật trạng thái', 'danger');
    }
}

const showNotification = (message, type = 'success') => {
    toast[type](message)
}

const renderStars = (count) => {
    return Array(5).fill().map((_, index) =>
        index < count ? mdiStar : mdiStarOutline
    )
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('vi-VN')
}

// Thêm computed để format rating type
const formatRatingType = (type) => {
    return type === 'product' ? 'SP' : 'DV'
}

// Thêm computed để lấy status color
const getStatusColor = (status) => {
    const colors = {
        pending: 'warning',
        approved: 'success',
        rejected: 'danger'
    }
    return colors[status] || 'info'
}

const getStatusText = (status) => {
    const texts = {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        rejected: 'Đã từ chối'
    }
    return texts[status] || status
}

const openGallery = (images, startIndex = 0) => {
    selectedImages.value = images
    selectedImageIndex.value = startIndex
    showGalleryModal.value = true
}

const getMediaCountText = (mediaCount) => {
    if (!mediaCount) return ''
    return `(${mediaCount} ảnh)`
}

// Thêm hàm xử lý lỗi ảnh
const handleImageError = (event) => {
    event.target.src = '/path/to/fallback/image.jpg' // Thay thế bằng ảnh mặc định
}

onMounted(() => {
    fetchRatings()
})
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Quản lý đánh giá" />

        <SectionMain>
            <CardBox class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                    <div class="flex items-center space-x-2">
                        <h1 class="text-2xl font-bold dark:text-gray-100">Quản lý đánh giá</h1>
                        <span v-if="loading" class="animate-spin">⌛</span>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 mt-4 md:mt-0 w-full md:w-auto">
                        <div class="relative">
                            <FormControl v-model="searchQuery" placeholder="Tìm kiếm theo tên khách hàng..."
                                class="min-w-[250px] dark:bg-slate-800 dark:border-slate-700 dark:text-gray-100"
                                @input="fetchRatings">
                                <template #rightIcon>
                                    <span v-if="searchQuery"
                                        class="cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        @click="searchQuery = ''; fetchRatings()">
                                        ✕
                                    </span>
                                </template>
                            </FormControl>
                        </div>

                        <select v-model="filterStatus" class="rounded-lg border-gray-300 dark:bg-slate-800 dark:border-slate-700 dark:text-gray-100 
                                   focus:border-blue-500 focus:ring-blue-500 transition-colors duration-150"
                            @change="fetchRatings">
                            <option value="">Tất cả</option>
                            <option value="pending">Chờ duyệt</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="rejected">Đã từ chối</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border dark:border-slate-700">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-slate-800 border-b dark:border-slate-700">
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Khách hàng
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">
                                    Loại
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Sản phẩm/Dịch vụ
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                    Đánh giá
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Bình luận & Hình ảnh
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                    Ngày tạo
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                    Trạng thái
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-900 divide-y dark:divide-slate-700">
                            <tr v-for="rating in ratings" :key="rating.id"
                                class="hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors duration-150">
                                <!-- Customer Column -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700"
                                                :src="rating.user?.avatar_url || '/path/to/default/avatar.png'"
                                                :alt="rating.user?.name">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ rating.user?.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ rating.user?.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Type Column -->
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 text-xs font-medium rounded-full" :class="rating.rating_type === 'product' ?
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'">
                                        {{ formatRatingType(rating.rating_type) }}
                                    </span>
                                </td>

                                <!-- Product/Service Name Column -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                        {{ rating.item?.name || rating.item?.service_name }}
                                    </div>
                                </td>

                                <!-- Stars Column -->
                                <td class="px-6 py-4">
                                    <div class="flex justify-center space-x-1">
                                        <BaseIcon v-for="(star, index) in renderStars(rating.stars)" :key="index"
                                            :path="star" class="w-5 h-5"
                                            :class="index < rating.stars ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'" />
                                    </div>
                                </td>

                                <!-- Comment & Images Column -->
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ rating.comment }}
                                        </div>
                                        <div v-if="rating.media_urls?.length" class="flex items-center gap-2">
                                            <div class="flex -space-x-2">
                                                <div v-for="(media, index) in rating.media_urls.slice(0, 3)"
                                                    :key="media.id" class="relative cursor-pointer"
                                                    @click="openGallery(rating.media_urls, index)">
                                                    <img :src="media.url"
                                                        class="w-12 h-12 object-cover rounded-lg border-2 border-white dark:border-slate-800
                                                                hover:scale-110 hover:z-10 transition-transform duration-200"
                                                        :alt="`Ảnh đánh giá ${index + 1}`" @error="handleImageError" />
                                                </div>
                                            </div>
                                            <span v-if="rating.media_urls.length > 3"
                                                class="text-xs text-blue-500 hover:text-blue-600 dark:text-blue-400 cursor-pointer"
                                                @click="openGallery(rating.media_urls, 3)">
                                                +{{ rating.media_urls.length - 3 }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Date Column -->
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(rating.created_at) }}
                                    </span>
                                </td>

                                <!-- Status Column -->
                                <td class="px-6 py-4">
                                    <div class="flex justify-center">
                                        <div v-if="rating.status === 'pending'" class="flex items-center space-x-2">
                                            <button @click="updateRatingStatus(rating.id, 'approved')"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded-full
                                                           shadow-sm text-white bg-green-600 hover:bg-green-700 
                                                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500
                                                           transition-all duration-200 ease-in-out transform hover:scale-105">
                                                <BaseIcon :path="mdiCheck" class="w-4 h-4 mr-1" />
                                                Duyệt
                                            </button>
                                            <button @click="updateRatingStatus(rating.id, 'rejected')"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded-full
                                                           shadow-sm text-white bg-red-600 hover:bg-red-700
                                                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
                                                           transition-all duration-200 ease-in-out transform hover:scale-105">
                                                <BaseIcon :path="mdiClose" class="w-4 h-4 mr-1" />
                                                Từ chối
                                            </button>
                                        </div>
                                        <div v-else class="flex items-center">
                                            <span
                                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': rating.status === 'approved',
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': rating.status === 'rejected'
                                                }">
                                                <BaseIcon :path="rating.status === 'approved' ? mdiCheck : mdiClose"
                                                    class="w-4 h-4 mr-1.5" />
                                                {{ getStatusText(rating.status) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex justify-between items-center mt-6">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Trang {{ currentPage }} / {{ lastPage }}
                    </div>
                    <BaseButtons>
                        <BaseButton :disabled="currentPage === 1" @click="currentPage--; fetchRatings()"
                            :color="currentPage === 1 ? 'light' : 'info'" class="transition-all duration-200">
                            Trước
                        </BaseButton>
                        <BaseButton :disabled="currentPage === lastPage" @click="currentPage++; fetchRatings()"
                            :color="currentPage === lastPage ? 'light' : 'info'" class="transition-all duration-200">
                            Sau
                        </BaseButton>
                    </BaseButtons>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>

    <ImageGalleryModal v-if="showGalleryModal" :images="selectedImages" :initial-index="selectedImageIndex"
        @close="showGalleryModal = false" />
</template>

<style scoped>
.capitalize {
    text-transform: capitalize;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-1rem);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.2s ease-out;
}
</style>