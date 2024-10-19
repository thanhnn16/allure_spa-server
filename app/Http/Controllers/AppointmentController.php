<?php

namespace App\Http\Controllers;

use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AppointmentRequest;
use OpenApi\Annotations as OA;

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
     *     summary="Lấy danh sách cuộc hẹn",
     *     tags={"Appointments"},
     *     @OA\Response(
     *         response=200,
     *         description="Trả về danh sách cuộc hẹn",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Appointment"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $appointments = $this->appointmentService->getAppointments($request);

        Log::info('Appointments:', $appointments->toArray());

        // Chuyển đổi múi giờ cho mỗi cuộc hẹn
        $appointments = $appointments->map(function ($appointment) {
            $appointment->start_time = Carbon::parse($appointment->start_time)->setTimezone('Asia/Ho_Chi_Minh');
            $appointment->end_time = Carbon::parse($appointment->end_time)->setTimezone('Asia/Ho_Chi_Minh');
            return $appointment;
        });

        if ($request->expectsJson()) {
            return $this->respondWithJson($appointments, 'Lấy danh sách cuộc hẹn thành công');
        }

        return $this->respondWithInertia('Calendar/CalendarView', [
            'appointments' => $appointments
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/appointments",
     *     summary="Tạo cuộc hẹn mới",
     *     tags={"Appointments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "staff_id", "treatment_id", "start_date", "end_date", "appointment_type", "status"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="staff_id", type="integer", example=2),
     *             @OA\Property(property="treatment_id", type="integer", example=3),
     *             @OA\Property(property="start_date", type="string", format="date-time", example="2023-05-01T09:00:00+07:00"),
     *             @OA\Property(property="end_date", type="string", format="date-time", example="2023-05-01T10:00:00+07:00"),
     *             @OA\Property(property="appointment_type", type="string", example="consultation"),
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="note", type="string", example="Ghi chú cho cuộc hẹn")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cuộc hẹn được tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi xác thực dữ liệu"
     *     )
     * )
     */
    public function store(AppointmentRequest $request)
    {
        $result = $this->appointmentService->createAppointment($request->validated());

        if ($request->expectsJson()) {
            return $this->respondWithJson($result['data'], $result['message'], $result['status']);
        }

        return redirect()->route('appointments.index')->with('success', $result['message']);
    }

    /**
     * @OA\Put(
     *     path="/api/appointments/{id}/update",
     *     summary="Cập nhật cuộc hẹn (cho cả web và mobile)",
     *     tags={"Appointments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="start_time", type="string", format="date-time"),
     *             @OA\Property(property="end_time", type="string", format="date-time"),
     *             @OA\Property(property="note", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trả về cuộc hẹn đã được cập nhật",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi xác th���c dữ liệu"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền cập nhật cuộc hẹn"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $result = $this->appointmentService->updateAppointment($id, $request->all());
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
    public function show($id)
    {
        $result = $this->appointmentService->getAppointmentDetails($id);
        return response()->json($result);
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
     *         description="Không tìm thấy cuộc hẹn"
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
     *     summary="Hủy cuộc hẹn (cho cả web và mobile)",
     *     tags={"Appointments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="note", type="string", example="Lý do hủy: Bận việc đột xuất")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trả về thông báo hủy thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền hủy cuộc hẹn"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy cuộc hẹn"
     *     )
     * )
     */
    public function cancel(Request $request, $id)
    {
        $result = $this->appointmentService->cancelAppointment($id, $request->input('note'));
        return $this->respondWithJson($result['data'], $result['message'], $result['status']);
    }
}
