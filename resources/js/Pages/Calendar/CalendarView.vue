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

    // Tìm time slot phù hợp
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
                const updatedData = response.data.data;
                
                // Cập nhật dữ liệu local
                const index = appointments.value.findIndex(apt => apt.id === updatedData.id);
                if (index !== -1) {
                    appointments.value[index] = {
                        ...appointments.value[index],
                        ...updatedData,
                        start: `${updatedData.appointment_date}T${updatedData.time_slot.start_time}`,
                        end: `${updatedData.appointment_date}T${updatedData.time_slot.end_time}`
                    };
                }

                toast.success(response.data.message);
            } else {
                throw new Error(response.data.message || "Có lỗi xảy ra");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toast.error(error.response?.data?.message || "Có lỗi xảy ra khi cập nhật lịch hẹn!");
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
        appointment_date: new Date(appointmentData.date).toISOString().split('T')[0],
        time_slot_id: appointmentData.timeSlotId,
        appointment_type: appointmentData.appointment_type,
        status: appointmentData.status || 'pending',
        note: appointmentData.note,
    })

    form.post(route('appointments.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (response) => {
            closeModal()
            if (response?.props?.appointment) {
                updateLocalAppointments(response.props.appointment)
            }
            if (calendarRef.value) {
                calendarRef.value.getApi().refetchEvents()
            }
            toast.success("Thêm lịch hẹn thành công!")
        },
        onError: (errors) => {
            toast.error("Có lỗi xảy ra khi thêm lịch hẹn!")
        }
    })
}

function handleAppointmentAdded() {
    if (calendarRef.value) {
        calendarRef.value.getApi().refetchEvents()
    }
}
</script>

<template>
    <LayoutAuthenticated>

        <Head title="Lịch hẹn" />
        <SectionMain>
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
    height: 4em !important;
    /* Tăng chiều cao của mỗi ô */
}

:deep(.fc-timegrid-axis-cushion) {
    max-width: none;
    white-space: nowrap;
    padding: 8px;
    /* Thêm padding cho nhãn thời gian */
}

:deep(.fc-timegrid-slot-label-cushion) {
    font-weight: bold;
    color: #555;
}

:deep(.fc-timegrid-now-indicator-line) {
    border-color: #ff0000;
}

:deep(.fc-event) {
    border-radius: 4px;
    font-size: 0.9em;
    margin: 1px 0;
    /* Thêm margin cho events */
}

:deep(.fc-toolbar-title) {
    font-size: 1.5em !important;
    font-weight: bold;
}

:deep(.fc-button) {
    text-transform: capitalize;
}

/* Thêm style cho grid lines */
:deep(.fc-timegrid-cols) {
    border: 1px solid #ddd;
}

:deep(.fc-timegrid-col) {
    border-right: 1px solid #ddd;
}

/* Tooltip styles */
.tippy-box[data-theme~='light-border'] {
    background-color: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.tippy-box[data-theme~='light-border'] .tippy-content {
    padding: 0;
}

.tippy-box[data-theme~='light-border'][data-placement^='top'] > .tippy-arrow::before {
    border-top-color: #e2e8f0;
}

.tippy-box[data-theme~='light-border'][data-placement^='bottom'] > .tippy-arrow::before {
    border-bottom-color: #e2e8f0;
}
</style>
