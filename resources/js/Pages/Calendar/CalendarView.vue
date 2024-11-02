<script setup>
import { ref, computed, onMounted, watch } from 'vue'
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
import { parseISO } from 'date-fns'
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
        title: `${appointment.user?.full_name || 'Không xác định'} - ${appointment.service?.service_name || 'Không xác định'}`,
        start: `${appointment.start}`,
        end: `${appointment.end}`,
        className: `status-${appointment.status.toLowerCase()}`,
        extendedProps: {
            userId: appointment.user_id,
            serviceId: appointment.service_id,
            staffId: appointment.staff_user_id,
            appointmentType: appointment.appointment_type,
            status: appointment.status,
            timeSlotId: appointment.time_slot_id,
            userName: appointment.user?.full_name,
            serviceName: appointment.service?.service_name,
            staffName: appointment.staff?.full_name
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
        tippy(info.el, {
            content: `
                <div class="p-2">
                    <div class="font-bold">${info.event.extendedProps.userName || 'Không xác định'}</div>
                    <div>Dịch vụ: ${info.event.extendedProps.serviceName || 'Không xác định'}</div>
                    <div>Nhân viên: ${info.event.extendedProps.staffName || 'Không xác định'}</div>
                    <div>Trạng thái: ${info.event.extendedProps.status}</div>
                </div>
            `,
            allowHTML: true,
            placement: 'top',
            theme: 'light-border',
            delay: [200, 0], // Delay before showing tooltip
            interactive: true
        })
    },
    eventClick: (info) => {
        info.jsEvent.preventDefault()
        router.visit(`/appointments/${info.event.id}`)
    },
    eventDrop: handleEventDrop,
    eventMaxStack: 2, // Cho phép hiển thị tối đa 2 events cùng 1 khung giờ
}))

const selectedTimeSlot = ref(null)

function handleDateSelect(selectInfo) {
    const selectedStart = new Date(selectInfo.start)
    const selectedHour = selectedStart.getHours().toString().padStart(2, '0')
    const selectedMinute = selectedStart.getMinutes().toString().padStart(2, '0')
    const selectedTimeString = `${selectedHour}:${selectedMinute}:00`

    const matchingTimeSlot = props.timeSlots.find(slot => {
        return slot.start_time === selectedTimeString
    })

    if (!matchingTimeSlot) {
        alert('Vui lòng chọn khung giờ hợp lệ')
        return
    }

    if (matchingTimeSlot.current_bookings >= matchingTimeSlot.max_bookings) {
        alert('Khung giờ này đã đầy')
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

const selectedAppointmentId = ref(null);

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
    const form = useForm({
        user_id: appointmentData.user_id,
        service_id: appointmentData.service_id,
        staff_id: appointmentData.staff_id,
        // Đảm bảo ngày được format đúng
        appointment_date: formatDate(appointmentData.appointment_date),
        time_slot_id: appointmentData.time_slot_id,
        appointment_type: appointmentData.appointment_type,
        status: appointmentData.status || 'pending',
        note: appointmentData.note,
    })

    form.post(route('appointments.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (response) => {
            closeModal()
            // Cập nhật danh sách appointments local với dữ liệu đã được format
            if (response?.props?.appointment) {
                const formattedAppointment = {
                    ...response.props.appointment,
                    start: `${response.props.appointment.appointment_date} ${response.props.appointment.timeSlot.start_time}`,
                    end: `${response.props.appointment.appointment_date} ${response.props.appointment.timeSlot.end_time}`
                }
                appointments.value = [...appointments.value, formattedAppointment]
            }
            // Refresh calendar events
            if (calendarRef.value) {
                calendarRef.value.getApi().refetchEvents()
            }
            toast.success("Thêm lịch hẹn thành công!")
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors).flat().join('\n')
            toast.error(`Có lỗi xảy ra khi thêm lịch hẹn! ${errorMessage}`)
        }
    })
}

// Thêm hàm format date
function formatDate(date) {
    if (!date) return ''
    const d = new Date(date)
    return d.toISOString().split('T')[0]
}

function handleAppointmentAdded() {
    if (calendarRef.value) {
        calendarRef.value.getApi().refetchEvents()
    }
}

// Add status legend component
const statusColors = [
    { status: 'Đang chờ', class: 'status-pending', color: '#fbbf24' },
    { status: 'Đã xác nhận', class: 'status-confirmed', color: '#34d399' },
    { status: 'Đã hủy', class: 'status-cancelled', color: '#ef4444' },
    { status: 'Hoàn thành', class: 'status-completed', color: '#3b82f6' }
]
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Lịch hẹn" />
        <SectionMain>
            <!-- Add status legend -->
            <div class="mb-4 flex items-center gap-4 p-4 bg-white rounded-lg shadow">
                <span class="font-semibold">Trạng thái:</span>
                <div class="flex gap-4">
                    <div v-for="item in statusColors" :key="item.status" class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded" :style="{ backgroundColor: item.color }"></div>
                        <span>{{ item.status }}</span>
                    </div>
                </div>
            </div>

            <FullCalendar ref="calendarRef" :options="calendarOptions" class="custom-calendar" />
        </SectionMain>
        <AddAppointmentModal :show="showModal" :appointments="appointments" :selectedTimeSlot="selectedTimeSlot"
            @close="closeModal" @save="saveAppointment" @appointmentAdded="handleAppointmentAdded"
            :closeModal="closeModal" />
    </LayoutAuthenticated>
</template>
<style scoped>
.custom-calendar {
    height: calc(100vh - 200px);
}

:deep(.fc-timegrid-slot) {
    height: 50px !important;
}

:deep(.fc-timegrid-axis-cushion) {
    max-width: none;
    white-space: nowrap;
    padding: 8px;
}

/* Tooltip styles */
.tippy-box[data-theme~='light-border'] {
    background-color: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    z-index: 9999 !important;
}

.tippy-box[data-theme~='light-border'] .tippy-content {
    padding: 0;
}

.tippy-box[data-theme~='light-border'][data-placement^='top']>.tippy-arrow::before {
    border-top-color: #e2e8f0;
}

.tippy-box[data-theme~='light-border'][data-placement^='bottom']>.tippy-arrow::before {
    border-bottom-color: #e2e8f0;
}

/* Status colors */
:deep(.status-pending) {
    background-color: #fbbf24 !important;
    border-color: #f59e0b !important;
    color: #000 !important;
}

:deep(.status-confirmed) {
    background-color: #34d399 !important;
    border-color: #10b981 !important;
    color: #000 !important;
}

:deep(.status-cancelled) {
    background-color: #ef4444 !important;
    border-color: #dc2626 !important;
    color: #fff !important;
}

:deep(.status-completed) {
    background-color: #3b82f6 !important;
    border-color: #2563eb !important;
    color: #fff !important;
}

/* Event styles */
:deep(.fc-timegrid-event-harness) {
    width: 100% !important;
    height: 50px;
    margin: 0 !important;
}


:deep(.fc-event-title) {
    font-size: 0.85em !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    padding: 2px !important;
}
</style>
