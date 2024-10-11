<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import AppointmentModal from '@/Pages/Calendar/Components/AppointmentModal.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    appointments: {
        type: Array,
        default: () => []
    }
})

const page = usePage()

const showModal = ref(false)
const selectedAppointment = ref(null)

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: 'timeGridWeek', // Changed to week view
    weekends: true,
    events: computed(() => {
        return props.appointments.map(appointment => ({
            id: appointment.id,
            title: `${appointment.user?.name || 'Không xác định'} - ${appointment.appointment_type || 'Không xác định'}`,
            start: appointment.start_date,
            end: appointment.end_date,
            allDay: appointment.is_all_day,
        }))
    }),
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
    allDayText: 'Cả ngày',
    moreLinkText: 'Xem thêm',
    noEventsText: 'Không có sự kiện để hiển thị',
    slotLabelFormat: {
        hour: 'numeric',
        minute: '2-digit',
        omitZeroMinute: false,
        meridiem: 'short'
    },
    slotMinTime: '08:00:00',
    slotMaxTime: '18:00:00',
    businessHours: {
        daysOfWeek: [0, 1, 2, 3, 4, 5, 6],
        startTime: '08:00',
        endTime: '18:00',
    },
    nowIndicator: true,
    editable: true,
    selectable: true,
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventResize: handleEventResize,
})

function handleDateSelect(selectInfo) {
    showModal.value = true
    selectedAppointment.value = {
        start: selectInfo.startStr,
        end: selectInfo.endStr,
        allDay: selectInfo.allDay
    }
}

function handleEventClick(clickInfo) {
    showModal.value = true
    selectedAppointment.value = {
        ...clickInfo.event.extendedProps,
        id: clickInfo.event.id,
        start: clickInfo.event.startStr,
        end: clickInfo.event.endStr,
        allDay: clickInfo.event.allDay
    }
}

function handleEventDrop(dropInfo) {
    // Implement this function
}

function handleEventResize(resizeInfo) {
    // Implement this function
}

function closeModal() {
    showModal.value = false
    selectedAppointment.value = null
}

function saveAppointment(appointmentData) {
    const form = useForm({
        user_id: appointmentData.user_id,
        appointment_type: appointmentData.appointment_type,
        staff_id: appointmentData.staff_id,
        order_item_id: appointmentData.order_item_id,
        start_date: appointmentData.start,
        end_date: appointmentData.end,
        is_all_day: appointmentData.allDay,
        status: appointmentData.status || 'pending',
        note: appointmentData.note,
    });

    form.post(route('appointments.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            // Có thể thêm logic để cập nhật danh sách lịch hẹn ở đây
        },
    });
}

function handleAppointmentAdded() {
    // Reload the page to reflect the new appointment
    window.location.reload();
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Lịch hẹn" />
        <SectionMain>
            <FullCalendar :options="calendarOptions" class="custom-calendar" />
        </SectionMain>
        <AppointmentModal 
            :show="showModal" 
            :appointment="selectedAppointment" 
            @close="closeModal"
            @save="saveAppointment" 
            @appointmentAdded="handleAppointmentAdded"
        />
    </LayoutAuthenticated>
</template>

<style scoped>
.custom-calendar {
    height: calc(100vh - 200px); /* Điều chỉnh chiều cao tùy theo layout của bạn */
}

:deep(.fc-timegrid-slot) {
    height: 3em !important; /* Đặt chiều cao cố định cho mỗi ô thời gian */
}

:deep(.fc-timegrid-axis-cushion) {
    max-width: none; /* Cho phép nhãn thời gian hiển thị đầy đủ */
    white-space: nowrap;
}

:deep(.fc-timegrid-slot-label-cushion) {
    font-weight: bold;
    color: #555;
}

:deep(.fc-timegrid-now-indicator-line) {
    border-color: #ff0000; /* Màu đỏ cho đường chỉ thời gian hiện tại */
}

:deep(.fc-event) {
    border-radius: 4px;
    font-size: 0.9em;
}

:deep(.fc-toolbar-title) {
    font-size: 1.5em !important;
    font-weight: bold;
}

:deep(.fc-button) {
    text-transform: capitalize;
}
</style>
