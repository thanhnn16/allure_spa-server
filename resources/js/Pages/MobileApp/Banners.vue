<script setup>
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import { mdiPlus, mdiPencil, mdiDelete, mdiImage } from '@mdi/js'

const props = defineProps({
    banners: {
        type: Array,
        required: true
    }
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
</script>

<template>
    <LayoutAuthenticated>
        <SectionMain>
            <CardBox class="mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">Banner Management</h1>
                    <BaseButton
                        :icon="mdiPlus"
                        color="info"
                        @click="openCreateModal"
                    >
                        Add Banner
                    </BaseButton>
                </div>

                <!-- Banner Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="banner in banners" :key="banner.id" 
                        class="bg-white dark:bg-dark-surface rounded-lg shadow-md overflow-hidden">
                        <img :src="banner.image_url" :alt="banner.title" 
                            class="w-full h-48 object-cover">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-semibold">{{ banner.title }}</h3>
                                <div class="flex space-x-2">
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
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">
                                {{ banner.description }}
                            </p>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>{{ banner.start_date }} - {{ banner.end_date }}</span>
                                <span :class="banner.is_active ? 'text-green-500' : 'text-red-500'">
                                    {{ banner.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </CardBox>
        </SectionMain>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50"></div>
                <div class="relative bg-white dark:bg-dark-surface rounded-lg w-full max-w-md p-6">
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