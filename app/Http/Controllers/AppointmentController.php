<?php

namespace App\Http\Controllers;

use App\Enums\AuthErrorCode;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AppointmentRequest;
use OpenApi\Annotations as OA;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;

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

            Log::info('Appointments:', $appointments->toArray());

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
    public function store(AppointmentRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->appointmentService->createAppointment($data);

            if ($result['status'] === 422) {
                return response()->json([
                    'message' => $result['message']
                ], 422);
            }

            return response()->json([
                'message' => 'Đặt lịch hẹn thành công',
                'data' => $result['data']
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi đặt lịch hẹn',
                'error' => $e->getMessage()
            ], 500);
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
        $result = $this->appointmentService->updateAppointment($id, $request->all());

        // Log để debug
        Log::info('Update appointment response:', $result);

        return $this->respondWithJson($result['data'], $result['message'], $result['status']);
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
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
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
        if ($result['status'] === 200) {
            return $this->respondWithInertia('Calendar/Components/AppointmentDetails', [
                'appointment' => $result['data']->load(['user', 'service', 'staff', 'timeSlot'])
            ]);
        }

        // Nếu có lỗi thì redirect về trang danh sách với thông báo
        return redirect()->route('appointments.index')->with('error', $result['message']);
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
        $result = $this->appointmentService->cancelAppointment($id, $request->input('note'));
        return $this->respondWithJson($result['data'], $result['message'], $result['status']);
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
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Facial Treatment"),
     *                     @OA\Property(property="start", type="string", format="date-time", example="2024-03-20 09:00:00"),
     *                     @OA\Property(property="end", type="string", format="date-time", example="2024-03-20 10:00:00"),
     *                     @OA\Property(
     *                         property="service",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Facial Treatment"),
     *                         @OA\Property(property="price", type="number", example=500000)
     *                     ),
     *                     @OA\Property(
     *                         property="staff",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="full_name", type="string", example="Staff Name")
     *                     ),
     *                     @OA\Property(property="status", type="string", example="confirmed"),
     *                     @OA\Property(property="appointment_type", type="string", example="facial"),
     *                     @OA\Property(property="note", type="string", example="Customer note"),
     *                     @OA\Property(
     *                         property="time_slot",
     *                         type="object",
     *                         @OA\Property(property="start_time", type="string", example="09:00:00"),
     *                         @OA\Property(property="end_time", type="string", example="10:00:00")
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
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => 401
                ], 401);
            }

            $filters = [
                'status' => $request->status,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date
            ];

            $result = $this->appointmentService->getMyAppointments($user->id, $filters);

            return $this->respondWithJson($result['data'], $result['message'], $result['status']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi lấy danh sách lịch hẹn: ',
                'status' => 500
            ], 500);
        }
    }
}
