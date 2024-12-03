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
use App\Models\UserServicePackage;
use App\Models\ServiceUsageHistory;
use App\Services\NotificationService;

class AppointmentService
{
    protected $firebaseService;
    protected $notificationService;

    // Thêm constant cho các loại lịch hẹn
    const APPOINTMENT_TYPES = [
        'service' => 'Thực hiện dịch vụ',
        'service_package' => 'Thực hiện combo',
        'consultation' => 'Tư vấn',
        'others' => 'Khác'
    ];

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
                // Validate appointment type
                if (
                    !isset($data['appointment_type']) ||
                    !array_key_exists($data['appointment_type'], self::APPOINTMENT_TYPES)
                ) {
                    throw new \Exception('Loại lịch hẹn không hợp lệ');
                }

                // Validate service or package selection based on appointment type
                if ($data['appointment_type'] === 'service' && empty($data['service_id'])) {
                    throw new \Exception('Vui lòng chọn dịch vụ');
                }

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

                // Ensure the total slots do not exceed 2
                if ($existingBookings + $requestedSlots > 2) {
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

                $appointmentData = [
                    'user_id' => $userId,
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

                // Nếu là appointment type service_package và có user_service_package_id
                if (
                    $appointment->appointment_type === 'service_package' &&
                    isset($data['user_service_package_id'])
                ) {
                    $package = UserServicePackage::findOrFail($data['user_service_package_id']);

                    // Chỉ kiểm tra số lần còn lại, không trừ số lần sử dụng
                    if ($package->remaining_sessions < 1) {
                        throw new \Exception('Không đủ số lần trong gói combo');
                    }

                    // Chỉ liên kết gói dịch vụ với lịch hẹn
                    $appointment->userServicePackages()->attach($package->id, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Load relationships
                $appointment->load(['user', 'service', 'timeSlot']);

                // Gửi thông báo cho khách hàng
                $appointmentDate = Carbon::parse($appointment->appointment_date)->format('d/m/Y');
                $appointmentTime = $appointment->timeSlot->start_time;

                $this->notificationService->createNotification([
                    'user_id' => $appointment->user_id,
                    'type' => 'appointment_new',
                    'data' => [
                        'date' => "{$appointmentDate} {$appointmentTime}",
                        'id' => $appointment->id
                    ],
                    'send_fcm' => true
                ]);

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

                return [
                    'status' => 200,
                    'message' => 'Đặt lịch thành công',
                    'data' => $appointment
                ];
            } catch (\Exception $e) {
                Log::error('Error in createAppointment:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return [
                    'status' => 500,
                    'message' => $e->getMessage(),
                    'data' => null
                ];
            }
        });
    }

    public function updateAppointment($id, $data)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $oldStatus = $appointment->status;

            // Nếu đang cập nhật status thành completed và là service_package
            if (
                isset($data['status']) &&
                $data['status'] === 'completed' &&
                $appointment->appointment_type === 'service_package'
            ) {
                // Tìm gói dịch vụ liên quan
                $package = $appointment->userServicePackage;
                if ($package) {
                    // Tạo service usage history
                    ServiceUsageHistory::create([
                        'user_service_package_id' => $package->id,
                        'used_date' => now(),
                        'staff_user_id' => $appointment->staff_user_id,
                        'note' => $data['note'] ?? "Hoàn thành lịch hẹn #{$appointment->id}",
                        'appointment_id' => $appointment->id
                    ]);
                }
            }

            // Chỉ cập nhật các trường được phép
            $updateData = array_filter([
                'staff_user_id' => $data['staff_id'] ?? null,
                'appointment_date' => $data['appointment_date'] ?? null,
                'time_slot_id' => $data['time_slot_id'] ?? null,
                'status' => strtolower($data['status'] ?? null), // Đảm bảo status luôn là chữ thường
                'note' => $data['note'] ?? null,
                'appointment_type' => $data['appointment_type'] ?? null,
            ]);

            // Check if time slot is available for update
            if (isset($data['time_slot_id']) && isset($data['appointment_date'])) {
                $timeSlot = TimeSlot::findOrFail($data['time_slot_id']);
                $requestedSlots = $data['slots'] ?? 1;

                // Count existing bookings for this date and time slot
                $existingBookings = Appointment::where('appointment_date', $data['appointment_date'])
                    ->where('time_slot_id', $data['time_slot_id'])
                    ->where('status', '!=', 'cancelled')
                    ->where('id', '!=', $id) // Exclude current appointment
                    ->sum('slots');

                // Ensure the total slots do not exceed 2
                if ($existingBookings + $requestedSlots > 2) {
                    return [
                        'status' => 422,
                        'message' => 'Không đủ slot trống trong khung giờ này',
                        'data' => null,
                        'success' => false
                    ];
                }
            }

            // Cập nhật appointment
            $appointment->update($updateData);

