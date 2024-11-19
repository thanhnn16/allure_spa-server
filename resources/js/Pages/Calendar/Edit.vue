<script setup>
import { ref, computed } from 'vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import { Head, router } from '@inertiajs/vue3'
import { mdiAlert } from '@mdi/js'
import axios from 'axios'

const props = defineProps({
    appointment: {
        type: Object,
        required: true
    },
    timeSlots: {
        type: Array,
        default: () => []
    }
})

const notification = ref(null)
const isSubmitting = ref(false)

const formData = ref({
    appointment_date: props.appointment.appointment_date,
    time_slot_id: props.appointment.time_slot_id,
    note: props.appointment.note,
    slots: props.appointment.slots
})

const handleSubmit = async () => {
    try {
        isSubmitting.value = true
        const response = await axios.put(`/api/appointments/${props.appointment.id}`, formData.value)

        if (response.data.success) {
            notification.value = {
                type: 'success',
                message: 'Cập nhật lịch hẹn thành công'
            }
            // Redirect sau 1 giây
            setTimeout(() => {
                router.visit(`/appointments/${props.appointment.id}`)
            }, 1000)
        }
    } catch (error) {
        notification.value = {
            type: 'danger',
            message: error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật lịch hẹn'
        }
    } finally {
        isSubmitting.value = false
    }
}

const availableTimeSlots = computed(() => {
    return props.timeSlots.filter(slot => {
        // Nếu là time slot hiện tại của appointment, luôn cho phép chọn
        if (slot.id === props.appointment.time_slot_id) return true
        return slot.available
    })
})
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Chỉnh sửa lịch hẹn" />

        <SectionMain :breadcrumbs="[
            { label: 'Lịch hẹn', href: route('appointments.index') },
            { label: 'Chi tiết', href: `/appointments/${appointment.id}` },
            { label: 'Chỉnh sửa' }
        ]">
            <CardBox class="mb-6">
                <div class="space-y-4">
                    <h1 class="text-2xl font-bold">Chỉnh sửa lịch hẹn #{{ appointment.id }}</h1>
                    <p class="text-gray-500">Cập nhật thông tin lịch hẹn</p>
                </div>
            </CardBox>

            <!-- Notification Bar -->
            <NotificationBar v-if="notification" :color="notification.type" :icon="mdiAlert" class="mb-6">
                {{ notification.message }}
            </NotificationBar>

            <CardBox>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Ngày hẹn -->
                    <FormField label="Ngày hẹn" help="Chọn ngày cho lịch hẹn">
                        <FormControl v-model="formData.appointment_date" type="date" required />
                    </FormField>

                    <!-- Khung giờ -->
                    <FormField label="Khung giờ" help="Chọn khung giờ phù hợp">
                        <FormControl v-model="formData.time_slot_id" type="select" required>
                            <option value="">Chọn khung giờ</option>
                            <option v-for="slot in availableTimeSlots" :key="slot.id" :value="slot.id">
                                {{ slot.start_time }} - {{ slot.end_time }}
                                ({{ slot.available ? 'Còn trống' : 'Đã đầy' }})
                            </option>
                        </FormControl>
                    </FormField>

                    <!-- Số lượng slot -->
                    <FormField label="Số lượng slot" help="Chọn số lượng slot cần đặt">
                        <FormControl v-model="formData.slots" type="number" min="1" required />
                    </FormField>

                    <!-- Ghi chú -->
                    <FormField label="Ghi chú" help="Thêm ghi chú cho lịch hẹn (không bắt buộc)">
                        <FormControl v-model="formData.note" type="textarea" :rows="4" />
                    </FormField>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-2">
                        <BaseButton type="button" color="info" label="Quay lại" outline
                            @click="router.visit(`/appointments/${appointment.id}`)" />
                        <BaseButton type="submit" color="success" label="Cập nhật" :loading="isSubmitting" />
                    </div>
                </form>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template> 