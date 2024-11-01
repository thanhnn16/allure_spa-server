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
import ViewAppointmentModal from './Components/ViewAppointmentModal.vue'
import { parseISO } from 'date-fns'

const props = defineProps({
    appointments: {
        type: Array,
        default: () => []
    },
    timeSlots: {
        type: Array,
        default: () => []
    },
    businessHours: {
        type: Object,
        required: true
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
    businessHours: props.businessHours,
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
    selectConstraint: "businessHours",
    eventConstraint: "businessHours",
    selectMirror: true,
    moreLinkText: 'Xem thêm',
    noEventsText: 'Không có lịch hẹn nào',
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    validRange: {
        start: new Date().toISOString().split('T')[0] // Chỉ cho phép từ ngày hiện tại
    },
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

const handleEventClick = (info) => {
    console.log('Event clicked:', info);
    if (!info || !info.event) {
        console.log('Invalid event info');
        return;
    }
    
    // Đảm bảo ID được chuyển đổi thành số
    const eventId = parseInt(info.event.id);
    console.log('Event ID:', eventId);
    
    if (!eventId || isNaN(eventId)) {
        console.error('Invalid event ID');
        return;
    }
    
    selectedAppointmentId.value = eventId;
    console.log('Selected appointment ID:', selectedAppointmentId.value);
    showViewModal.value = true;
};

const closeViewModal = () => {
    console.log('Closing view modal');
    showViewModal.value = false;
    // Reset selectedAppointmentId sau khi đóng modal
    setTimeout(() => {
        selectedAppointmentId.value = null;
    }, 100);
};

// Thêm watch để debug
watch(() => selectedAppointmentId.value, (newVal) => {
    console.log('selectedAppointmentId changed:', newVal);
});

watch(() => showViewModal.value, (newVal) => {
    console.log('showViewModal changed:', newVal);
});

function handleEventDrop(dropInfo) {
    const startDate = new Date(dropInfo.event.start)
    const hour = startDate.getHours().toString().padStart(2, '0')
    const minute = startDate.getMinutes().toString().padStart(2, '0')
    const timeString = `${hour}:${minute}:00`

    const matchingTimeSlot = props.timeSlots.find(slot =>
        slot.start_time === timeString
    )

    if (!matchingTimeSlot) {
        alert('Không thể di chuyển đến khung giờ này')
        dropInfo.revert()
        return
    }

    const updatedAppointment = {
        id: dropInfo.event.id,
        appointment_date: startDate.toISOString().split('T')[0],
        time_slot_id: matchingTimeSlot.id
    }

    updateAppointment(updatedAppointment)
}

function updateLocalAppointments(updatedAppointment) {
    const index = appointments.value.findIndex(apt => apt.id === updatedAppointment.id)
    if (index !== -1) {
        appointments.value[index] = updatedAppointment
    } else {
        appointments.value.push(updatedAppointment)
    }
}

function updateAppointment(appointmentData) {
    axios.put(`/api/appointments/${appointmentData.id}`, appointmentData)
        .then(response => {
            updateLocalAppointments(response.data.appointment)
            if (calendarRef.value) {
                calendarRef.value.getApi().refetchEvents()
            }
        })
        .catch(error => {
            console.error('Lỗi khi cập nhật cuộc hẹn:', error)
            if (calendarRef.value) {
                calendarRef.value.getApi().refetchEvents()
            }
        })
}

function closeModal() {
    showModal.value = false
    selectedAppointment.value = null
}

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
        onSuccess: () => {
            closeModal()
            router.reload()
        },
    })
}

function handleAppointmentAdded() {
    // Reload the page to reflect the new appointment
    window.location.reload();
}

function handleAppointmentUpdate(updatedAppointment) {
    updateAppointment(updatedAppointment);
    closeViewModal();
}

const fetchAppointmentDetails = async (id) => {
    try {
        const response = await axios.get(`/api/appointments/${id}`);
        if (response.data.status === 200 && response.data.data) {
            return response.data.data;
        } else {
            console.error('Lỗi khi fetch thông tin cuộc hẹn:', response.data.message);
            return null;
        }
    } catch (error) {
        console.error('Lỗi khi fetch thông tin cuộc hẹn:', error.response?.data?.message || error.message);
        return null;
    }
};
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
        <ViewAppointmentModal :show="showViewModal" :appointmentId="selectedAppointmentId"
            @close="closeViewModal" @update="handleAppointmentUpdate"
            :fetchAppointmentDetails="fetchAppointmentDetails" />
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
</style>
