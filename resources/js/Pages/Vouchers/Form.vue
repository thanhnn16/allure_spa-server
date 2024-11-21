<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import { mdiArrowLeft } from '@mdi/js'

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
    status: props.voucher?.status || 'active'
})

const submit = () => {
    if (props.voucher?.id) {
        form.put(route('vouchers.update', props.voucher.id))
    } else {
        form.post(route('vouchers.store'))
    }
}

const breadcrumbs = [
    { label: 'Trang chủ', route: 'dashboard' },
    { label: 'Quản lý Voucher', route: 'vouchers.index' },
    { label: props.voucher?.id ? 'Sửa Voucher' : 'Thêm Voucher' }
]

// Add validation rules
const rules = {
    code: 'required|min:3',
    discount_type: 'required|in:percentage,fixed',
    discount_value: 'required|numeric|min:0',
    min_order_value: 'required|numeric|min:0',
    max_discount_amount: 'required|numeric|min:0',
    start_date: 'required|date',
    end_date: 'required|date|after:start_date',
    uses_per_user: 'required|numeric|min:1'
}
</script>

<template>
    <LayoutAuthenticated>

        <Head :title="voucher?.id ? 'Sửa Voucher' : 'Thêm Voucher'" />

        <SectionMain :breadcrumbs="breadcrumbs">
            <CardBox>
                <form @submit.prevent="submit">
                    <FormField label="Mã voucher" :error="form.errors.code">
                        <FormControl v-model="form.code" type="text" placeholder="Nhập mã voucher" required />
                    </FormField>

                    <FormField label="Mô tả" :error="form.errors.description">
                        <FormControl v-model="form.description" type="textarea" placeholder="Nhập mô tả" />
                    </FormField>

                    <FormField label="Loại giảm giá" :error="form.errors.discount_type">
                        <FormControl v-model="form.discount_type" type="select" :options="[
                            { value: 'percentage', label: 'Phần trăm' },
                            { value: 'fixed', label: 'Số tiền cố định' }
                        ]" required />
                    </FormField>

                    <FormField label="Giá trị giảm" :error="form.errors.discount_value">
                        <FormControl v-model="form.discount_value" type="number" min="0"
                            :step="form.discount_type === 'percentage' ? '1' : '1000'" required />
                    </FormField>

                    <FormField label="Đơn hàng tối thiểu" :error="form.errors.min_order_value">
                        <FormControl v-model="form.min_order_value" type="number" min="0" step="1000" required />
                    </FormField>

                    <FormField label="Giảm tối đa" :error="form.errors.max_discount_amount">
                        <FormControl v-model="form.max_discount_amount" type="number" min="0" step="1000" required />
                    </FormField>

                    <FormField label="Số lần sử dụng tối đa" :error="form.errors.usage_limit" v-if="!form.is_unlimited">
                        <FormControl v-model="form.usage_limit" type="number" min="1" required />
                    </FormField>

                    <FormField label="Không giới hạn số lần sử dụng" :error="form.errors.is_unlimited">
                        <FormControl v-model="form.is_unlimited" type="checkbox" />
                    </FormField>

                    <FormField label="Số lần sử dụng cho mỗi người dùng" :error="form.errors.uses_per_user">
                        <FormControl v-model="form.uses_per_user" type="number" min="1" required />
                    </FormField>

                    <FormField label="Trạng thái" :error="form.errors.status">
                        <FormControl v-model="form.status" type="select" :options="[
                            { value: 'active', label: 'Hoạt động' },
                            { value: 'inactive', label: 'Không hoạt động' }
                        ]" required />
                    </FormField>

                    <BaseButtons>
                        <BaseButton type="submit" color="info" :label="voucher?.id ? 'Cập nhật' : 'Tạo mới'"
                            :loading="form.processing" />
                        <BaseButton :icon="mdiArrowLeft" label="Quay lại" color="white"
                            @click="$inertia.visit(route('vouchers.index'))" />
                    </BaseButtons>
                </form>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>