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
}
