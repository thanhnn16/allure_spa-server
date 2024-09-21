<script setup>
import {reactive} from 'vue'
import {Head} from '@inertiajs/vue3'
import {router} from '@inertiajs/vue3'
import {mdiAccount, mdiAsterisk} from '@mdi/js'
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
    phone_number: '0346542636',
    password: '123456',
    remember: true
})

const submit = () => {
    console.log('submit with: ', form)
    router.post('/login', form);
}

</script>

<template>
    <LayoutGuest>
        <Head title="Đăng nhập"/>
        <SectionFullScreen v-slot="{ cardClass }" bg="purplePink">
            <CardBox :class="cardClass" is-form @submit.prevent="submit">
                <FormField label="Số điện thoại" help="Nhập số điện thoại">
                    <FormControl
                        v-model="form.login"
                        :icon="mdiAccount"
                        name="phone_number"
                        autocomplete="username"
                    />
                </FormField>

                <FormField label="Mật khẩu" help="Nhập mật khẩu">
                    <FormControl
                        v-model="form.pass"
                        :icon="mdiAsterisk"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                    />
                </FormField>

                <FormCheckRadio
                    v-model="form.remember"
                    name="remember"
                    label="Ghi nhớ"
                    :input-value="true"
                />

                <div v-if="Object.keys(props.errors).length" class="alert alert-danger">
                    <ul>
                        <li v-for="(error, key) in props.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <template #footer>
                    <BaseButtons>
                        <BaseButton type="submit" color="info" label="Đăng nhập"/>
                    </BaseButtons>
                </template>
            </CardBox>
        </SectionFullScreen>
    </LayoutGuest>
</template>
