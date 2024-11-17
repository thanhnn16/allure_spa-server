<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import AddAppointmentModal from '@/Pages/Calendar/Components/AddAppointmentModal.vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import tippy from 'tippy.js'
import 'tippy.js/dist/tippy.css'
import { useToast } from "vue-toastification"

const props = defineProps({
    appointments: {
        type: Array,
        default: () => []
    },
    timeSlots: {
        type: Array,
        default: () => []
    },
    slotDuration: {
        type: String,
        default: '01:00:00'
    },
    slotMinTime: {
        type: String,
        default: '08:00:00'
    },
    slotMaxTime: {
        type: String,
        default: '18:30:00'
    }
})

const page = usePage()

const showModal = ref(false)
const selectedAppointment = ref(null)
const showViewModal = ref(false)

const calendarRef = ref(null)

const appointments = ref(props.appointments)

// Watch for changes in props.appointments
watch(() => props.appointments, (newAppointments) => {
    appointments.value = newAppointments
}, { deep: true })

const events = computed(() => {
    return appointments.value.map(appointment => ({
        id: appointment.id,
        title: `${appointment.user?.full_name || 'Không xác định'} - ${appointment.service?.name || 'Không xác định'}`,
        start: appointment.start,
        end: appointment.end,
        className: `status-${appointment.status.toLowerCase()}`,
        extendedProps: {
            userId: appointment.user_id,
            serviceId: appointment.service_id,
            staffId: appointment.staff_user_id,
            appointmentType: appointment.appointment_type,
            status: appointment.status,
            timeSlotId: appointment.time_slot_id,
            userName: appointment.user?.full_name,
            serviceName: appointment.service?.name,
            staffName: appointment.staff?.full_name,
            cancelledBy: appointment.cancelled_by,
            cancelledAt: appointment.cancelled_at,
            cancellationNote: appointment.cancellation_note,
            cancelledByUser: appointment.cancelled_by_user,
        }
    }))
})

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    weekends: true,
    events: events.value,
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
    allDaySlot: false,
    height: 'auto',
    nowIndicator: true,
    editable: true,
    selectable: true,
    eventResizableFromStart: false,
    eventDurationEditable: false,
    slotDuration: props.slotDuration,
    snapDuration: props.slotDuration,
    slotLabelInterval: props.slotDuration,
    slotMinTime: props.slotMinTime,
    slotMaxTime: props.slotMaxTime,
    slotLabelFormat: {
        hour: 'numeric',
        minute: '2-digit',
        omitZeroMinute: true,         // Bỏ hiển thị :00
        meridiem: 'short'
    },
    moreLinkText: 'Xem thêm',
    noEventsText: 'Không có lịch hẹn nào',
    select: handleDateSelect,
    eventDidMount: (info) => {
        const event = info.event;
        const props = event.extendedProps;

        let tooltipContent = `
            <div class="p-2">
                <div class="font-bold">${props.userName || 'Không xác định'}</div>
                <div>Dịch vụ: ${props.serviceName || 'Không xác định'}</div>
                <div>Nhân viên: ${props.staffName || 'Không xác định'}</div>
                <div>Trạng thái: ${props.status}</div>
        `;

        if (props.status === 'cancelled') {
            tooltipContent += `
                <div class="mt-2 pt-2 border-t">
                    <div>Hủy bởi: ${props.cancelledByUser?.full_name || 'Không xác định'}</div>
                    <div>Thời gian hủy: ${formatDateTime(props.cancelledAt)}</div>
                    ${props.cancellationNote ? `<div>Lý do: ${props.cancellationNote}</div>` : ''}
                </div>
            `;
        }

        tooltipContent += '</div>';

        tippy(info.el, {
            content: tooltipContent,
            allowHTML: true,
            placement: 'top',
            theme: 'light-border',
            delay: [200, 0],
            interactive: true
        });
    },
    eventClick: (info) => {
        info.jsEvent.preventDefault()
        router.visit(`/appointments/${info.event.id}`)
    },
    eventDrop: handleEventDrop,
    eventMaxStack: 2, // Cho phép hiển thị tối đa 2 events cùng 1 khung giờ
    eventClassNames: (arg) => {
        return [`${arg.event.extendedProps.status.toLowerCase()}-event`, 'calendar-event']
    },
    themeSystem: 'standard',
    views: {
        timeGrid: {
            dayMaxEvents: 2,
            nowIndicator: true,
            eventMinHeight: 30
        }
    }
}))

const selectedTimeSlot = ref(null)

