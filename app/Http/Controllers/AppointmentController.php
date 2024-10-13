<?php

namespace App\Http\Controllers;

use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentController extends BaseController
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(Request $request)
    {
        $appointments = $this->appointmentService->getAppointments($request);

        // Đảm bảo rằng $appointments không rỗng
        Log::info('Appointments:', $appointments->toArray());

        // Convert timezone for each appointment
        $appointments = $appointments->map(function ($appointment) {
            $appointment->start_time = Carbon::parse($appointment->start_time)->setTimezone('Asia/Ho_Chi_Minh');
            $appointment->end_time = Carbon::parse($appointment->end_time)->setTimezone('Asia/Ho_Chi_Minh');
            return $appointment;
        });

        if ($request->expectsJson()) {
            return $this->respondWithJson($appointments, 'Appointments retrieved successfully');
        }

        return $this->respondWithInertia('Calendar/CalendarView', [
            'appointments' => $appointments
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Received appointment creation request', $request->all());

        $result = $this->appointmentService->createAppointment($request->all());

        Log::info('Appointment creation result', $result);

        if ($result['status'] === 422) {
            return response()->json(['errors' => $result['data']], 422);
        }

        if ($request->expectsJson()) {
            return $this->respondWithJson($result['data'], $result['message'], $result['status']);
        }

        return redirect()->route('appointments.index')->with('success', $result['message']);
    }

    public function update(Request $request, $id)
    {
        $result = $this->appointmentService->updateAppointment($id, $request->all());

        if ($request->expectsJson()) {
            return $this->respondWithJson($result['data'], $result['message'], $result['status']);
        }

        return redirect()->route('appointments.index')->with('success', $result['message']);
    }

    // Implement other methods (show, destroy) similarly...
}
