<script setup>
import { ref, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import { mdiPlus, mdiPencil, mdiDelete, mdiImage, mdiDragVertical, mdiEye, mdiEyeOff } from '@mdi/js'
import draggable from 'vuedraggable'

const props = defineProps({
    banners: {
        type: Array,
        required: true
    }
})

// Create a local copy of banners
const localBanners = ref([...props.banners])

// Update local banners when props change
watch(() => props.banners, (newBanners) => {
    localBanners.value = [...newBanners]
})

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
    imagePreview.value = banner.image_url
    showModal.value = true
}

const submit = () => {
    if (editingBanner.value) {
        form.put(route('banners.update', editingBanner.value.id), {
            onSuccess: () => {
                showModal.value = false
            }
        })
    } else {
        form.post(route('banners.store'), {
            onSuccess: () => {
                showModal.value = false
            }
        })
    }
}

const deleteBanner = (banner) => {
    if (confirm('Are you sure you want to delete this banner?')) {
        form.delete(route('banners.destroy', banner.id))
    }
}

const updateOrder = async (event) => {
    const newOrder = localBanners.value.map((banner, index) => ({
        id: banner.id,
        order: index + 1
    }))
    
    try {
        await axios.post(route('banners.reorder'), { orders: newOrder })
    } catch (error) {
        console.error('Error updating order:', error)
    }
}

const toggleStatus = async (banner) => {
    form.put(route('banners.update', banner.id), {
        ...banner,
        is_active: !banner.is_active
    })
}
</script>

<template>
    <LayoutAuthenticated>
        <SectionMain>
            <CardBox class="mb-6">
                <!-- Header Section -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Banner Management</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your website banners</p>
                    </div>
                    <BaseButton
                        :icon="mdiPlus"
                        color="info"
                        @click="openCreateModal"
                        class="shadow-lg hover:shadow-xl transition-shadow"
                    >
                        Add Banner
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
                            <!-- Drag Handle -->
                            <div class="absolute top-2 left-2 cursor-move opacity-0 group-hover:opacity-100 transition-opacity">
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
                                        {{ banner.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>
            </CardBox>
        </SectionMain>

        <!-- Modal with improved styling -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
                <div class="relative bg-white dark:bg-dark-surface rounded-xl shadow-xl w-full max-w-md p-6 transform transition-all">
                    <h2 class="text-xl font-semibold mb-4">
                        {{ editingBanner ? 'Edit Banner' : 'Create Banner' }}
                    </h2>

                    <form @submit.prevent="submit">
                        <FormField label="Title">
                            <FormControl
                                v-model="form.title"
                                type="text"
                                required
                            />
                        </FormField>

                        <FormField label="Description">
                            <FormControl
                                v-model="form.description"
                                type="textarea"
                            />
                        </FormField>

                        <FormField label="Image">
                            <input
                                type="file"
                                @change="handleImageChange"
                                accept="image/*"
                                class="hidden"
                                ref="fileInput"
                            />
                            <div class="relative">
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    class="w-full h-32 object-cover rounded-lg mb-2"
                                />
                                <BaseButton
                                    :icon="mdiImage"
                                    color="info"
                                    @click="$refs.fileInput.click()"
                                >
                                    {{ imagePreview ? 'Change Image' : 'Upload Image' }}
                                </BaseButton>
                            </div>
                        </FormField>

                        <div class="grid grid-cols-2 gap-4">
                            <FormField label="Start Date">
                                <FormControl
                                    v-model="form.start_date"
                                    type="date"
                                />
                            </FormField>

                            <FormField label="End Date">
                                <FormControl
                                    v-model="form.end_date"
                                    type="date"
                                />
                            </FormField>
                        </div>

                        <FormField label="Link URL">
                            <FormControl
                                v-model="form.link_url"
                                type="url"
                            />
                        </FormField>

                        <FormField>
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="mr-2"
                                />
                                <span>Active</span>
                            </div>
                        </FormField>

                        <div class="flex justify-end space-x-2 mt-6">
                            <BaseButton
                                type="button"
                                color="light"
                                @click="showModal = false"
                            >
                                Cancel
                            </BaseButton>
                            <BaseButton
                                type="submit"
                                color="info"
                                :loading="form.processing"
                            >
                                {{ editingBanner ? 'Update' : 'Create' }}
                            </BaseButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </LayoutAuthenticated>
</template>