<template>
    <CardBoxModal :modelValue="show" @update:modelValue="$emit('update:show')" title="Tạo sản phẩm mới"
        @submit.prevent="handleSubmit" @cancel="closeModal" :isLoading="isSubmitting" :hasButton="false">
        <form @submit.prevent="handleSubmit" class="space-y-6" :class="{ 'pointer-events-none': isSubmitting }">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tên sản phẩm *</label>
                    <input v-model="form.name" name="name" :class="[
                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text',
                        validationErrors.name
                            ? 'border-red-500 dark:border-red-500'
                            : 'border-gray-300 dark:border-dark-border'
                    ]" @blur="validateField('name', form.name)" type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                    <span v-if="validationErrors.name" class="text-red-500 text-sm">{{ validationErrors.name }}</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Danh mục *</label>
                    <select v-model="form.category_id" name="category_id" :class="[
                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text',
                        validationErrors.category_id
                            ? 'border-red-500 dark:border-red-500'
                            : 'border-gray-300 dark:border-dark-border'
                    ]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                        <option value="">Chọn danh mục</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.category_name }}
                        </option>
                    </select>
                    <span v-if="validationErrors.category_id" class="text-red-500 text-sm">{{
                        validationErrors.category_id }}</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Giá *</label>
                    <input v-model="form.price" name="price" type="number" min="0" step="1000" :class="[
                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text',
                        validationErrors.price
                            ? 'border-red-500 dark:border-red-500'
                            : 'border-gray-300 dark:border-dark-border'
                    ]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                    <span v-if="validationErrors.price" class="text-red-500 text-sm">{{ validationErrors.price }}</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Số lượng *</label>
                    <input v-model="form.quantity" name="quantity" type="number" min="0" :class="[
                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text',
                        validationErrors.quantity
                            ? 'border-red-500 dark:border-red-500'
                            : 'border-gray-300 dark:border-dark-border'
                    ]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                    <span v-if="validationErrors.quantity" class="text-red-500 text-sm">{{ validationErrors.quantity
                        }}</span>
                </div>
            </div>

            <!-- Product Images -->
            <div class="py-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Hình ảnh sản phẩm</label>
                <div class="flex flex-wrap gap-4 mb-4">
                    <div v-for="(preview, index) in previews" :key="index"
                        class="relative w-24 h-24 border rounded-lg overflow-hidden">
                        <img :src="preview" class="w-full h-full object-cover">
                        <button @click.prevent="removeImage(index)"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                            <BaseIcon :path="mdiClose" size="16" />
                        </button>
                    </div>
                    <div class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-indigo-500"
                        @click="triggerFileInput">
                        <BaseIcon :path="mdiPlus" size="24" class="text-gray-400" />
                        <input type="file" ref="fileInput" class="hidden" multiple @change="handleImageUpload"
                            accept="image/*">
                    </div>
                </div>
                <p class="text-xs text-gray-500">PNG, JPG, GIF tối đa 2MB</p>
                <span v-if="validationErrors.images" class="text-red-500 text-sm">{{ validationErrors.images }}</span>
            </div>

            <!-- Product Details -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mô tả thương hiệu</label>
                    <textarea v-model="form.brand_description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Cách sử dụng</label>
                    <textarea v-model="form.usage" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Lợi ích</label>
                    <textarea v-model="form.benefits" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Thành phần chính</label>
                    <textarea v-model="form.key_ingredients" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Thành phần đầy đủ</label>
                    <textarea v-model="form.ingredients" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Hướng dẫn sử dụng</label>
                    <textarea v-model="form.directions" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Hướng dẫn bảo quản</label>
                    <textarea v-model="form.storage_instructions" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Ghi chú sản phẩm</label>
                    <textarea v-model="form.product_notes" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"></textarea>
                </div>
            </div>

            <!-- Validation Errors Summary -->
            <div v-if="Object.keys(validationErrors).length > 0" class="bg-red-50 dark:bg-red-900/20 p-4 rounded-md">
                <p class="text-red-700 dark:text-red-400 font-medium">Vui lòng sửa các lỗi sau:</p>
                <ul class="mt-2 text-sm text-red-600 dark:text-red-400">
                    <li v-for="(error, field) in validationErrors" :key="field">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <div class="flex justify-end space-x-2">
                <BaseButton type="button" label="Hủy" @click="closeModal" :disabled="isSubmitting" />
                <BaseButton type="submit" color="info" label="Tạo" :loading="isSubmitting" :disabled="isSubmitting || Object.keys(validationErrors).length > 0"
                    @click.prevent="handleSubmit" />
            </div>
        </form>
    </CardBoxModal>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { mdiClose, mdiPlus } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'

const toast = useToast()
const fileInput = ref(null)
const validationErrors = ref({})
const isSubmitting = ref(false)

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['close', 'created', 'error', 'validationFailed', 'update:show'])

const isModalActive = ref(props.show)
const previews = ref([])
const imageFiles = ref([])

const form = useForm({
    name: '',
    category_id: '',
    price: '',
    quantity: '',
    brand_description: '',
    usage: '',
    benefits: '',
    key_ingredients: '',
    ingredients: '',
    directions: '',
    storage_instructions: '',
    product_notes: '',
    images: [],
    _validate: {
        name: (value) => !!value || 'Tên sản phẩm là bắt buộc',
        category_id: (value) => !!value || 'Danh mục là bắt buộc',
        price: (value) => {
            if (!value) return 'Giá là bắt buộc'
            if (isNaN(value)) return 'Giá phải là số'
            if (value <= 0) return 'Giá phải lớn hơn 0'
            return true
        }
    }
})

