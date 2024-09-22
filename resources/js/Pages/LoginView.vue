<script setup>
import { reactive } from 'vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
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
    errors: Object,
    auth: Object,
    ziggy: Object,
    canResetPassword: Boolean,
    status: String
})

const form = reactive({
    phone_number: '',
    password: '',
    remember: true
})

const submit = () => {
    router.post('/login', form);
}

</script>

<template>
    <LayoutGuest>

        <Head title="Đăng nhập" />
        <SectionFullScreen v-slot="{ cardClass }" bg="purplePink">
            <CardBox :class="cardClass" is-form @submit.prevent="submit">
                <div class="flex justify-center mb-6">
                    <img src="https://allurespa.com.vn/wp-content/uploads/2024/08/logo_homepage-e1723436204113.png"
                        alt="Logo" class="h-32">
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

                <div v-if="errors.phone_number || errors.details"
                    class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <p v-if="errors.phone_number" class="mb-2">{{ errors.phone_number }}</p>
                    <p v-if="errors.details">{{ errors.details }}</p>
                </div>

                <template #footer>
                    <BaseButtons>
                        <BaseButton type="submit" color="info" label="Đăng nhập" class="text-black" />
                    </BaseButtons>
                </template>
            </CardBox>
        </SectionFullScreen>
    </LayoutGuest>
</template>
