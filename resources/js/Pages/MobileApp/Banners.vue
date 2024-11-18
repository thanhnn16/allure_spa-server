<script setup>
import { ref, onMounted, watch } from 'vue'
import { useForm, router , Head} from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import { mdiPlus, mdiPencil, mdiDelete, mdiImage, mdiDragVertical, mdiEye, mdiEyeOff } from '@mdi/js'
import draggable from 'vuedraggable'
import { useToast } from 'vue-toastification'

const props = defineProps({
    banners: {
        type: Array,
        required: true
    }
})

// Update the watch to sort banners by order
watch(() => props.banners, (newBanners) => {
    localBanners.value = [...newBanners].sort((a, b) => a.order - b.order)
})

// Initialize localBanners with sorted data
const localBanners = ref([...props.banners].sort((a, b) => a.order - b.order))

const form = useForm({
    title: '',
    description: '',
    image: null,
    link_url: '',
    start_date: '',
    end_date: '',
    is_active: true
})

const showModal = ref(false)
const editingBanner = ref(null)
const imagePreview = ref(null)
const toast = useToast()

const handleImageChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const openCreateModal = () => {
    editingBanner.value = null
    form.reset()
    imagePreview.value = null
    showModal.value = true
}

const openEditModal = (banner) => {
    editingBanner.value = banner
    form.title = banner.title
    form.description = banner.description
    form.link_url = banner.link_url
    form.start_date = banner.start_date
    form.end_date = banner.end_date
    form.is_active = banner.is_active
    imagePreview.value = banner.full_image_url
    showModal.value = true
}

const submit = () => {
    if (editingBanner.value) {
        form.put(route('banners.update', editingBanner.value.id), {
            onSuccess: () => {
                showModal.value = false
                router.reload()
            }
        })
    } else {
        form.post(route('banners.store'), {
            onSuccess: () => {
                showModal.value = false
                router.reload()
            }
        })
    }
}

const deleteBanner = (banner) => {
    if (confirm('Bạn có chắc chắn muốn xóa banner này?')) {
        form.delete(route('banners.destroy', banner.id), {
            onSuccess: () => {
                router.reload()
            }
        })
    }
}

const updateOrder = async (event) => {
    const newOrder = localBanners.value.map((banner, index) => ({
        id: banner.id,
        order: index + 1
    }))
    
    try {
        await axios.post(route('banners.reorder'), { orders: newOrder })
        toast.success('Cập nhật thứ tự banner thành công')
        router.reload()
    } catch (error) {
        console.error('Error updating order:', error)
        toast.error('Cập nhật thứ tự banner thất bại')
        router.reload()
    }
}