const errors = ref({})
const isValid = ref(false)

const validateForm = () => {
    validationErrors.value = {}
    let isValid = true

    // Safe checks with optional chaining
    if (!form.name?.trim()) {
        validationErrors.value.name = 'Tên sản phẩm là bắt buộc'
        isValid = false
    } else if (form.name.length < 3) {
        validationErrors.value.name = 'Tên sản phẩm phải có ít nhất 3 ký tự'
        isValid = false
    }

    if (!form.category_id) {
        validationErrors.value.category_id = 'Vui lòng chọn danh mục'
        isValid = false
    }

    if (!form.price) {
        validationErrors.value.price = 'Giá sản phẩm là bắt buộc'
        isValid = false
    } else if (isNaN(Number(form.price)) || Number(form.price) <= 0) {
        validationErrors.value.price = 'Giá sản phẩm phải lớn hơn 0'
        isValid = false
    }

    if (!isValid) {
        emit('validationFailed', validationErrors.value)
    }

    return isValid
}

const triggerFileInput = () => {
    fileInput.value.click()
}

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files)
    const existingCount = imageFiles.value.length
    const totalCount = existingCount + files.length

    if (totalCount > 10) {
        toast.error('Không được upload quá 10 ảnh')
        return
    }

    files.forEach(file => {
        if (file.size > 2 * 1024 * 1024) {
            toast.error(`File ${file.name} vượt quá 2MB`)
            return
        }

        const reader = new FileReader()
        reader.onload = (e) => {
            previews.value.push(e.target.result)
        }
        reader.readAsDataURL(file)
        imageFiles.value.push(file)
    })
}

const removeImage = (index) => {
    previews.value.splice(index, 1)
    imageFiles.value.splice(index, 1)
    form.images = imageFiles.value
}

const handleSubmit = async (event) => {
    // Ngăn chặn form submission mặc định
    event?.preventDefault();
    
    // Double check để tránh multiple submissions
    if (isSubmitting.value) {
        console.log('Submission already in progress');
        return;
    }

    if (!validateForm()) {
        console.log('Form validation failed');
        return;
    }

    isSubmitting.value = true;
    console.log('Starting submission...');

    try {
        // Disable nút submit
        const submitButton = event?.target?.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        await form.post(route('products.store'), {
            preserveScroll: true,
            preserveState: false, // Thêm option này để tránh state preservation
            onSuccess: () => {
                console.log('Submission successful');
                emit('created', true);
                resetForm();
                emit('close');
                router.reload(); // Reload để đảm bảo state mới
            },
            onError: (errors) => {
                console.log('Submission failed with errors:', errors);
                validationErrors.value = errors;
                emit('validationFailed', errors);
            },
            onFinish: () => {
                console.log('Submission finished');
                isSubmitting.value = false;
                // Re-enable submit button
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    } catch (error) {
        console.error('Error during submission:', error);
        emit('error', error);
        isSubmitting.value = false;
    }
};

// Thêm method reset form riêng
const resetForm = () => {
    form.reset();
    imageFiles.value = [];
    previews.value = [];
    validationErrors.value = {};
    isSubmitting.value = false;
};

// Thêm cleanup khi component unmount
onBeforeUnmount(() => {
    isSubmitting.value = false;
});

// Đảm bảo modal đóng sẽ reset form
const closeModal = () => {
    if (!isSubmitting.value) {
        resetForm();
        emit('close');
    }
};

// Thêm watcher cho modelValue
watch(() => props.show, (newVal) => {
    if (!newVal) {
        resetForm();
    }
});

// Validate single field
const validateField = (fieldName, value) => {
    const validation = {
        name: (val) => {
            if (!val) return 'Tên sản phẩm là bắt buộc'
            if (val.length < 3) return 'Tên sản phẩm phải có ít nhất 3 ký tự'
            if (val.length > 255) return 'Tên sản phẩm không được vượt quá 255 ký tự'
            return null
        },
        category_id: (val) => {
            if (!val) return 'Danh mục là bắt buộc'
            return null
        },
        price: (val) => {
            if (!val) return 'Giá là bắt buộc'
            if (isNaN(val)) return 'Giá phải là số'
            if (parseFloat(val) <= 0) return 'Giá phải lớn hơn 0'
            if (parseFloat(val) > 1000000000) return 'Giá không được vượt quá 1 tỷ VND'
            return null
        },
        quantity: (val) => {
            if (!val) return 'Số lượng là bắt buộc'
            if (isNaN(val)) return 'Số lượng phải là số'
            if (parseInt(val) < 0) return 'Số lượng không được âm'
            if (parseInt(val) > 10000) return 'Số lượng không được vượt quá 10,000'
            return null
        }
    }

    if (validation[fieldName]) {
        const error = validation[fieldName](value)
        if (error) {
            validationErrors.value[fieldName] = error
            return false
        } else {
            delete validationErrors.value[fieldName]
            return true
        }
    }
    return true
}

// Add watchers for real-time validation
watch(() => form.name, (value) => validateField('name', value))
watch(() => form.category_id, (value) => validateField('category_id', value))
watch(() => form.price, (value) => validateField('price', value))
watch(() => form.quantity, (value) => validateField('quantity', value))
</script>
<style scoped>
.error-input {
    @apply border-red-500 focus:border-red-500 focus:ring-red-500;
}

.error-message {
    @apply text-red-500 text-sm mt-1;
}
</style>
