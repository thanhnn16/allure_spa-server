<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import { Head, useForm } from "@inertiajs/vue3";
import { ref, onMounted } from 'vue'
import axios from 'axios'

const appointments = ref([])
const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    weekends: true,
    events: appointments,
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

onMounted(() => {
    fetchAppointments()
})

function fetchAppointments() {
    axios.get('/api/appointments').then(response => {
        appointments.value = response.data.map(appointment => ({
            id: appointment.id,
            title: appointment.title,
            start: appointment.start_date,
            end: appointment.end_date,
            allDay: appointment.is_all_day,
        }))
    })
}

function handleDateSelect(selectInfo) {
    form.reset()
    form.start_date = selectInfo.startStr
    form.end_date = selectInfo.endStr
    form.is_all_day = selectInfo.allDay
    // Hiển thị modal tạo lịch hẹn mới, cho phép chọn user_id
}

function handleEventClick(clickInfo) {
    form.id = clickInfo.event.id
    form.title = clickInfo.event.title
    form.start = clickInfo.event.startStr
    form.end = clickInfo.event.endStr
    form.allDay = clickInfo.event.allDay
    // Hiển thị modal chỉnh sửa lịch hẹn
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
    axios.put(`/api/appointments/${data.id}`, data).then(() => {
        fetchAppointments()
    })
}

function submitForm() {
    if (form.id) {
        form.put(`/api/appointments/${form.id}`).then(() => {
            fetchAppointments()
            // Đóng modal
        })
    } else {
        form.post('/api/appointments').then(() => {
            fetchAppointments()
            // Đóng modal
        })
    }
}

function deleteAppointment() {
    if (form.id) {
        axios.delete(`/api/appointments/${form.id}`).then(() => {
            fetchAppointments()
            // Đóng modal
        })
    }
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Lịch hẹn" />
        <SectionMain>
            <FullCalendar :options="calendarOptions" />
        </SectionMain>
        <!-- Thêm modal cho việc tạo/chỉnh sửa lịch hẹn -->
    </LayoutAuthenticated>
</template>
