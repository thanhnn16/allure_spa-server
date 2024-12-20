<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import { mdiClose } from '@mdi/js'

const props = defineProps({
    modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'staff-added'])

const form = useForm({
    full_name: '',
    phone_number: '',
    email: '',
    gender: '',
    date_of_birth: '',
    staff_detail: {
        position: '',
        department: '',
        hire_date: ''
    }
})

const submit = () => {
    form.post(route('staff.store'), {
        onSuccess: () => {
            form.reset()
            emit('update:modelValue', false)
            emit('staff-added')
        }
    })
}
</script>

<template>
    <CardBox v-if="modelValue" is-modal :icon="mdiClose" @close="emit('update:modelValue', false)" title="Thêm nhân viên mới"
        class="shadow-lg w-full md:w-2/3 lg:w-1/2 xl:w-2/5 z-50">
        <form @submit.prevent="submit">
            <FormField label="Họ và tên" :class="{ 'text-red-400': form.errors.full_name }">
                <FormControl v-model="form.full_name" type="text" placeholder="Nhập họ và tên" />
                <div class="text-red-400" v-if="form.errors.full_name">{{ form.errors.full_name }}</div>
            </FormField>

            <FormField label="Số điện thoại" :class="{ 'text-red-400': form.errors.phone_number }">
                <FormControl v-model="form.phone_number" type="text" placeholder="Nhập số điện thoại" />
                <div class="text-red-400" v-if="form.errors.phone_number">{{ form.errors.phone_number }}</div>
            </FormField>

            <FormField label="Email" :class="{ 'text-red-400': form.errors.email }">
                <FormControl v-model="form.email" type="email" placeholder="Nhập email" />
                <div class="text-red-400" v-if="form.errors.email">{{ form.errors.email }}</div>
            </FormField>

            <FormField label="Giới tính" :class="{ 'text-red-400': form.errors.gender }">
                <select v-model="form.gender" class="w-full px-4 py-2 border rounded-md dark:bg-slate-800 
                    dark:border-slate-700 dark:text-slate-300">
                    <option value="">Chọn giới tính</option>
                    <option value="male">Nam</option>
                    <option value="female">Nữ</option>
                    <option value="other">Khác</option>
                </select>
                <div class="text-red-400" v-if="form.errors.gender">{{ form.errors.gender }}</div>
            </FormField>

            <FormField label="Ngày sinh" :class="{ 'text-red-400': form.errors.date_of_birth }">
                <FormControl v-model="form.date_of_birth" type="date" />
                <div class="text-red-400" v-if="form.errors.date_of_birth">{{ form.errors.date_of_birth }}</div>
            </FormField>

            <FormField label="Chức vụ">
                <FormControl v-model="form.staff_detail.position" type="text" placeholder="Nhập chức vụ" />
            </FormField>

            <FormField label="Phòng ban">
                <FormControl v-model="form.staff_detail.department" type="text" placeholder="Nhập phòng ban" />
            </FormField>

            <FormField label="Ngày vào làm">
                <FormControl v-model="form.staff_detail.hire_date" type="date" />
            </FormField>

            <BaseButtons>
                <BaseButton type="submit" color="info" label="Thêm nhân viên" :loading="form.processing" />
                <BaseButton type="button" color="danger" label="Hủy" @click="emit('update:modelValue', false)" />
            </BaseButtons>
        </form>
    </CardBox>
</template> 