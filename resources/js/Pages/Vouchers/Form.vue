<script setup>
import { ref, watch, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { mdiArrowLeft, mdiTicket, mdiCalendar, mdiPercent, mdiCurrencyUsd, mdiInformation } from '@mdi/js'

const props = defineProps({
    voucher: {
        type: Object,
        default: () => ({})
    }
})

const form = useForm({
    code: props.voucher?.code || '',
    description: props.voucher?.description || '',
    discount_type: props.voucher?.discount_type || 'percentage',
    discount_value: props.voucher?.discount_value || '',
    min_order_value: props.voucher?.min_order_value || '',
    max_discount_amount: props.voucher?.max_discount_amount || '',
    start_date: props.voucher?.start_date || '',
    end_date: props.voucher?.end_date || '',
    is_unlimited: props.voucher?.is_unlimited || false,
    usage_limit: props.voucher?.usage_limit || 1,
    uses_per_user: props.voucher?.uses_per_user || 1,
    status: props.voucher?.status || 'active'
})

const canChangeStatus = computed(() => {
    const formStatus = typeof form.status === 'object' ? form.status.value : form.status

    console.log('canChangeStatus check:', {
        voucherId: props.voucher?.id,
        currentStatus: props.voucher?.status,
        formStatus: formStatus,
        result: !props.voucher?.id || props.voucher.status === 'active'
    })

    if (!props.voucher?.id) return true

    if (props.voucher.status === 'inactive' && formStatus === 'active') return false

    return true
})

watch(() => form.status, (newValue, oldValue) => {
    const currentStatus = typeof newValue === 'object' ? newValue.value : newValue
    const previousStatus = typeof oldValue === 'object' ? oldValue.value : oldValue

    console.log('Status watch triggered:', {
        currentStatus,
        previousStatus,
        canChange: canChangeStatus.value,
        voucherStatus: props.voucher?.status
    })

    if (!canChangeStatus.value && currentStatus === 'active') {
        console.log('Preventing status change to active')
        form.status = 'inactive'
    }
}, { immediate: true })

const isUnlimitedRef = ref(props.voucher?.is_unlimited || false)

watch(() => form.discount_type, (newType) => {
    form.discount_value = ''
})

watch(isUnlimitedRef, (newValue) => {
    form.is_unlimited = newValue
    if (newValue) {
        form.usage_limit = null
    } else {
        form.usage_limit = 1
    }
})

const validateDiscountValue = () => {
    if (form.discount_type === 'percentage' && form.discount_value > 100) {
        form.discount_value = 100
    }
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(value)
}

const submit = () => {
    validateDiscountValue()
    
    console.log('Submitting form with data:', {
        ...form,
        discount_value: Number(form.discount_value),
        min_order_value: Number(form.min_order_value || 0),
        max_discount_amount: Number(form.max_discount_amount || 0),
        usage_limit: form.is_unlimited ? null : Number(form.usage_limit),
        uses_per_user: Number(form.uses_per_user)
    })

    const formData = {
        ...form,
        discount_value: Number(form.discount_value),
        min_order_value: Number(form.min_order_value || 0),
        max_discount_amount: Number(form.max_discount_amount || 0),
        usage_limit: form.is_unlimited ? null : Number(form.usage_limit),
        uses_per_user: Number(form.uses_per_user)
    }

    if (props.voucher?.id) {
        console.log('Updating voucher:', {
            id: props.voucher.id,
            route: route('vouchers.update', props.voucher.id),
            method: 'PUT'
        })
        form.put(route('vouchers.update', props.voucher.id), formData)
    } else {
        console.log('Creating new voucher:', {
            route: route('vouchers.store'),
            method: 'POST'
        })
        form.post(route('vouchers.store'), formData)
    }
}

watch(() => form.errors, (newErrors) => {
    if (Object.keys(newErrors).length > 0) {
        console.error('Form validation errors:', newErrors)
    }
}, { deep: true })

watch(() => form.processing, (isProcessing) => {
    console.log('Form processing state:', isProcessing)
})

const breadcrumbs = [
    {
        label: 'Trang chủ',
        route: 'dashboard',
        href: route('dashboard')
    },
    {
        label: 'Quản lý Voucher',
        route: 'vouchers.index',
        href: route('vouchers.index')
    },
    {
        label: props.voucher?.id ? 'Sửa Voucher' : 'Thêm Voucher',
    }
]

// Thêm computed properties để hiển thị preview
const discountPreview = computed(() => {
    if (!form.discount_value) return '';
    if (form.discount_type === 'percentage') {
        return `${form.discount_value}%`;
    }
    return formatCurrency(form.discount_value);
});

const isValidDateRange = computed(() => {
    if (!form.start_date || !form.end_date) return true;
    return new Date(form.start_date) <= new Date(form.end_date);
});
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="voucher?.id ? 'Sửa Voucher' : 'Thêm Voucher'" />

        <SectionMain :breadcrumbs="breadcrumbs">
            <CardBox class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center">
                        <BaseIcon :path="mdiTicket" class="w-6 h-6 mr-2" />
                        {{ voucher?.id ? 'Sửa Voucher' : 'Thêm Voucher Mới' }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Điền thông tin chi tiết để {{ voucher?.id ? 'cập nhật' : 'tạo' }} voucher
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Thông tin cơ bản -->
                    <div class="bg-gray-50 dark:bg-slate-800/50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-4">Thông tin cơ bản</h3>
                        
                        <!-- Mã voucher -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Mã voucher <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <input v-model="form.code" type="text" required 
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                    dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                    focus:ring-blue-500 pr-10"
                                    :class="{ 'border-red-500': form.errors.code }">
                                <BaseIcon :path="mdiTicket" 
                                    class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" />
                            </div>
                            <p v-if="form.errors.code" class="mt-1 text-sm text-red-500">
                                {{ form.errors.code }}
                            </p>
                        </div>

                        <!-- Mô tả -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Mô tả
                            </label>
                            <textarea v-model="form.description" rows="2"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 
                                dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                focus:ring-blue-500"
                                placeholder="Nhập mô tả về voucher (không bắt buộc)">
                            </textarea>
                        </div>
                    </div>

                    <!-- Giá trị giảm giá -->
                    <div class="bg-gray-50 dark:bg-slate-800/50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-4">Giá trị giảm giá</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Loại giảm giá -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Loại giảm giá <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative">
                                    <select v-model="form.discount_type" required 
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                        dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                        focus:ring-blue-500 pr-10">
                                        <option value="percentage">Phần trăm</option>
                                        <option value="fixed">Số tiền cố định</option>
                                    </select>
                                    <BaseIcon :path="form.discount_type === 'percentage' ? mdiPercent : mdiCurrencyUsd" 
                                        class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" />
                                </div>
                            </div>

                            <!-- Giá trị giảm -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Giá trị giảm <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative">
                                    <input v-model.number="form.discount_value" type="number" required
                                        min="0" :max="form.discount_type === 'percentage' ? 100 : null"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                        dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                        focus:ring-blue-500 pr-10">
                                    <span class="absolute right-3 top-2 text-gray-500 dark:text-gray-400">
                                        {{ form.discount_type === 'percentage' ? '%' : 'đ' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Giảm: {{ discountPreview }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Đơn hàng tối thiểu -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Giá trị đơn hàng tối thiểu
                                </label>
                                <div class="mt-1 relative">
                                    <input v-model.number="form.min_order_value" type="number" min="0"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                        dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                        focus:ring-blue-500 pr-10">
                                    <span class="absolute right-3 top-2 text-gray-500 dark:text-gray-400">đ</span>
                                </div>
                            </div>

                            <!-- Giảm giá tối đa -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Giảm giá tối đa
                                </label>
                                <div class="mt-1 relative">
                                    <input v-model.number="form.max_discount_amount" type="number" min="0"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                        dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                        focus:ring-blue-500 pr-10">
                                    <span class="absolute right-3 top-2 text-gray-500 dark:text-gray-400">đ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thời gian và giới hạn sử dụng -->
                    <div class="bg-gray-50 dark:bg-slate-800/50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-4">Thời gian và giới hạn sử dụng</h3>

                        <!-- Thời gian -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ngày bắt đầu <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative">
                                    <input v-model="form.start_date" type="date" required 
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                        dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                        focus:ring-blue-500 pr-10">
                                    <BaseIcon :path="mdiCalendar" 
                                        class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ngày kết thúc <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative">
                                    <input v-model="form.end_date" type="date" required 
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 
                                        dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                        focus:ring-blue-500 pr-10"
                                        :class="{ 'border-red-500': !isValidDateRange }">
                                    <BaseIcon :path="mdiCalendar" 
                                        class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" />
                                </div>
                                <p v-if="!isValidDateRange" class="mt-1 text-sm text-red-500">
                                    Ngày kết thúc phải sau ngày bắt đầu
                                </p>
                            </div>
                        </div>

                        <!-- Giới hạn sử dụng -->
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" v-model="isUnlimitedRef"
                                    id="is_unlimited" class="rounded border-gray-300 dark:border-gray-600 
                                    text-blue-600 focus:ring-blue-500">
                                <label for="is_unlimited"
                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Không giới hạn số lần sử dụng
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Chỉ hiển thị ô Tổng số lần sử dụng khi không tích vào Không giới hạn -->
                                <div v-if="!form.is_unlimited">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tổng số lần sử dụng <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model.number="form.usage_limit" type="number" required
                                        min="1" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 
                                        dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                        focus:ring-blue-500">
                                </div>

                                <!-- Luôn hiển thị ô Số lần sử dụng cho mỗi người dùng -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Số lần sử dụng cho mỗi người dùng <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model.number="form.uses_per_user" type="number" required
                                        min="1" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 
                                        dark:bg-slate-800 dark:text-white focus:border-blue-500 
                                        focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3 pt-6">
                        <BaseButton type="button" label="Quay lại" color="white"
                            @click="$inertia.visit(route('vouchers.index'))" />
                        <BaseButton type="submit" :label="voucher?.id ? 'Cập nhật' : 'Tạo mới'"
                            color="info" :loading="form.processing" />
                    </div>
                </form>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
.form-section {
    @apply transition-all duration-200;
}

.form-section:hover {
    @apply shadow-md;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    @apply dark:invert;
}
</style>