<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import { Head, useForm } from "@inertiajs/vue3"
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AppointmentModal from './Components/AppointmentModal.vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    appointments: Array
})

const appointments = ref(props.appointments)

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    weekends: true,
    events: computed(() => appointments.value.map(appointment => ({
        id: appointment.id,
        title: `${appointment.user.name} - ${appointment.appointmentType.type_name}`,
        start: appointment.start_date,
        end: appointment.end_date,
        allDay: appointment.is_all_day,
    }))),
    locale: 'vi',
    firstDay: 1,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },
    buttonText: {
        today: 'Hôm nay',
        month: 'Tháng',
        week: 'Tuần',
        day: 'Ngày',
        list: 'Danh sách'
    },
    nowIndicator: true,
    editable: true,
    selectable: true,
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventResize: handleEventResize,
})

const form = useForm({
    id: null,
    user_id: '', // Thêm trường này
    appointment_type_id: '',
    staff_id: null,
    order_item_id: '',
    start_date: '',
    end_date: '',
    is_all_day: false,
    status: 'pending',
    note: '',
})

const showModal = ref(false)
const selectedAppointment = ref(null)

onMounted(() => {
    fetchAppointments()
})

function fetchAppointments() {
    axios.get('/api/appointments').then(response => {
        appointments.value = response.data
    })
}

function handleDateSelect(selectInfo) {
    selectedAppointment.value = {
        start_date: selectInfo.startStr,
        end_date: selectInfo.endStr,
        is_all_day: selectInfo.allDay,
    }
    showModal.value = true
}

function handleEventClick(clickInfo) {
    selectedAppointment.value = {
        id: clickInfo.event.id,
        title: clickInfo.event.title,
        start_date: clickInfo.event.startStr,
        end_date: clickInfo.event.endStr,
        is_all_day: clickInfo.event.allDay,
    }
    showModal.value = true
}

function handleEventDrop(dropInfo) {
    updateAppointment({
        id: dropInfo.event.id,
        start: dropInfo.event.startStr,
        end: dropInfo.event.endStr,
        allDay: dropInfo.event.allDay,
    })
}

function handleEventResize(resizeInfo) {
    updateAppointment({
        id: resizeInfo.event.id,
        start: resizeInfo.event.startStr,
        end: resizeInfo.event.endStr,
    })
}

function updateAppointment(data) {
    axios.put(`/api/appointments/${data.id}`, {
        start_date: data.start,
        end_date: data.end,
        is_all_day: data.allDay,
    }).then(() => {
        fetchAppointments()
    })
}

function submitForm() {
    if (form.id) {
        form.put(`/api/appointments/${form.id}`).then(() => {
            fetchAppointments()
        })
    } else {
        form.post('/api/appointments').then(() => {
            fetchAppointments()
        })
    }
}

function deleteAppointment(id) {
    axios.delete(`/api/appointments/${id}`).then(() => {
        fetchAppointments()
    })
}

function closeModal() {
    showModal.value = false
    selectedAppointment.value = null
}

function saveAppointment(appointmentData) {
    if (appointmentData.id) {
        updateAppointment(appointmentData)
    } else {
        createAppointment(appointmentData)
    }
    closeModal()
}

function createAppointment(data) {
    axios.post('/api/appointments', data).then(() => {
        fetchAppointments()
    })
}
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Lịch hẹn" />
        <SectionMain>
            <FullCalendar :options="calendarOptions" />
        </SectionMain>
        <AppointmentModal :show="showModal" :appointment="selectedAppointment" @close="closeModal"
            @save="saveAppointment" />
        <!-- Thêm modal cho việc tạo/chỉnh sửa lịch hẹn -->
    </LayoutAuthenticated>
</template>
