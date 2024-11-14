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
    protected $firebaseService;
    protected $notificationService;

    public function __construct(
        FirebaseService $firebaseService,
        NotificationService $notificationService
    ) {
        $this->firebaseService = $firebaseService;
        $this->notificationService = $notificationService;
    }

    public function getAppointments($request)
    {
        if (Auth::user()->role !== 'admin') {
            throw new \Exception('Unauthorized access');
        }

        $query = Appointment::with([
            'user',
            'service',
            'staff',
            'timeSlot'
        ]);

        // Add date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('appointment_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Add status filter
        if ($request->has('status')) {
            $query->where('status', strtolower($request->status));
        }

        return $query->orderBy('appointment_date')
            ->orderBy('time_slot_id')
            ->get();
    }

    public function createAppointment($data)
    {
        return DB::transaction(function () use ($data) {
            try {
                // Validate and convert types for numeric fields
                $data['service_id'] = (int) $data['service_id'];
                $data['slots'] = (int) $data['slots'];
                $data['time_slot_id'] = (int) $data['time_slot_id'];

                // Xác định user_id (giữ nguyên dạng string nếu là UUID)
                $userId = $data['user_id'] ?? Auth::id();
                if (!$userId) {
                    throw new \Exception('Không tìm thấy thông tin người dùng');
                }

                // Check if time slot is available
                $timeSlot = TimeSlot::findOrFail($data['time_slot_id']);
                $requestedSlots = $data['slots'] ?? 1;

                // Count existing bookings for this date and time slot
                $existingBookings = Appointment::where('appointment_date', $data['appointment_date'])
                    ->where('time_slot_id', $data['time_slot_id'])
                    ->where('status', '!=', 'cancelled')
                    ->sum('slots');

                $remainingSlots = $timeSlot->max_bookings - $existingBookings;

                if ($requestedSlots > $remainingSlots) {
                    return [
                        'status' => 422,
                        'message' => 'Không đủ slot trống trong khung giờ này',
                        'data' => null
                    ];
                }

                // Find available staff
                $availableStaff = User::where('role', 'staff')
                    ->whereNull('deleted_at')
                    ->first();

                if (!$availableStaff) {
                    return [
                        'status' => 422,
                        'message' => 'Không tìm thấy nhân viên phù hợp',
                        'data' => null
                    ];
                }

                // Create appointment with explicit data mapping
                $appointmentData = [
                    'user_id' => $userId, // Giữ nguyên dạng UUID
                    'service_id' => $data['service_id'],
                    'staff_user_id' => $availableStaff->id,
                    'appointment_date' => $data['appointment_date'],
                    'time_slot_id' => $data['time_slot_id'],
                    'appointment_type' => $data['appointment_type'],
                    'status' => $data['status'] ?? 'pending',
                    'note' => $data['note'] ?? null,
                    'slots' => $requestedSlots,
                ];

                $appointment = Appointment::create($appointmentData);

                // Load relationships
                $appointment->load(['user', 'service', 'timeSlot']);

                // Gửi thông báo cho admin
                $this->notificationService->notifyAdmins(
                    'Lịch hẹn mới',
                    "Khách hàng {$appointment->user->full_name} đặt lịch {$appointment->service->name}",
                    'new_appointment',
                    [
                        'type' => 'appointment',
                        'appointment_id' => $appointment->id,
                        'action' => 'created'
                    ]
                );

                // Gửi thông báo cho khách hàng
                $this->notificationService->createNotification([
                    'user_id' => $appointment->user_id,
                    'title' => 'Đặt lịch thành công',
                    'content' => "Bạn đã đặt lịch {$appointment->service->name} vào ngày {$appointment->appointment_date}",
                    'type' => 'appointment_created'
                ]);

                return [
                    'status' => 200,
                    'message' => 'Đặt lịch thành công',
                    'data' => $appointment
                ];
            } catch (\Exception $e) {
                // Ghi log chi tiết lỗi
                Log::error('Error creating appointment:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => json_encode($data) // Encode data to prevent array conversion issues
                ]);

                throw new \Exception('Có lỗi xảy ra khi đặt lịch hẹn: ' . $e->getMessage());
            }
        });
    }

    public function updateAppointment($id, $data)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $oldStatus = $appointment->status;

            // Chỉ cập nhật các trường được phép
            $updateData = array_filter([
                'staff_user_id' => $data['staff_id'] ?? null,
                'appointment_date' => $data['appointment_date'] ?? null,
                'time_slot_id' => $data['time_slot_id'] ?? null,
                'status' => strtolower($data['status'] ?? null), // Đảm bảo status luôn là chữ thường
                'note' => $data['note'] ?? null,
                'appointment_type' => $data['appointment_type'] ?? null,
            ]);

            // Cập nhật appointment
            $appointment->update($updateData);

            // Load relationships
            $appointment->load(['user', 'service', 'staff', 'timeSlot']);

            // Gửi thông báo khi trạng thái thay đổi
            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                $statusMessage = $this->getStatusMessage($data['status']);

                // Thông báo cho khách hàng
                $this->notificationService->createNotification([
                    'user_id' => $appointment->user_id,
                    'title' => 'Cập nhật lịch hẹn',
                    'content' => $statusMessage,
                    'type' => 'appointment_status_changed'
                ]);

                // Thông báo cho admin nếu khách hàng hủy lịch
                if ($data['status'] === 'cancelled') {
                    $this->notificationService->notifyAdmins(
                        'Lịch hẹn bị hủy',
                        "Khách hàng {$appointment->user->full_name} đã hủy lịch hẹn {$appointment->service->name}",
                        'appointment_cancelled',
                        [
                            'type' => 'appointment',
                            'appointment_id' => $appointment->id,
                            'action' => 'cancelled'
                        ]
                    );
                }
            }

            return [
                'status' => 200,
                'message' => 'Cập nhật lịch hẹn thành công',
                'data' => $appointment,
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::error('Error updating appointment:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi khi cập nhật lịch hẹn',
                'data' => null,
                'success' => false
            ];
        }
    }

    private function getStatusMessage($status)
    {
        switch ($status) {
            case 'confirmed':
                return 'Lịch hẹn của bạn đã được xác nhận';
            case 'cancelled':
                return 'Lịch hẹn của bạn đã bị hủy';
            case 'completed':
                return 'Lịch hẹn của bạn đã hoàn thành';
            default:
                return 'Trạng thái lịch hẹn đã được cập nhật';
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
            Log::info('Starting getAppointmentsByUser', [
                'user_id' => $userId,
                'filters' => $filters
            ]);

            $query = Appointment::with(['service', 'staff', 'timeSlot'])
                ->where('user_id', $userId);

            // Filter by status if provided and not empty
            if (!empty($filters['status'])) {
                $query->where('status', strtolower($filters['status']));
            }

            // Filter by appointment type if provided and not empty
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

            // Add order by
            $query->orderBy('appointment_date', 'desc')
                ->orderBy('created_at', 'desc');

            $appointments = $query->get();

            // Sử dụng accessor từ model
            $formattedAppointments = $appointments->map->getFormattedAppointmentAttribute();

            return [
                'status' => 200,
                'message' => 'Lấy danh sách lịch hẹn thành công',
                'data' => $formattedAppointments
            ];
        } catch (\Exception $e) {
            Log::error('Error in getAppointmentsByUser:', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách lịch hẹn',
                'data' => []
            ];
        }
    }

    public function getMyAppointments($userId, array $filters = [])
    {
        try {
            $query = Appointment::with(['service', 'staff', 'timeSlot'])
                ->where('user_id', $userId);

            // Filter by status
            if (!empty($filters['status'])) {
                $query->where('status', strtolower($filters['status']));
            }

            // Filter by date range
            if (!empty($filters['from_date'])) {
                $query->where('appointment_date', '>=', $filters['from_date']);
            }
            if (!empty($filters['to_date'])) {
                $query->where('appointment_date', '<=', $filters['to_date']);
            }

            // Add sorting
            $query->orderBy('appointment_date', 'desc')
                ->orderBy('created_at', 'desc');

            $appointments = $query->get();

            return [
                'status' => 200,
                'message' => 'Lấy danh sách lịch hẹn thành công',
                'data' => $appointments->map->getFormattedAppointmentAttribute()
            ];
        } catch (\Exception $e) {
            Log::error('Error in getMyAppointments:', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách lịch hẹn',
                'data' => []
            ];
        }
    }
}
