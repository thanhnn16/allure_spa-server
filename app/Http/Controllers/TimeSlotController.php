<?php

namespace App\Http\Controllers;

use App\Services\AppointmentService;
use Illuminate\Http\Request;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Log;

class TimeSlotController extends BaseController
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $serviceId = $request->get('service_id');

            $timeSlots = TimeSlot::where('is_active', true)
                ->orderBy('start_time')
                ->get()
                ->map(function ($slot) use ($date) {
                    // Đếm số lượng cuộc hẹn hiện tại cho slot này
                    $currentBookings = $slot->appointments()
                        ->where('appointment_date', $date)
                        ->where('status', '!=', 'cancelled')
                        ->count();

                    return [
                        'id' => $slot->id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'max_bookings' => $slot->max_bookings,
                        'available' => $currentBookings < $slot->max_bookings,
                        'current_bookings' => $currentBookings
                    ];
                });

            return $this->respondWithJson($timeSlots, 'Time slots retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Error fetching time slots: ' . $e->getMessage());
            return $this->respondWithError('Error fetching time slots: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/time-slots/available",
     *     summary="Kiểm tra các khung giờ trống cho ngày được chọn",
     *     tags={"TimeSlots"},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Ngày cần kiểm tra (format: Y-m-d)",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách khung giờ trống thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="start_time", type="string", example="09:00:00"),
     *                     @OA\Property(property="end_time", type="string", example="10:00:00"),
     *                     @OA\Property(property="available", type="boolean", example=true),
     *                     @OA\Property(property="current_bookings", type="integer", example=1),
     *                     @OA\Property(property="max_bookings", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi validate",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ngày không hợp lệ"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        try {
            $date = $request->get('date');

            $timeSlots = TimeSlot::where('is_active', true)
                ->orderBy('start_time')
                ->get()
                ->map(function ($slot) use ($date) {
                    $currentBookings = $slot->appointments()
                        ->where('appointment_date', $date)
                        ->where('status', '!=', 'cancelled')
                        ->count();

                    return [
                        'id' => $slot->id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'available' => $currentBookings < min(2, $slot->max_bookings),
                        'current_bookings' => $currentBookings,
                        'max_bookings' => min(2, $slot->max_bookings)
                    ];
                })
                ->filter(function ($slot) {
                    return $slot['available'];
                })
                ->values();

            return $this->respondWithJson($timeSlots, 'Lấy danh sách khung giờ trống thành công');

        } catch (\Exception $e) {
            Log::error('Error fetching available time slots: ' . $e->getMessage());
            return $this->respondWithError('Error fetching available time slots: ' . $e->getMessage());
        }
    }
}
