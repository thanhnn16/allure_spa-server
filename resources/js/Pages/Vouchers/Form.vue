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
    usage_limit: props.voucher?.usage_limit || '',
    start_date: props.voucher?.start_date || '',
    end_date: props.voucher?.end_date || '',
    is_unlimited: props.voucher?.is_unlimited || false,
    uses_per_user: props.voucher?.uses_per_user || 1,
    status: props.voucher?.status || 'inactive'
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
    if (props.voucher?.id) {
        form.put(route('vouchers.update', props.voucher.id))
    } else {
        form.post(route('vouchers.store'))
    }
}

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
</script>

<template>
    <LayoutAuthenticated>

        <Head :title="voucher?.id ? 'Sửa Voucher' : 'Thêm Voucher'" />

        <SectionMain :breadcrumbs="breadcrumbs">
            <CardBox class="max-w-4xl mx-auto" has-table>
                <div class="flex items-center justify-between mb-6 border-b pb-4">
                    <h1 class="text-2xl font-bold flex items-center">
                        <BaseIcon :path="mdiTicket" class="w-8 h-8 mr-2" />
                        {{ voucher?.id ? 'Sửa Voucher' : 'Thêm Voucher Mới' }}
                    </h1>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Information -->
                    <div
                        class="bg-white dark:bg-dark-surface p-6 rounded-lg shadow-sm border border-gray-100 dark:border-dark-border">
                        <div class="flex items-center mb-4">
                            <BaseIcon :path="mdiInformation" class="w-5 h-5 mr-2 text-blue-500" />
                            <h2 class="text-lg font-semibold section-title">Thông tin cơ bản</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormField label="Mã voucher" :error="form.errors.code">
                                <FormControl v-model="form.code" type="text" class="uppercase"
                                    placeholder="VD: SUMMER2024" :error="form.errors.code" required />
                                <p class="text-xs text-gray-500 mt-1">
                                    Mã voucher phải là duy nhất và viết liền không dấu
                                </p>
                            </FormField>

                            <FormField label="Trạng thái" :error="form.errors.status">
                                <select 
                                    v-model="form.status"
                                    class="w-full form-control border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"
                                    :disabled="!canChangeStatus"
                                    required
                                >
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Không hoạt động</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-dark-muted mt-1">
                                    {{ !canChangeStatus && props.voucher?.status === 'inactive'
                                        ? 'Không thể chuyển trạng thái từ không hoạt động sang hoạt động'
                                        : 'Chọn trạng thái hoạt động để voucher có thể được sử dụng' }}
                                </p>
                            </FormField>
                        </div>

                        <FormField label="Mô tả" :error="form.errors.description" class="mt-4">
                            <FormControl v-model="form.description" type="textarea"
                                placeholder="Nhập mô tả chi tiết về voucher" :error="form.errors.description"
                                rows="3" />
                        </FormField>
                    </div>

                    <!-- Discount Settings -->
                    <div
                        class="bg-white dark:bg-dark-surface p-6 rounded-lg shadow-sm border border-gray-100 dark:border-dark-border">
                        <div class="flex items-center mb-4">
                            <BaseIcon :path="mdiPercent" class="w-5 h-5 mr-2 text-green-500" />
                            <h2 class="text-lg font-semibold section-title">Cài đặt giảm giá</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormField label="Loại giảm giá" :error="form.errors.discount_type">
                                <select 
                                    v-model="form.discount_type"
                                    class="w-full form-control border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-dark-surface dark:border-dark-border dark:text-dark-text"
                                    required
                                >
                                    <option value="percentage">Phần trăm (%)</option>
                                    <option value="fixed">Số tiền cố định</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ form.discount_type === 'percentage' ?
                                        'Giảm giá theo phần trăm (tối đa 100%)' :
                                        'Giảm giá theo số tiền cố định' }}
                                </p>
                            </FormField>

                            <FormField label="Giá trị giảm" :error="form.errors.discount_value">
                                <div class="relative">
                                    <FormControl v-model="form.discount_value" type="number" :min="0"
                                        :max="form.discount_type === 'percentage' ? 100 : null"
                                        :step="form.discount_type === 'percentage' ? '1' : '1000'"
                                        :error="form.errors.discount_value" @input="validateDiscountValue" required />
                                    <span class="absolute right-3 top-2 text-gray-500">
                                        {{ form.discount_type === 'percentage' ? '%' : 'đ' }}
                                    </span>
                                </div>
                            </FormField>

                            <FormField label="Đơn hàng tối thiểu" :error="form.errors.min_order_value">
                                <div class="relative">
                                    <FormControl v-model="form.min_order_value" type="number" min="0" step="1000"
                                        :error="form.errors.min_order_value" required />
                                    <span class="absolute right-3 top-2 text-gray-500">đ</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Giá trị đơn hàng tối thiểu để áp dụng voucher
                                </p>
                            </FormField>

                            <FormField label="Giảm tối đa" :error="form.errors.max_discount_amount">
                                <div class="relative">
                                    <FormControl v-model="form.max_discount_amount" type="number" min="0" step="1000"
                                        :error="form.errors.max_discount_amount" required />
                                    <span class="absolute right-3 top-2 text-gray-500">đ</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Số tiền giảm tối đa cho mỗi đơn hàng
                                </p>
                            </FormField>
                        </div>
                    </div>

                    <!-- Usage Limits -->
                    <div
                        class="bg-white dark:bg-dark-surface p-6 rounded-lg shadow-sm border border-gray-100 dark:border-dark-border">
                        <div class="flex items-center mb-4">
                            <BaseIcon :path="mdiTicket" class="w-5 h-5 mr-2 text-purple-500" />
                            <h2 class="text-lg font-semibold section-title">Giới hạn sử dụng</h2>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="isUnlimitedRef"
                                        class="form-checkbox h-5 w-5 text-primary-600 transition duration-150 ease-in-out dark:bg-dark-surface dark:border-dark-border dark:checked:bg-primary-600">
                                    <span class="ml-2 text-gray-700 dark:text-dark-text">
                                        Không giới hạn tổng số lần sử dụng
                                    </span>
                                </label>
                            </div>

                            <div v-if="!isUnlimitedRef" class="transition-all duration-200">
                                <FormField label="Số lần sử dụng tối đa" :error="form.errors.usage_limit">
                                    <FormControl v-model="form.usage_limit" type="number" min="1"
                                        placeholder="Nhập tổng số lần sử dụng tối đa của voucher"
                                        :error="form.errors.usage_limit" required />
                                    <p class="text-xs text-gray-500 dark:text-dark-muted mt-1">
                                        Tổng số lần voucher có thể được sử dụng bởi tất cả người dùng
                                    </p>
                                </FormField>
                            </div>

                            <div class="transition-all duration-200">
                                <FormField label="Số lần sử dụng cho mỗi người dùng" :error="form.errors.uses_per_user">
                                    <FormControl v-model="form.uses_per_user" type="number" min="1"
                                        placeholder="Nhập số lần sử dụng ti đa cho mỗi người dùng"
                                        :error="form.errors.uses_per_user" required />
                                    <p class="text-xs text-gray-500 dark:text-dark-muted mt-1">
                                        Số lần tối đa mỗi người dùng có thể sử dụng voucher này
                                    </p>
                                </FormField>
                            </div>
                        </div>
                    </div>

                    <!-- Time Settings -->
                    <div
                        class="bg-white dark:bg-dark-surface p-6 rounded-lg shadow-sm border border-gray-100 dark:border-dark-border">
                        <div class="flex items-center mb-4">
                            <BaseIcon :path="mdiCalendar" class="w-5 h-5 mr-2 text-orange-500" />
                            <h2 class="text-lg font-semibold section-title">Thời gian hiệu lực</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormField label="Ngày bắt đầu" :error="form.errors.start_date">
                                <FormControl v-model="form.start_date" type="datetime-local"
                                    :error="form.errors.start_date" required />
                            </FormField>

                            <FormField label="Ngày kết thúc" :error="form.errors.end_date">
                                <FormControl v-model="form.end_date" type="datetime-local" :min="form.start_date"
                                    :error="form.errors.end_date" required />
                            </FormField>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4 pt-6 border-t">
                        <BaseButton :icon="mdiArrowLeft" label="Quay lại" color="white"
                            @click="$inertia.visit(route('vouchers.index'))" />
                        <BaseButton type="submit" color="info" :label="voucher?.id ? 'Cập nhật' : 'Tạo mới'"
                            :loading="form.processing" />
                    </div>
                </form>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
.form-section {
    @apply bg-white dark:bg-dark-surface p-6 rounded-lg shadow-sm border border-gray-100 dark:border-dark-border transition-all duration-200;
}

.form-section:hover {
    @apply shadow-md;
}

.section-title {
    @apply text-lg font-semibold text-gray-700 dark:text-dark-text flex items-center;
}

:deep(.form-control) {
    @apply dark:bg-dark-surface dark:border-dark-border dark:text-dark-text transition-colors duration-200;
}

:deep(.form-label) {
    @apply dark:text-dark-text transition-colors duration-200;
}

:deep(.form-checkbox) {
    @apply rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-dark-border dark:bg-dark-surface dark:checked:bg-primary-600 transition-colors duration-200;
}

:deep(.form-control:disabled) {
    @apply opacity-60 cursor-not-allowed bg-gray-100 dark:bg-dark-surface/60;
}

/* Thêm style cho select disabled */
:deep(select:disabled) {
    @apply opacity-60 cursor-not-allowed bg-gray-100 dark:bg-dark-surface/60;
}
</style>