            // Load relationships
            $appointment->load(['user', 'service', 'staff', 'timeSlot']);

            // Gửi thông báo cho khách hàng
            $this->notificationService->createNotification([
                'user_id' => $appointment->user_id,
                'type' => NotificationService::NOTIFICATION_TYPES['appointment']['status'],
                'data' => [
                    'id' => $appointment->id,
                    'status' => $this->getStatusTranslation($data['status'])
                ]
            ]);

            // Thông báo cho admin nếu khách hàng hủy lịch
            if ($data['status'] === 'cancelled') {
                $this->notificationService->createNotification([
                    'user_id' => $appointment->user_id,
                    'type' => NotificationService::NOTIFICATION_TYPES['appointment']['cancelled'],
                    'data' => [
                        'id' => $appointment->id
                    ]
                ]);
            }

            // Nếu thay đổi gói dịch vụ
            if (isset($data['user_service_package_id'])) {
                // Xóa liên kết cũ
                $appointment->userServicePackages()->detach();

                // Thêm liên kết mới
                if ($data['user_service_package_id']) {
                    $appointment->userServicePackages()->attach($data['user_service_package_id'], [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
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

    private function getStatusTranslation($status)
    {
        $translations = [
            'confirmed' => [
                'en' => 'confirmed',
                'vi' => 'đã xác nhận',
                'ja' => '確認済み'
            ],
            'cancelled' => [
                'en' => 'cancelled',
                'vi' => 'đã hủy',
                'ja' => 'キャンセル済み'
            ],
            'completed' => [
                'en' => 'completed',
                'vi' => 'đã hoàn thành',
                'ja' => '完了'
            ]
        ];

        return $translations[$status] ?? $status;
    }

    public function getAppointmentDetails($id)
    {
        try {
            $appointment = Appointment::with([
                'user',
                'service',
                'staff',
                'timeSlot',
                'cancelledBy'
            ])->findOrFail($id);

            // Format the response using the accessor
            $formattedAppointment = $appointment->getFormattedAppointmentAttribute();

            // Add additional data specific to appointment details
            $formattedAppointment = array_merge($formattedAppointment, [
                'user' => [
                    'id' => $appointment->user->id,
                    'full_name' => $appointment->user->full_name,
                ],
                'cancelled_by_user' => $appointment->cancelled_by ? [
                    'id' => $appointment->cancelledBy->id,
                    'full_name' => $appointment->cancelledBy->full_name
                ] : null
            ]);

            return [
                'status' => 200,
                'message' => 'Lấy thông tin cuộc hẹn thành công',
                'data' => $formattedAppointment
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

    public function cancelAppointment($id, $note, $isAutoCancel = false)
    {
        try {
            $appointment = Appointment::with('timeSlot')->findOrFail($id);

            // Nếu là hủy tự động, bỏ qua các kiểm tra và quyền và thời gian
            if (!$isAutoCancel) {
                $currentUser = Auth::user();

                // Kiểm tra quyền hủy cuộc hẹn
                if ($appointment->user_id !== $currentUser->id && $currentUser->role !== 'admin') {
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
            }

            $appointment->update([
                'status' => 'cancelled',
                'cancelled_by' => $isAutoCancel ? null : Auth::id(),
                'cancelled_at' => now(),
                'cancellation_note' => $note,
            ]);

            // Gửi thông báo cho khách hàng
            $this->notificationService->createNotification([
                'user_id' => $appointment->user_id,
                'type' => NotificationService::NOTIFICATION_TYPES['appointment']['cancelled'],
                'data' => [
                    'id' => $appointment->id
                ]
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
            $query = Appointment::with([
                'service',
                'staff',
                'timeSlot',
                'cancelledBy'
            ])->where('user_id', $userId);

            // Add filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['from_date'])) {
                $query->where('appointment_date', '>=', $filters['from_date']);
            }
            if (!empty($filters['to_date'])) {
                $query->where('appointment_date', '<=', $filters['to_date']);
            }

            $appointments = $query->get();

            // Format appointments with cancellation info
            $formattedAppointments = $appointments->map(function ($appointment) {
                $baseFormat = $appointment->getFormattedAppointmentAttribute();

                // Add cancelled_by_user information separately
                return array_merge($baseFormat, [
                    'cancelled_by_user' => $appointment->cancelledBy ? [
                        'id' => $appointment->cancelledBy->id,
                        'full_name' => $appointment->cancelledBy->full_name
                    ] : null
                ]);
            })->toArray();

            return [
                'status' => 200,
                'message' => 'Lấy danh sách lịch hẹn thành công',
                'data' => $formattedAppointments
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

    // Thêm helper method để lấy danh sách loại lịch hẹn
    public function getAppointmentTypes()
    {
        return self::APPOINTMENT_TYPES;
    }
}
