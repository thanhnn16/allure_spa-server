<?php

namespace App\Services;

use App\Models\Appointment;
use App\Events\AppointmentCreated;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\TimeSlot;
use App\Models\Service;
use App\Models\User;

class AppointmentService
{
    public function getAppointments($request)
    {
        $query = Appointment::with([
            'user',
            'service',
            'staff',
            'timeSlot'
        ]);

        // Thêm filter theo thời gian
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('appointment_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Thêm filter theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('appointment_date')
            ->orderBy('time_slot_id')
            ->get();
    }

    public function createAppointment($data)
    {
        return DB::transaction(function () use ($data) {
            // Check if time slot is available
            $timeSlot = TimeSlot::findOrFail($data['time_slot_id']);

            // Count existing bookings for this date and time slot
            $existingBookings = Appointment::where('appointment_date', $data['appointment_date'])
                ->where('time_slot_id', $data['time_slot_id'])
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($existingBookings >= $timeSlot->max_bookings) {
                return [
                    'status' => 422,
                    'message' => 'Khung giờ này đã đầy',
                    'data' => null
                ];
            }

            // Find available staff
            $staffId = null;
            $staff = User::where('role', 'staff')
                ->first();
            if ($staff) {
                $staffId = $staff->id;
            }

            $appointment = Appointment::create([
                'user_id' => $data['user_id'],
                'service_id' => $data['service_id'],
                'staff_user_id' => $staffId,
                'appointment_date' => $data['appointment_date'],
                'time_slot_id' => $data['time_slot_id'],
                'appointment_type' => $data['appointment_type'],
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
            ]);

            event(new AppointmentCreated($appointment));

            return [
                'status' => 200,
                'message' => 'Đặt lịch thành công',
                'data' => $appointment->load(['user', 'service', 'timeSlot'])
            ];
        });
    }

    public function updateAppointment($id, $data)
    {
        $validator = Validator::make($data, [
            'staff_id' => 'sometimes|exists:users,id',
            'appointment_date' => 'sometimes|date',
            'time_slot_id' => 'sometimes|exists:time_slots,id',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'note' => 'nullable|string',
            'appointment_type' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return ['status' => 422, 'message' => 'Validation failed', 'data' => $validator->errors()];
        }

        try {
            $appointment = Appointment::findOrFail($id);

            // If changing time slot, check availability
            if (isset($data['time_slot_id']) && isset($data['appointment_date'])) {
                $timeSlot = TimeSlot::findOrFail($data['time_slot_id']);
                $existingBookings = Appointment::where('appointment_date', $data['appointment_date'])
                    ->where('time_slot_id', $data['time_slot_id'])
                    ->where('id', '!=', $id)
                    ->where('status', '!=', 'cancelled')
                    ->count();

                if ($existingBookings >= $timeSlot->max_bookings) {
                    return [
                        'status' => 422,
                        'message' => 'Khung giờ này đã đầy',
                        'data' => null
                    ];
                }
            }

            $updateData = array_filter([
                'staff_user_id' => $data['staff_id'] ?? null,
                'appointment_date' => $data['appointment_date'] ?? null,
                'time_slot_id' => $data['time_slot_id'] ?? null,
                'status' => $data['status'] ?? null,
                'note' => $data['note'] ?? null,
                'appointment_type' => $data['appointment_type'] ?? null,
            ]);

            $appointment->update($updateData);

            // Load relationships cần thiết
            $appointment->load(['user', 'service', 'staff', 'timeSlot']);

            return [
                'status' => 200,
                'message' => 'Cập nhật lịch hẹn thành công',
                'data' => $appointment
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật lịch hẹn: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi khi cập nhật lịch hẹn',
                'data' => null
            ];
        }
    }

    public function getAppointmentDetails($id)
    {
        try {
            $appointment = Appointment::with(['user', 'service', 'staff'])
                ->findOrFail($id);

            return [
                'status' => 200,
                'message' => 'Lấy thông tin cuộc hẹn thành công',
                'data' => $appointment
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thông tin cuộc hẹn: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi khi lấy thông tin cuộc hẹn',
                'data' => null
            ];
        }
    }

    public function deleteAppointment($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return [
                'status' => 200,
                'message' => 'Xóa cuộc hẹn thành công',
                'data' => null
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa cuộc hẹn: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi khi xóa cuộc hẹn',
                'data' => null
            ];
        }
    }

    public function cancelAppointment($id, $note)
    {
        try {
            $appointment = Appointment::with('timeSlot')->findOrFail($id);

            // Kiểm tra xem người dùng có quyền hủy cuộc hẹn không
            if ($appointment->user_id !== Auth::id()) {
                return [
                    'status' => 403,
                    'message' => 'Bạn không có quyền hủy cuộc hẹn này',
                    'data' => null,
                    'success' => false
                ];
            }

            // Kiểm tra thời gian bắt đầu của cuộc hẹn
            $appointmentStart = Carbon::parse($appointment->appointment_date)
                ->setTimeFromTimeString($appointment->timeSlot->start_time);

            if ($appointmentStart <= now()) {
                return [
                    'status' => 422,
                    'message' => 'Không thể hủy cuộc hẹn đã bắt đầu hoặc đã kết thúc',
                    'data' => null,
                    'success' => false
                ];
            }

            $appointment->update([
                'status' => 'cancelled',
                'note' => $note,
            ]);

            return [
                'status' => 200,
                'message' => 'Hủy cuộc hẹn thành công',
                'data' => $appointment,
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi khi hủy cuộc hẹn: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi khi hủy cuộc hẹn',
                'data' => null,
                'success' => false
            ];
        }
    }

    public function getAvailableTimeSlots($date, $serviceId = null)
    {
        try {
            $timeSlots = TimeSlot::where('is_active', true)
                ->orderBy('start_time')
                ->get();

            // If service ID is provided, filter slots based on service duration
            if ($serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    $timeSlots = $timeSlots->filter(function ($slot) use ($service) {
                        $slotDuration = Carbon::parse($slot->end_time)
                            ->diffInMinutes(Carbon::parse($slot->start_time));
                        return $slotDuration >= $service->duration;
                    });
                }
            }

            // Get existing appointments for the date
            $existingAppointments = Appointment::where('appointment_date', $date)
                ->where('status', '!=', 'cancelled')
                ->get();

            // Mark slots as available/unavailable based on existing appointments
            $timeSlots = $timeSlots->map(function ($slot) use ($existingAppointments) {
                $bookings = $existingAppointments->where('time_slot_id', $slot->id)->count();
                $slot->available = $bookings < $slot->max_bookings;
                return $slot;
            });

            return [
                'status' => 200,
                'message' => 'Time slots retrieved successfully',
                'data' => $timeSlots
            ];
        } catch (\Exception $e) {
            Log::error('Error getting available time slots: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Error retrieving time slots',
                'data' => null
            ];
        }
    }

