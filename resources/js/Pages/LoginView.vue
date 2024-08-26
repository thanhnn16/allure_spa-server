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

const form = reactive({
    login: 'john.doe',
    pass: 'highly-secure-password-fYjUw-',
    remember: true
})

const submit = () => {
    console.log('submit with: ', form)
    router.post('/login', form)
}
</script>

<template>
    <LayoutGuest>
        <Head title="Đăng nhập"/>
        <SectionFullScreen v-slot="{ cardClass }" bg="purplePink">
            <CardBox :class="cardClass" is-form @submit.prevent="submit">
                <FormField label="Login" help="Please enter your login">
                    <FormControl
                        v-model="form.login"
                        :icon="mdiAccount"
                        name="login"
                        autocomplete="username"
                    />
                </FormField>

                <FormField label="Password" help="Please enter your password">
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
                    label="Remember"
                    :input-value="true"
                />

                <template #footer>
                    <BaseButtons>
                        <BaseButton type="submit" color="info" label="Login"/>
                        <BaseButton color="info" outline label="Back"/>
                    </BaseButtons>
                </template>
            </CardBox>
        </SectionFullScreen>
    </LayoutGuest>
</template>