function handleDateSelect(selectInfo) {
    const selectedStart = new Date(selectInfo.start)
    const selectedHour = selectedStart.getHours().toString().padStart(2, '0')
    const selectedMinute = selectedStart.getMinutes().toString().padStart(2, '0')

    const matchingTimeSlot = props.timeSlots.find(slot => {
        // So sánh chỉ phần giờ và phút
        const slotTime = slot.start_time.substring(0, 5)  // "HH:mm"
        const selectedTime = `${selectedHour}:${selectedMinute}`  // "HH:mm"
        return slotTime === selectedTime
    })

    if (!matchingTimeSlot) {
        toast.error('Vui lòng chọn đúng khung giờ')
        return
    }

    if (matchingTimeSlot.current_bookings >= matchingTimeSlot.max_bookings) {
        toast.error('Khung giờ này đã đầy')
        return
    }

    selectedTimeSlot.value = {
        date: selectInfo.startStr.split('T')[0],
        timeSlotId: matchingTimeSlot.id,
        startTime: matchingTimeSlot.start_time,
        endTime: matchingTimeSlot.end_time
    }

    showModal.value = true
}


function handleEventDrop(dropInfo) {
    const event = dropInfo.event;
    const selectedStart = new Date(event.start);
    const selectedHour = selectedStart.getHours().toString().padStart(2, '0');
    const selectedMinute = selectedStart.getMinutes().toString().padStart(2, '0');
    const selectedTimeString = `${selectedHour}:${selectedMinute}:00`;

    const matchingTimeSlot = props.timeSlots.find(slot => {
        return slot.start_time === selectedTimeString;
    });

    if (!matchingTimeSlot) {
        toast.error('Vui lòng chọn khung giờ hợp lệ');
        dropInfo.revert();
        return;
    }

    const updatedAppointment = {
        appointment_date: event.start.toISOString().split('T')[0],
        time_slot_id: matchingTimeSlot.id,
    };

    axios.put(`/api/appointments/${event.id}`, updatedAppointment)
        .then(response => {
            if (response.status === 200) {
                toast.success(response.data.message);
            } else {
                throw new Error(response.data.message || "Có lỗi xảy ra");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toast.error(error.response?.data?.message || "Có lỗi xảy ra khi cập nhật lịch hẹn!");
            if (calendarRef.value) {
                calendarRef.value.getApi().refetchEvents();
            }
            dropInfo.revert();
        });
}

function refreshCalendar() {
    if (calendarRef.value) {
        calendarRef.value.getApi().refetchEvents()
    }
}

function updateLocalAppointments(updatedAppointment) {
    const index = appointments.value.findIndex(apt => apt.id === updatedAppointment.id)
    if (index !== -1) {
        appointments.value[index] = updatedAppointment
    }
}

function closeModal() {
    showModal.value = false
    selectedAppointment.value = null
}

const toast = useToast()

function saveAppointment(appointmentData) {
    // Không cần xử lý gì ở đây nữa vì AddAppointmentModal sẽ tự xử lý
    // và emit event appointmentAdded khi thành công
}

function handleAppointmentAdded(newAppointment) {
    // Kiểm tra và format dữ liệu appointment
    if (!newAppointment || !newAppointment.time_slot) {
        console.error('Invalid appointment data:', newAppointment);
        return;
    }

    // Format appointment data để phù hợp với calendar
    const formattedAppointment = {
        ...newAppointment,
        id: newAppointment.id,
        title: `${newAppointment.user?.full_name || 'N/A'} - ${newAppointment.service?.name || 'N/A'}`,
        start: `${newAppointment.appointment_date} ${newAppointment.time_slot.start_time}`,
        end: `${newAppointment.appointment_date} ${newAppointment.time_slot.end_time}`,
        extendedProps: {
            userId: newAppointment.user_id,
            serviceId: newAppointment.service_id,
            staffId: newAppointment.staff_id,
            appointmentType: newAppointment.appointment_type,
            status: newAppointment.status,
            timeSlotId: newAppointment.time_slot_id,
            userName: newAppointment.user?.full_name,
            serviceName: newAppointment.service?.name,
            staffName: newAppointment.staff?.full_name
        }
    };

    // Thêm appointment mới vào danh sách
    appointments.value = [...appointments.value, formattedAppointment];

    // Refresh calendar
    if (calendarRef.value) {
        calendarRef.value.getApi().refetchEvents();
    }
}

// Thêm hàm format date
function formatDate(date) {
    if (!date) return ''
    const d = new Date(date)
    return d.toISOString().split('T')[0]
}

// Add status legend component
const statusColors = [
    { status: 'Đang chờ', class: 'status-pending', color: '#fbbf24' },
    { status: 'Đã xác nhận', class: 'status-confirmed', color: '#34d399' },
    { status: 'Đã hủy', class: 'status-cancelled', color: '#ef4444' },
    { status: 'Hoàn thành', class: 'status-completed', color: '#3b82f6' }
]

// Thêm prop isAsideLgActive
const isAsideLgActive = ref(true)

// Thêm hàm format datetime
function formatDateTime(dateTimeStr) {
    if (!dateTimeStr) return 'N/A';
    const date = new Date(dateTimeStr);
    return new Intl.DateTimeFormat('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Lịch hẹn" />
        <SectionMain :is-aside-lg-active="isAsideLgActive">
            <!-- Status legend với dark mode support -->
            <div class="mb-4 flex items-center gap-4 p-4 bg-white dark:bg-slate-800 rounded-lg shadow">
                <span class="font-semibold dark:text-slate-200">Trạng thái:</span>
                <div class="flex gap-4">
                    <div v-for="item in statusColors" :key="item.status"
                        class="flex items-center gap-2 dark:text-slate-300">
                        <div class="w-4 h-4 rounded" :style="{ backgroundColor: item.color }"></div>
                        <span>{{ item.status }}</span>
                    </div>
                </div>
            </div>

            <div class="calendar-container bg-white dark:bg-slate-800 rounded-lg shadow p-4">
                <FullCalendar ref="calendarRef" :options="calendarOptions" class="custom-calendar" />
            </div>
        </SectionMain>

        <AddAppointmentModal :show="showModal" :appointments="appointments" :selectedTimeSlot="selectedTimeSlot"
            @close="closeModal" @appointmentAdded="handleAppointmentAdded" :closeModal="closeModal" />
    </LayoutAuthenticated>
</template>

<style>
/* Base calendar styles */
.calendar-container {
    min-height: calc(100vh - 200px);
}

.custom-calendar {
    height: 100%;
}

/* Dark mode styles */
.dark .fc {
    --fc-border-color: rgb(51, 65, 85);
    --fc-page-bg-color: rgb(30, 41, 59);
    --fc-neutral-bg-color: rgb(51, 65, 85);
    --fc-list-event-hover-bg-color: rgb(51, 65, 85);
    --fc-today-bg-color: rgba(59, 130, 246, 0.1);
}

.dark .fc-theme-standard td,
.dark .fc-theme-standard th {
    border-color: var(--fc-border-color);
}

.dark .fc-theme-standard .fc-scrollgrid {
    border-color: var(--fc-border-color);
}

.dark .fc-timegrid-slot-label {
    color: rgb(203, 213, 225);
}

.dark .fc-day-today {
    background: var(--fc-today-bg-color) !important;
}

/* Event styles with dark mode support */
.calendar-event {
    margin: 1px 0;
    border-radius: 4px;
    padding: 2px 4px;
}

/* Status colors - Light mode */
.status-pending {
    background-color: #fbbf24 !important;
    border-color: #f59e0b !important;
    color: #000 !important;
}

.status-confirmed {
    background-color: #34d399 !important;
    border-color: #10b981 !important;
    color: #000 !important;
}

.status-cancelled {
    background-color: #ef4444 !important;
    border-color: #dc2626 !important;
    color: #fff !important;
    text-decoration: line-through;
    opacity: 0.8;
}

.dark .status-cancelled {
    opacity: 0.7;
}

.status-completed {
    background-color: #3b82f6 !important;
    border-color: #2563eb !important;
    color: #fff !important;
}

/* Dark mode specific event styles */
.dark .calendar-event {
    opacity: 0.9;
}

/* Tooltip styles with dark mode */
.tippy-box[data-theme~='light-border'] {
    background-color: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    max-width: 300px !important;
}

.dark .tippy-box[data-theme~='light-border'] {
    background-color: rgb(30, 41, 59);
    border-color: rgb(51, 65, 85);
    color: rgb(203, 213, 225);
}

.dark .tippy-box[data-theme~='light-border'] .border-t {
    border-color: rgb(71, 85, 105);
}

/* Calendar header and controls */
.dark .fc-button-primary {
    background-color: rgb(51, 65, 85) !important;
    border-color: rgb(71, 85, 105) !important;
    color: rgb(203, 213, 225) !important;
}

.dark .fc-button-primary:hover {
    background-color: rgb(71, 85, 105) !important;
}

.dark .fc-button-primary:disabled {
    background-color: rgb(51, 65, 85) !important;
    opacity: 0.65;
}

/* Time grid specific styles */
.fc-timegrid-slot {
    height: 50px !important;
}

.fc-timegrid-axis-cushion {
    max-width: none;
    white-space: nowrap;
    padding: 8px;
}

.dark .fc-timegrid-axis-cushion {
    color: rgb(203, 213, 225);
}

/* Event title styles */
.fc-event-title {
    font-size: 0.85em !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    padding: 2px !important;
}

/* Now indicator */
.dark .fc-timegrid-now-indicator-line {
    border-color: #ef4444;
}

.dark .fc-timegrid-now-indicator-arrow {
    border-color: #ef4444;
}

/* Thêm style cho cancelled event tooltip */
.tippy-box[data-theme~='light-border'] .mt-2 {
    margin-top: 0.5rem;
}

.tippy-box[data-theme~='light-border'] .pt-2 {
    padding-top: 0.5rem;
}

.tippy-box[data-theme~='light-border'] .border-t {
    border-top-width: 1px;
    border-top-style: solid;
    border-top-color: #e2e8f0;
}
</style>