    public function getAppointmentsByUser($userId, array $filters = [])
    {
        try {
            $query = Appointment::with(['service', 'staff', 'timeSlot'])
                ->where('user_id', $userId);

            // Filter by status
            if (!empty($filters['status'])) {
                $query->where('status', strtolower($filters['status']));
            }

            // Filter by appointment type
            if (!empty($filters['appointment_type'])) {
                $query->where('appointment_type', strtolower($filters['appointment_type']));
            }

            // Filter by date range
            if (!empty($filters['from_date'])) {
                $query->where('appointment_date', '>=', $filters['from_date']);
            }
            if (!empty($filters['to_date'])) {
                $query->where('appointment_date', '<=', $filters['to_date']);
            }

            // Thêm order by
            $query->orderBy('appointment_date', 'desc')
                  ->orderBy('created_at', 'desc');

            $appointments = $query->get();

            // Log để debug
            Log::info('Raw appointments:', ['appointments' => $appointments->toArray()]);

            $formattedAppointments = $appointments->map(function ($appointment) {
                // Log để debug từng appointment
                Log::info('Processing appointment:', ['appointment' => $appointment->toArray()]);
                
                try {
                    return [
                        'id' => $appointment->id,
                        'title' => $appointment->service->name ?? 'Cuộc hẹn',
                        'start' => Carbon::parse($appointment->appointment_date . ' ' . optional($appointment->timeSlot)->start_time)
                            ->setTimezone('Asia/Ho_Chi_Minh')
                            ->format('Y-m-d H:i:s'),
                        'end' => Carbon::parse($appointment->appointment_date . ' ' . optional($appointment->timeSlot)->end_time)
                            ->setTimezone('Asia/Ho_Chi_Minh')
                            ->format('Y-m-d H:i:s'),
                        'service' => $appointment->service,
                        'staff' => $appointment->staff,
                        'status' => $appointment->status,
                        'appointment_type' => $appointment->appointment_type,
                        'note' => $appointment->note,
                        'time_slot' => $appointment->timeSlot,
                        'appointment_date' => $appointment->appointment_date,
                    ];
                } catch (\Exception $e) {
                    Log::error('Error formatting single appointment: ' . $e->getMessage(), [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter()->values();

            // Log kết quả cuối cùng
            Log::info('Formatted appointments:', ['formatted' => $formattedAppointments->toArray()]);

            return [
                'status' => 200,
                'message' => 'Lấy danh sách lịch hẹn thành công',
                'data' => $formattedAppointments
            ];

        } catch (\Exception $e) {
            Log::error('Error in getAppointmentsByUser: ' . $e->getMessage(), [
                'user_id' => $userId,
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách lịch hẹn',
                'data' => null
            ];
        }
    }

}