const toggleStatus = async (banner) => {
    try {
        await axios.post(route('banners.toggle', banner.id))
        toast.success('Cập nhật trạng thái banner thành công')
        router.reload()
    } catch (error) {
        toast.error(error.response?.data?.message || 'Cập nhật trạng thái banner thất bại')
        router.reload()
    }
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Banners" />
        <SectionMain>
            <CardBox class="mb-6">
                <!-- Header Section -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Quản lý Banner</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Quản lý banner của website</p>
                    </div>
                    <BaseButton
                        :icon="mdiPlus"
                        color="info"
                        @click="openCreateModal"
                        class="shadow-lg hover:shadow-xl transition-shadow"
                    >
                        Thêm Banner
                    </BaseButton>
                </div>

                <!-- Banner Grid with Drag & Drop -->
                <draggable 
                    v-model="localBanners"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                    @end="updateOrder"
                    item-key="id"
                >
                    <template #item="{ element: banner }">
                        <div class="group relative bg-white dark:bg-dark-surface rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                            <!-- Order Badge -->
                            <div class="absolute top-2 right-2 z-10">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-800/70 text-white">
                                    Thứ tự: {{ banner.order }}
                                </span>
                            </div>

                            <!-- Drag Handle -->
                            <div class="absolute top-2 left-2 cursor-move opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                <BaseButton
                                    :icon="mdiDragVertical"
                                    color="light"
                                    small
                                />
                            </div>
                            
                            <!-- Banner Image -->
                            <div class="relative h-48 overflow-hidden rounded-t-xl">
                                <img 
                                    :src="banner.full_image_url" 
                                    :alt="banner.title"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            </div>

                            <!-- Content -->
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                        {{ banner.title }}
                                    </h3>
                                    <div class="flex space-x-2">
                                        <BaseButton
                                            :icon="banner.is_active ? mdiEye : mdiEyeOff"
                                            :color="banner.is_active ? 'success' : 'light'"
                                            small
                                            @click="toggleStatus(banner)"
                                            :loading="form.processing"
                                            :disabled="form.processing"
                                            :tooltip="banner.is_active ? 'Deactivate' : 'Activate'"
                                        />
                                        <BaseButton
                                            :icon="mdiPencil"
                                            color="info"
                                            small
                                            @click="openEditModal(banner)"
                                        />
                                        <BaseButton
                                            :icon="mdiDelete"
                                            color="danger"
                                            small
                                            @click="deleteBanner(banner)"
                                        />
                                    </div>
                                </div>

                                <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-2 mb-3">
                                    {{ banner.description }}
                                </p>

                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-dark-border text-gray-600 dark:text-gray-300">
                                        {{ new Date(banner.start_date).toLocaleDateString() }}
                                    </span>
                                    <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-dark-border text-gray-600 dark:text-gray-300">
                                        {{ new Date(banner.end_date).toLocaleDateString() }}
                                    </span>
                                    <span 
                                        :class="[
                                            'px-2 py-1 rounded-full',
                                            banner.is_active 
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
                                        ]"
                                    >
                                        {{ banner.is_active ? 'Đang hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>
            </CardBox>
        </SectionMain>

        <!-- Modal section -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
                
                <!-- Modal Content -->
                <div class="relative bg-white dark:bg-dark-surface rounded-xl shadow-xl w-full max-w-md p-6 transform transition-all">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">
                        {{ editingBanner ? 'Chỉnh sửa Banner' : 'Tạo Banner mới' }}
                    </h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Title -->
                        <FormField label="Tiêu đề" class="text-gray-700 dark:text-gray-200">
                            <FormControl
                                v-model="form.title"
                                type="text"
                                required
                                class="w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-white focus:border-primary-500 dark:focus:border-primary-400"
                            />
                        </FormField>

                        <!-- Description -->
                        <FormField label="Mô tả" class="text-gray-700 dark:text-gray-200">
                            <FormControl
                                v-model="form.description"
                                type="textarea"
                                class="w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-white focus:border-primary-500 dark:focus:border-primary-400"
                            />
                        </FormField>

                        <!-- Image Upload -->
                        <FormField label="Hình ảnh" class="text-gray-700 dark:text-gray-200">
                            <input
                                type="file"
                                @change="handleImageChange"
                                accept="image/*"
                                class="hidden"
                                ref="fileInput"
                            />
                            <div class="space-y-2">
                                <!-- Image Preview -->
                                <div v-if="imagePreview || (editingBanner && editingBanner.full_image_url)" 
                                     class="relative w-full h-32 rounded-lg overflow-hidden bg-gray-100 dark:bg-dark-border">
                                    <img
                                        :src="imagePreview || (editingBanner && editingBanner.full_image_url)"
                                        class="w-full h-full object-cover"
                                        alt="Banner preview"
                                    />
                                </div>
                                <!-- Upload Button -->
                                <BaseButton
                                    :icon="mdiImage"
                                    color="info"
                                    @click="$refs.fileInput.click()"
                                    class="w-full justify-center"
                                >
                                    {{ imagePreview || (editingBanner && editingBanner.full_image_url) ? 'Đổi hình ảnh' : 'Tải hình ảnh' }}
                                </BaseButton>
                            </div>
                        </FormField>

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4">
                            <FormField label="Ngày bắt đầu" class="text-gray-700 dark:text-gray-200">
                                <FormControl
                                    v-model="form.start_date"
                                    type="date"
                                    class="w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-white focus:border-primary-500 dark:focus:border-primary-400"
                                />
                            </FormField>

                            <FormField label="Ngày kết thúc" class="text-gray-700 dark:text-gray-200">
                                <FormControl
                                    v-model="form.end_date"
                                    type="date"
                                    class="w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-white focus:border-primary-500 dark:focus:border-primary-400"
                                />
                            </FormField>
                        </div>

                        <!-- Link URL -->
                        <FormField label="Đường dẫn liên kết" class="text-gray-700 dark:text-gray-200">
                            <FormControl
                                v-model="form.link_url"
                                type="url"
                                class="w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface dark:text-white focus:border-primary-500 dark:focus:border-primary-400"
                            />
                        </FormField>

                        <!-- Active Status -->
                        <FormField class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                class="rounded border-gray-300 dark:border-dark-border text-primary-600 focus:ring-primary-500 dark:bg-dark-surface"
                            />
                            <span class="text-gray-700 dark:text-gray-200">Kích hoạt</span>
                        </FormField>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-2 mt-6">
                            <BaseButton
                                type="button"
                                color="light"
                                @click="showModal = false"
                                label="Hủy"
                                class="px-4 py-2 text-gray-700 dark:text-gray-200"
                            />
                            <BaseButton
                                type="submit"
                                color="info"
                                :loading="form.processing"
                                :label="editingBanner ? 'Cập nhật' : 'Tạo mới'"
                                class="px-4 py-2"
                            />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </LayoutAuthenticated>
</template>