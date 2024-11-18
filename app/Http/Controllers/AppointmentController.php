<?php

namespace App\Http\Controllers;

use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use OpenApi\Annotations as OA;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\UserServicePackage;

/**
 * @OA\Tag(
 *     name="Appointments",
 *     description="API Endpoints của Appointment"
 * )
 */
class AppointmentController extends BaseController
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * @OA\Get(
     *     path="/api/appointments",
     *     summary="Lấy danh sách lịch hẹn",
     *     tags={"Appointments"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Ngày bắt đầu (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date", 
     *         in="query",
     *         description="Ngày kết thúc (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query", 
     *         description="Trạng thái lịch hẹn",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "cancelled", "completed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách lịch hẹn thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="start", type="string", format="date-time"),
     *                     @OA\Property(property="end", type="string", format="date-time"),
     *                     @OA\Property(property="user", ref="#/components/schemas/User"),
     *                     @OA\Property(property="service", ref="#/components/schemas/Service"),
     *                     @OA\Property(property="staff", ref="#/components/schemas/User"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="appointment_type", type="string"),
     *                     @OA\Property(property="note", type="string"),
     *                     @OA\Property(property="time_slot", ref="#/components/schemas/TimeSlot")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            // Check if user is admin
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized access',
                    'status' => 403
                ], 403);
            }

            $appointments = $this->appointmentService->getAppointments($request);

            // Format appointments for calendar view
            $formattedAppointments = $appointments->map(function ($appointment) {
                $timeSlot = $appointment->timeSlot;

                // Combine appointment date with time slot times
                $startDateTime = Carbon::parse($appointment->appointment_date)
                    ->setTimeFromTimeString($timeSlot->start_time)
                    ->setTimezone('Asia/Ho_Chi_Minh');

                $endDateTime = Carbon::parse($appointment->appointment_date)
                    ->setTimeFromTimeString($timeSlot->end_time)
                    ->setTimezone('Asia/Ho_Chi_Minh');

                return [
                    'id' => $appointment->id,
                    'title' => $appointment->user->full_name ?? 'Unknown',
                    'start' => $startDateTime->format('Y-m-d H:i:s'),
                    'end' => $endDateTime->format('Y-m-d H:i:s'),
                    'user' => $appointment->user,
                    'service' => $appointment->service,
                    'staff' => $appointment->staff,
                    'status' => $appointment->status,
                    'appointment_type' => $appointment->appointment_type,
                    'note' => $appointment->note,
                    'time_slot' => $timeSlot,
                ];
            });

            if ($request->expectsJson()) {
                return $this->respondWithJson($formattedAppointments, 'Lấy danh sách cuộc hẹn thành công');
            }

            // Get time slots for calendar
            $timeSlots = TimeSlot::where('is_active', true)
                ->orderBy('start_time')
                ->get()
                ->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'max_bookings' => $slot->max_bookings
                    ];
                });

            return $this->respondWithInertia('Calendar/CalendarView', [
                'appointments' => $formattedAppointments,
                'timeSlots' => $timeSlots,
                'businessHours' => [
                    'start' => '08:00',
                    'end' => '18:30',
                    'daysOfWeek' => [0, 1, 2, 3, 4, 5, 6]
                ],
                'slotDuration' => '01:00:00',
                'initialView' => 'timeGridWeek',
                'slotMinTime' => '08:00:00',
                'slotMaxTime' => '18:30:00',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/appointments",
     *     summary="Tạo lịch hẹn mới",
     *     tags={"Appointments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","service_id","staff_id","appointment_date","time_slot_id","appointment_type","status"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="service_id", type="integer", example=1),
     *             @OA\Property(property="staff_id", type="integer", example=1),
     *             @OA\Property(property="appointment_date", type="string", format="date", example="2024-03-20"),
     *             @OA\Property(property="time_slot_id", type="integer", example=1),
     *             @OA\Property(property="appointment_type", type="string", example="consultation"),
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="note", type="string", example="Ghi chú cho lịch hẹn", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đặt lịch thành công"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Khung giờ này đã đầy"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Validate request using AppointmentRequest
            $validator = new AppointmentRequest();
            $validated = validator($request->all(), $validator->rules(), $validator->messages())->validate();

            // Prepare appointment data from validated input
            $appointmentData = [
                'user_id' => $validated['user_id'],
                'appointment_date' => $validated['appointment_date'],
                'time_slot_id' => $validated['time_slot_id'],
                'appointment_type' => $validated['appointment_type'],
                'status' => $validated['status'],
                'slots' => $validated['slots'],
                'note' => $validated['note'] ?? null,
                'staff_user_id' => $validated['staff_id']
            ];

            // Add service_id or user_service_package_id based on appointment type
            if ($validated['appointment_type'] === 'service' && isset($validated['service_id'])) {
                $appointmentData['service_id'] = $validated['service_id'];
            } elseif ($validated['appointment_type'] === 'service_package' && isset($validated['user_service_package_id'])) {
                $userServicePackage = UserServicePackage::findOrFail($validated['user_service_package_id']);
                $appointmentData['service_id'] = $userServicePackage->service_id;
                $appointmentData['user_service_package_id'] = $validated['user_service_package_id'];
            }

            $result = $this->appointmentService->createAppointment($appointmentData);

            return $this->respondWithJson(
                $result['data'] ?? null,
                $result['message'] ?? 'Đặt lịch hẹn thành công',
                $result['status'] ?? 200
            );
        } catch (\Exception $e) {
            Log::error('Error creating appointment: ' . $e->getMessage());
            return $this->respondWithJson(
                null,
                'Có lỗi xảy ra khi đặt lịch hẹn: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/api/appointments/{id}",
     *     summary="Cập nhật lịch hẹn",
     *     tags={"Appointments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của lịch hẹn",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="staff_id", type="integer", example=1),
     *             @OA\Property(property="appointment_date", type="string", format="date", example="2024-03-20"),
     *             @OA\Property(property="time_slot_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"pending", "confirmed", "cancelled", "completed"}),
     *             @OA\Property(property="appointment_type", type="string", example="consultation"),
     *             @OA\Property(property="note", type="string", example="Ghi chú cập nhật")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cập nhật lịch hẹn thành công"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $result = $this->appointmentService->updateAppointment($id, $request->all());

            if (!$result['success']) {
                return $this->respondWithJson(
                    null,
                    $result['message'] ?? 'Có lỗi xảy ra khi cập nhật lịch hẹn',
                    422
                );
            }

            return $this->respondWithJson(
                $result['data'],
                'Cập nhật lịch hẹn thành công'
            );
        } catch (\Exception $e) {
            Log::error('Error updating appointment: ' . $e->getMessage());
            return $this->respondWithJson(
                null,
                'Có lỗi xảy ra khi cập nhật lịch hẹn: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/appointments/{id}",
     *     summary="Lấy thông tin chi tiết cuộc hẹn",
     *     tags={"Appointments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trả về thông tin chi tiết cuộc hẹn",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Appointment"),
     *                     @OA\Schema(
     *                         @OA\Property(property="cancelled_by", type="string", nullable=true),
     *                         @OA\Property(property="cancelled_at", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="cancellation_note", type="string", nullable=true),
     *                         @OA\Property(
     *                             property="cancelled_by_user",
     *                             type="object",
     *                             nullable=true,
     *                             @OA\Property(property="id", type="string"),
     *                             @OA\Property(property="full_name", type="string")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy cuộc hẹn"
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        $result = $this->appointmentService->getAppointmentDetails($id);

        // Nếu là request API thì trả về JSON
        if ($request->expectsJson()) {
            return response()->json($result);
        }

        // Nếu không phải API request thì render trang chi tiết bằng Inertia
        if ($result['status'] === 200 && isset($result['data'])) {
            // Lấy appointment từ database một lần nữa để có thể sử dụng relationship loading
            $appointment = Appointment::with(['user', 'service', 'staff', 'timeSlot', 'cancelledBy'])
                ->find($result['data']['id']);

            if (!$appointment) {
                return redirect()->route('appointments.index')
                    ->with('error', 'Không tìm thấy lịch hẹn');
            }

            return $this->respondWithInertia('Calendar/Components/AppointmentDetails', [
                'appointment' => $appointment
            ]);
        }

        // Nếu có lỗi thì redirect về trang danh sách với thông báo
        return redirect()->route('appointments.index')
            ->with('error', $result['message'] ?? 'Đã xảy ra lỗi');
    }

    /**
     * @OA\Delete(
     *     path="/api/appointments/{id}",
     *     summary="Xóa cuộc hẹn",
     *     tags={"Appointments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trả về thông báo xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Khng tìm thấy cuộc hẹn"
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        $result = $this->appointmentService->deleteAppointment($id);

        if ($request->expectsJson()) {
            return $this->respondWithJson($result['data'], $result['message'], $result['status']);
        }

        return redirect()->route('appointments.index')->with('success', $result['message']);
    }

    // Phương thức cho web (Inertia)
    public function create()
    {
        return $this->respondWithInertia('Appointments/Create');
    }

    public function edit($id)
    {
        $appointment = $this->appointmentService->getAppointmentDetails($id);
        return $this->respondWithInertia('Appointments/Edit', ['appointment' => $appointment['data']]);
    }

    /**
     * @OA\Put(
     *     path="/api/appointments/{id}/cancel",
     *     summary="Huỷ lịch hẹn",
     *     tags={"Appointments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của lịch hẹn",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="note", type="string", example="Lý do huỷ lịch hẹn")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Huỷ lịch hẹn thành công"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền huỷ lịch hẹn này"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi nghiệp vụ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không thể huỷ lịch hẹn đã bắt đầu hoặc đã kết thúc"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function cancel(Request $request, $id)
    {
        try {
            $result = $this->appointmentService->cancelAppointment($id, $request->input('note'));

            return $this->respondWithJson(
                $result['data'],
                $result['message'] ?? 'Hủy lịch hẹn thành công',
                $result['status'] ?? 200
            );
        } catch (\Exception $e) {
            Log::error('Error cancelling appointment: ' . $e->getMessage());
            return $this->respondWithJson(
                null,
                'Có lỗi xảy ra khi hủy lịch hẹn: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/my-appointments",
     *     summary="Lấy danh sách lịch hẹn của người dùng đăng nhập",
     *     tags={"Appointments"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Lọc theo trạng thái",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "confirmed", "cancelled", "completed"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Lọc từ ngày (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Lọc đến ngày (Y-m-d)", 
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="appointment_type",
     *         in="query",
     *         description="Lọc theo loại lịch hẹn",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"consultation", "treatment", "follow_up", "others"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách lịch hẹn thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="start", type="string", format="date-time"),
     *                     @OA\Property(property="end", type="string", format="date-time"),
     *                     @OA\Property(property="service", ref="#/components/schemas/Service"),
     *                     @OA\Property(property="staff", ref="#/components/schemas/User"),
     *                     @OA\Property(property="status", type="string", example="confirmed"),
     *                     @OA\Property(property="appointment_type", type="string", example="facial"),
     *                     @OA\Property(property="note", type="string", example="Customer note"),
     *                     @OA\Property(property="time_slot", ref="#/components/schemas/TimeSlot"),
     *                     @OA\Property(property="cancelled_by", type="string", nullable=true),
     *                     @OA\Property(property="cancelled_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="cancellation_note", type="string", nullable=true),
     *                     @OA\Property(
     *                         property="cancelled_by_user",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="full_name", type="string", example="User Name")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi khi lấy danh sách lịch hẹn"),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function getMyAppointments(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->respondWithJson(null, 'Unauthorized', 401);
            }

            $filters = array_filter([
                'status' => $request->input('status'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date')
            ]);

            $result = $this->appointmentService->getMyAppointments($user->id, $filters);

            return $this->respondWithJson(
                $result['data'],
                $result['message'] ?? 'Lấy danh sách lịch hẹn thành công',
                $result['status'] ?? 200
            );
        } catch (\Exception $e) {
            Log::error('Error getting appointments: ' . $e->getMessage());
            return $this->respondWithJson(
                null,
                'Đã xảy ra lỗi khi lấy danh sách lịch hẹn: ' . $e->getMessage(),
                500
            );
        }
    }
}
