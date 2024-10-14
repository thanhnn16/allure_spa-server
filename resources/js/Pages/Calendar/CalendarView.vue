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
        title: `${appointment.user?.full_name || 'Không xác định'} - ${appointment.treatment?.name || 'Không xác định'}`,
        start: appointment.start_time,
        end: appointment.end_time,
        extendedProps: {
            userId: appointment.user_id,
            treatmentId: appointment.treatment_id,
            staffId: appointment.staff_user_id,
            appointmentType: appointment.appointment_type,
            status: appointment.status
        }
    }))
})

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: 'timeGridWeek', // Changed to week view
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
    allDayText: 'Cả ngày',
    moreLinkText: 'Xem thêm',
    noEventsText: 'Không có lịch hẹn nào',
    slotLabelFormat: {
        hour: 'numeric',
        minute: '2-digit',
        omitZeroMinute: false,
        meridiem: 'short'
    },
    slotMinTime: '08:00:00',
    slotMaxTime: '18:30:00',
    businessHours: {
        daysOfWeek: [0, 1, 2, 3, 4, 5, 6],
        startTime: '08:00',
        endTime: '18:30',
    },
    nowIndicator: true,
    editable: true,
    selectable: true,
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventResize: handleEventResize,
}))

const selectedTimeSlot = ref(null)

function handleDateSelect(selectInfo) {
    selectedTimeSlot.value = {
        start: selectInfo.start,
        end: selectInfo.end
    }
    showModal.value = true
}

function handleEventClick(info) {
    try {
        const appointmentId = info.event.id;
        showViewModal.value = true;
        selectedAppointmentId.value = appointmentId;
    } catch (error) {
        console.error('Lỗi khi xử lý sự kiện click:', error);
        // Hiển thị thông báo lỗi cho người dùng nếu cần
    }
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

function closeViewModal() {
    showViewModal.value = false;
    selectedAppointmentId.value = null;
}

function handleAppointmentUpdate(updatedAppointment) {
    updateAppointment(updatedAppointment);
    closeViewModal();
}

const selectedAppointmentId = ref(null)

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
        <ViewAppointmentModal :show="showViewModal" :appointmentId="Number(selectedAppointmentId)"
            @close="closeViewModal" @update="handleAppointmentUpdate"
            :fetchAppointmentDetails="fetchAppointmentDetails" />
    </LayoutAuthenticated>
</template>

<style scoped>
.custom-calendar {
    height: calc(100vh - 200px);
    /* Điều chỉnh chiều cao tùy theo layout của bạn */
}

:deep(.fc-timegrid-slot) {
    height: 3em !important;
    /* Đặt chiều cao cố định cho mỗi ô thời gian */
}

:deep(.fc-timegrid-axis-cushion) {
    max-width: none;
    /* Cho phép nhãn thời gian hiển thị đầy đủ */
    white-space: nowrap;
}

:deep(.fc-timegrid-slot-label-cushion) {
    font-weight: bold;
    color: #555;
}

:deep(.fc-timegrid-now-indicator-line) {
    border-color: #ff0000;
    /* Màu đỏ cho đường chỉ thời gian hiện tại */
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
