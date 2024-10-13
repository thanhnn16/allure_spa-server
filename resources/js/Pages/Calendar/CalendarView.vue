<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import AppointmentModal from '@/Pages/Calendar/Components/AddAppointmentModal.vue'
import { useForm } from '@inertiajs/vue3'
import { formatISO } from 'date-fns'
import axios from 'axios'
import ViewAppointmentModal from './Components/ViewAppointmentModal.vue'

const props = defineProps({
    appointments: {
        type: Array,
        default: () => []
    }
})

const page = usePage()

const showModal = ref(false)
const selectedAppointment = ref(null)
const showViewModal = ref(false)

function toLocalTime(utcTimeString) {
    // Xóa hàm này vì chúng ta không cần chuyển đổi thời gian nữa
}

const calendarRef = ref(null)

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: 'timeGridWeek', // Changed to week view
    weekends: true,
    events: computed(() => {
        return props.appointments.map(appointment => ({
            id: appointment.id,
            title: `${appointment.user?.full_name || 'Không xác định'} - ${appointment.appointment_type || 'Không xác định'}`,
            start: appointment.start_time, // Sử dụng trực tiếp start_time
            end: appointment.end_time, // Sử dụng trực tiếp end_time
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
}))

function handleDateSelect(selectInfo) {
    showModal.value = true
    selectedAppointment.value = {
        start: selectInfo.startStr,
        end: selectInfo.endStr,
        allDay: selectInfo.allDay
    }
}

function handleEventClick(clickInfo) {
    selectedAppointment.value = props.appointments.find(apt => apt.id == clickInfo.event.id);
    showViewModal.value = true;
}

function handleEventDrop(dropInfo) {
    const updatedAppointment = {
        id: dropInfo.event.id,
        start_time: dropInfo.event.start.toISOString(),
        end_time: dropInfo.event.end.toISOString(),
    };
    updateAppointment(updatedAppointment);
}

function updateAppointment(appointmentData) {
    axios.put(`/api/appointments/${appointmentData.id}`, appointmentData)
        .then(response => {
            // Update the local appointments array
            const index = props.appointments.findIndex(apt => apt.id === appointmentData.id);
            if (index !== -1) {
                props.appointments[index] = response.data.appointment;
            }
            // Refresh the calendar
            if (calendarRef.value) {
                calendarRef.value.getApi().refetchEvents();
            }
        })
        .catch(error => {
            console.error('Error updating appointment:', error);
            // Revert the event if the update fails
            if (calendarRef.value) {
                calendarRef.value.getApi().refetchEvents();
            }
        });
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
        treatment_id: appointmentData.treatment_id,
        user_treatment_package_id: appointmentData.user_treatment_package_id,
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
            router.reload();
        },
    });
}

function handleAppointmentAdded() {
    // Reload the page to reflect the new appointment
    window.location.reload();
}

// Hàm để định dạng ngày giờ
function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString)
    return date.toLocaleString() // Hoặc sử dụng bất kỳ định dạng nào bạn muốn
}

const appointments = ref(page.props.appointments || [])

onMounted(() => {
    console.log('Appointments in CalendarView:', appointments.value)
})

function closeViewModal() {
    showViewModal.value = false;
    selectedAppointment.value = null;
}

function handleAppointmentUpdate(updatedAppointment) {
    updateAppointment(updatedAppointment);
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Lịch hẹn" />
        <SectionMain>
            <FullCalendar ref="calendarRef" :options="calendarOptions" class="custom-calendar" />
        </SectionMain>
        <AppointmentModal 
            :show="showModal" 
            :appointment="selectedAppointment" 
            @close="closeModal"
            @save="saveAppointment" 
            @appointmentAdded="handleAppointmentAdded"
            :closeModal="closeModal"
        />
        <ViewAppointmentModal
            :show="showViewModal"
            :appointment="selectedAppointment"
            @close="closeViewModal"
            @update="handleAppointmentUpdate"
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
