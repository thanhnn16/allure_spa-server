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
        title: `${appointment.user?.full_name || 'Không xác định'} - ${appointment.service?.service_name || 'Không xác định'}`,
        start: appointment.start,
        end: appointment.end,
        className: `status-${appointment.status.toLowerCase()}`,
        slots: appointment.slots || 1,
        extendedProps: {
            userId: appointment.user_id,
            serviceId: appointment.service_id,
            staffId: appointment.staff_user_id,
            appointmentType: appointment.appointment_type,
            status: appointment.status,
            timeSlotId: appointment.time_slot_id,
            userName: appointment.user?.full_name,
            serviceName: appointment.service?.service_name,
            staffName: appointment.staff?.full_name,
            cancelledBy: appointment.cancelled_by,
            cancelledAt: appointment.cancelled_at,
            cancellationNote: appointment.cancellation_note,
            cancelledByUser: appointment.cancelled_by_user,
            slots: appointment.slots || 1
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
    eventDragStart: (info) => {
        // Hủy tooltip của event đang được kéo
        if (info.el._tippy) {
            info.el._tippy.destroy();
        }

        // Hủy tất cả các tooltip đang hiển thị
        const tooltips = document.querySelectorAll('[data-tippy-root]');
        tooltips.forEach(tooltip => tooltip.remove());

        // Xóa z-index của event đang kéo
        info.el.style.zIndex = 'auto';
    },
    eventDrop: (info) => {
        handleEventDrop(info);

        // Thêm logic xử lý sau khi kéo thả vào đây
        info.el.style.zIndex = '';

        // Khởi tạo lại tooltip sau khi kéo thả xong
        setTimeout(() => {
            if (!info.el._tippy) {
                initializeTooltip(info.el, info.event);
            }
        }, 100);
    },
    eventDidMount: (info) => {
        const event = info.event;
        const slots = event.extendedProps.slots || 1;
        const eventEl = info.el;
        const harness = eventEl.closest('.fc-timegrid-event-harness');

        // Kiểm tra nếu harness tồn tại
        if (harness) {
            // Thêm event listeners cho drag
            eventEl.addEventListener('dragstart', () => {
                // Hủy tooltip của event đang được kéo
                if (eventEl._tippy) {
                    eventEl._tippy.destroy();
                }

                // Hủy tất cả các tooltip đang hiển thị
                const tooltips = document.querySelectorAll('[data-tippy-root]');
                tooltips.forEach(tooltip => tooltip.remove());

                // Xóa z-index của event đang kéo
                eventEl.style.zIndex = 'auto';
            });

            // Reset styles và tooltip sau khi drag
            eventEl.addEventListener('dragend', () => {
                // Khôi phục z-index
                eventEl.style.zIndex = '';

                // Khởi tạo lại tooltip
                setTimeout(() => {
                    if (!eventEl._tippy) {
                        initializeTooltip(eventEl, event);
                    }
                }, 100);
            });

            // Reset style
            eventEl.style = '';
            eventEl.classList.remove('full-slot-event', 'half-slot-event');

            if (slots === 2) {
                // Event 2 slots
                harness.style.cssText = `
                    position: absolute !important;
                    inset: 0 !important;
                    width: 100% !important;
                `;
                eventEl.style.cssText = `
                    position: absolute !important;
                    inset: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                `;
                eventEl.classList.add('full-slot-event');
            } else {
                // Event 1 slot
                const timeCol = harness.closest('.fc-timegrid-col');
                const allHarnesses = Array.from(timeCol.querySelectorAll('.fc-timegrid-event-harness'));
                const currentIndex = allHarnesses.indexOf(harness);

                harness.style.cssText = `
                    position: absolute !important;
                    top: 0 !important;
                    bottom: 0 !important;
                    width: 50% !important;
                    left: ${currentIndex === 0 ? '0' : '50%'} !important;
                `;

                eventEl.style.cssText = `
                    position: absolute !important;
                    inset: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                `;
                eventEl.classList.add('half-slot-event');
            }
        }

        initializeTooltip(eventEl, event);
    },
    eventClick: (info) => {
        info.jsEvent.preventDefault()
        router.visit(`/appointments/${info.event.id}`)
    },
    eventDrop: handleEventDrop,
    eventMaxStack: 2, // Cho phép hiển thị tối đa 2 events cùng 1 khung giờ
    eventClassNames: (arg) => {
        const slots = arg.event.slots || 1;
        return [
            `${arg.event.extendedProps.status.toLowerCase()}-event`,
            'calendar-event',
            slots === 2 ? 'full-slot-event' : 'half-slot-event'
        ];
    },
    themeSystem: 'standard',
    views: {
        timeGrid: {
            dayMaxEvents: 2,
            eventMaxStack: 2,
            nowIndicator: true,
            eventMinHeight: 29
        }
    },
    selectConstraint: {
        start: new Date().toISOString(),
    },
    selectOverlap: function (event) {
        // Chỉ cho phép chọn nếu không có event hoặc event hiện tại chiếm 1 slot
        if (!event) return true;
        return event.extendedProps.slots === 1;
    },
    eventConstraint: {
        start: new Date().toISOString(),
    },
}))

