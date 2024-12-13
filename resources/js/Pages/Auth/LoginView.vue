<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { mdiAccount, mdiAsterisk } from '@mdi/js'
import SectionFullScreen from '@/Components/SectionFullScreen.vue'
import CardBox from '@/Components/CardBox.vue'
import FormCheckRadio from '@/Components/FormCheckRadio.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import LayoutGuest from '@/Layouts/LayoutGuest.vue'

const props = defineProps({
    canResetPassword: Boolean,
    status: String
})

const form = useForm({
    phone_number: '',
    password: '',
    remember: true
})

const submit = () => {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <LayoutGuest>

        <Head title="Đăng nhập" />
        <SectionFullScreen v-slot="{ cardClass }" bgImg="/bg.jpg">
            <CardBox :class="cardClass" is-form @submit.prevent="submit" class="p-6">
                <div class="flex justify-center mb-6">
                    <img src="https://allurespa.com.vn/wp-content/uploads/2024/08/logo_homepage-e1723436204113.png"
                        alt="Logo" class="h-20">
                </div>
                <FormField label="Số điện thoại" help="Nhập số điện thoại">
                    <FormControl v-model="form.phone_number" :icon="mdiAccount" name="phone_number"
                        autocomplete="username" />
                </FormField>

                <FormField label="Mật khẩu" help="Nhập mật khẩu">
                    <FormControl v-model="form.password" :icon="mdiAsterisk" type="password" name="password"
                        autocomplete="current-password" />
                </FormField>

                <FormCheckRadio v-model="form.remember" name="remember" label="Ghi nhớ" :input-value="true" />

                <div v-if="form.errors.phone_number || form.errors.password"
                    class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <p v-if="form.errors.phone_number" class="mb-2">{{ form.errors.phone_number }}</p>
                    <p v-if="form.errors.password">{{ form.errors.password }}</p>
                </div>

                <template #footer>
                    <BaseButtons>
                        <BaseButton type="submit" color="info" label="Đăng nhập" class="text-black"
                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing" />
                    </BaseButtons>
                </template>
            </CardBox>
        </SectionFullScreen>
    </LayoutGuest>
</template>