const selectedTimeSlot = ref(null)

function handleDateSelect(selectInfo) {
    const selectedStart = new Date(selectInfo.start)
    const now = new Date()

    // Thêm 1 giờ vào thời gian hiện tại
    const minBookingTime = new Date(now.getTime() + 60 * 60 * 1000)

    // Kiểm tra nếu thời gian chọn nhỏ hơn thời gian tối thiểu cho phép
    if (selectedStart < minBookingTime) {
        toast.error('Vui lòng đặt lịch trước ít nhất 1 tiếng')
        return
    }

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
    const now = new Date();
    const minBookingTime = new Date(now.getTime() + 60 * 60 * 1000);

    // Kiểm tra nếu thời gian kéo thả nhỏ hơn thời gian tối thiểu cho phép
    if (selectedStart < minBookingTime) {
        toast.error('Vui lòng đặt lịch trước ít nhất 1 tiếng');
        dropInfo.revert();

        // Reload lại trang
        router.visit(route('appointments.index'), {
            preserveScroll: true,
            preserveState: false,
            only: ['appointments']
        });
        return;
    }

    const selectedHour = selectedStart.getHours().toString().padStart(2, '0');
    const selectedMinute = selectedStart.getMinutes().toString().padStart(2, '0');

    // Tìm time slot phù hợp với thời gian được chọn
    const matchingTimeSlot = props.timeSlots.find(slot => {
        const slotTime = slot.start_time.substring(0, 5);  // "HH:mm"
        const selectedTime = `${selectedHour}:${selectedMinute}`;  // "HH:mm"
        return slotTime === selectedTime;
    });

    if (!matchingTimeSlot) {
        toast.error('Vui lòng kéo thả vào đúng khung giờ');
        dropInfo.revert();
        return;
    }

    // Chuẩn bị dữ liệu cập nhật
    const updatedAppointment = {
        staff_id: event.extendedProps.staffId,
        appointment_date: event.start.toISOString().split('T')[0],
        time_slot_id: matchingTimeSlot.id,
        status: event.extendedProps.status,
        appointment_type: event.extendedProps.appointmentType,
        slots: event.extendedProps.slots || 1
    };

    // Gọi API cập nhật lịch hẹn
    axios.put(`/api/appointments/${event.id}`, updatedAppointment)
        .then(response => {
            if (response.data.success) {
                toast.success('Cập nhật lịch hẹn thành công');
                router.visit(route('appointments.index'), {
                    preserveScroll: true,
                    preserveState: false,
                    only: ['appointments']
                });
            } else {
                throw new Error(response.data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật lịch hẹn';
            toast.error(errorMessage);
            dropInfo.revert();

            // Reload lại trang khi có lỗi
            router.visit(route('appointments.index'), {
                preserveScroll: true,
                preserveState: false,
                only: ['appointments']
            });
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
        title: `${newAppointment.user?.full_name || 'N/A'} - ${newAppointment.service?.service_name || 'N/A'}`,
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
            serviceName: newAppointment.service?.service_name,
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

// Thêm hàm khởi tạo tooltip
function initializeTooltip(element, event) {
    const tooltipContent = `
        <div class="p-2">
            <div class="font-bold">${event.extendedProps.userName || 'Không xác định'}</div>
            <div>Dịch vụ: ${event.extendedProps.serviceName || 'Không xác định'}</div>
            <div>Nhân viên: ${event.extendedProps.staffName || 'Không xác định'}</div>
            <div>Trạng thái: ${event.extendedProps.status}</div>
            <div>Số slot: ${event.extendedProps.slots || 1}</div>
            ${event.extendedProps.status === 'cancelled' ? `
                <div class="mt-2 pt-2 border-t">
                    <div>Hủy bởi: ${event.extendedProps.cancelledByUser?.full_name || 'Không xác định'}</div>
                    <div>Thời gian hủy: ${formatDateTime(event.extendedProps.cancelledAt)}</div>
                    ${event.extendedProps.cancellationNote ? `<div>Lý do: ${event.extendedProps.cancellationNote}</div>` : ''}
                </div>
            ` : ''}
        </div>
    `;

    const tippyInstance = tippy(element, {
        content: tooltipContent,
        allowHTML: true,
        placement: 'top',
        theme: 'light-border',
        delay: [200, 0],
        interactive: true,
        zIndex: 999999,
        appendTo: document.body,
        popperOptions: {
            modifiers: [
                {
                    name: 'preventOverflow',
                    options: {
                        altAxis: true,
                        padding: 5
                    }
                }
            ]
        }
    });

    element._tippy = tippyInstance;
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
/* Base styles */
.calendar-container {
    min-height: calc(100vh - 200px);
}

.custom-calendar {
    height: 100%;
}

/* Slot styling */
.fc-timegrid-slot {
    height: 60px !important;
}

/* Calendar event styles */
.calendar-event {
    position: absolute !important;
    margin: 0 !important;
    border-radius: 0.25rem !important;
    padding: 0.25rem !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

/* Event container styles */
.fc-timegrid-event-harness {
    margin: 0 !important;
    box-sizing: border-box !important;
}

/* Ensure exact height calculation */
.fc-timegrid-event {
    box-sizing: border-box !important;
    margin: 0 !important;
    border-width: 1px !important;
}

/* Styles specific to timeGrid view */
.fc-view-timeGridWeek .full-slot-event,
.fc-view-timeGridDay .full-slot-event {
    width: 100% !important;
    height: 100% !important;
    left: 0 !important;
    right: 0 !important;
}

.fc-view-timeGridWeek .half-slot-event,
.fc-view-timeGridDay .half-slot-event {
    width: 50% !important;
    height: 100% !important;
}

/* First half slot event */
.fc-view-timeGridWeek .half-slot-event:first-child,
.fc-view-timeGridDay .half-slot-event:first-child {
    left: 0 !important;
    right: 50% !important;
}

/* Second half slot event */
.fc-view-timeGridWeek .half-slot-event:not(:first-child),
.fc-view-timeGridDay .half-slot-event:not(:first-child) {
    left: 50% !important;
    right: 0 !important;
}

/* Status colors */
.status-pending {
    @apply bg-amber-400 border-amber-500 text-black !important;
}

.status-confirmed {
    @apply bg-emerald-400 border-emerald-500 text-black !important;
}

.status-cancelled {
    @apply bg-red-500 border-red-600 text-white line-through opacity-80 !important;
}

.status-completed {
    @apply bg-primary-500 border-primary-600 text-white !important;
}

/* Event title */
.fc-event-title {
    font-size: 0.75rem !important;
    line-height: 1.2 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

/* Hover effect */
.calendar-event:hover {
    filter: brightness(0.95);
}

/* Dark mode support */
.dark .fc {
    @apply bg-dark-surface text-dark-text;
    --fc-border-color: theme('colors.dark-border.DEFAULT');
    --fc-page-bg-color: theme('colors.dark-surface.DEFAULT');
    --fc-neutral-bg-color: theme('colors.dark-surface.hover');
    --fc-list-event-hover-bg-color: theme('colors.dark-surface.hover');
    --fc-today-bg-color: theme('colors.primary.900/10');
}

/* Thêm styles mới cho tippy */
.tippy-box {
    z-index: 999999 !important;
    position: relative !important;
}

.tippy-box[data-placement^='top']>.tippy-arrow::before {
    z-index: 999999 !important;
}

/* Đảm bảo header calendar không che tooltip */
.fc-header-toolbar {
    z-index: 2 !important;
    position: relative !important;
}

.fc-event.fc-dragging {
    opacity: 0.8;
    cursor: move !important;
    pointer-events: none;
    /* Tránh interference với các elements khác */
}

.fc-timegrid-event {
    transition: transform 0.05s ease;
    /* Thêm transition nhẹ để giảm giật */
}

/* Reset styles cho chế độ xem danh sách và tháng */
.fc-dayGridMonth-view .calendar-event,
.fc-listWeek-view .calendar-event {
    position: relative !important;
    width: auto !important;
    height: auto !important;
}

/* Chỉ áp dụng custom styles cho timeGrid views */
.fc-timegrid-view .calendar-event {
    position: absolute !important;
    margin: 0 !important;
    border-radius: 0.25rem !important;
    padding: 0.25rem !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

/* Đảm bảo styles cho full-slot và half-slot chỉ áp dụng cho timeGrid */
.fc-timegrid-view .full-slot-event {
    width: 100% !important;
    height: 100% !important;
    left: 0 !important;
    right: 0 !important;
}

.fc-timegrid-view .half-slot-event {
    width: 50% !important;
    height: 100% !important;
}

/* Reset styles cho event harness trong các chế độ xem khác */
.fc-dayGridMonth-view .fc-event-harness,
.fc-listWeek-view .fc-event-harness {
    margin: inherit;
}
</style>
