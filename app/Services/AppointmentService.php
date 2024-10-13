<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentService
{
    public function getAppointments($request)
    {
        return Appointment::with(['user', 'treatment', 'staff'])->get();
    }

    public function createAppointment($data)
    {
        Log::info('Creating appointment with data:', $data);

        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'staff_id' => 'required|exists:users,id',
            'treatment_id' => 'required|exists:treatments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'appointment_type' => 'required|string',
            'status' => 'required|in:pending,confirmed,cancelled,completed,Pending,Confirmed,Cancelled,Completed',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Appointment validation failed', ['errors' => $validator->errors()]);
            return ['status' => 422, 'message' => 'Validation failed', 'data' => $validator->errors()];
        }

        try {
            $appointment = Appointment::create([
                'user_id' => $data['user_id'],
                'staff_user_id' => $data['staff_id'],
                'treatment_id' => $data['treatment_id'],
                'start_time' => Carbon::parse($data['start_date'])->setTimezone(config('app.timezone')),
                'end_time' => Carbon::parse($data['end_date'])->setTimezone(config('app.timezone')),
                'appointment_type' => $data['appointment_type'],
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
            ]);

            Log::info('Appointment created successfully', ['appointment' => $appointment]);
            return ['status' => 200, 'message' => 'Appointment created successfully', 'data' => $appointment];
        } catch (\Exception $e) {
            Log::error('Error creating appointment', ['error' => $e->getMessage()]);
            return ['status' => 500, 'message' => 'An error occurred while creating the appointment', 'data' => null];
        }
    }

    public function updateAppointment($id, $data)
    {
        $validator = Validator::make($data, [
            'staff_id' => 'sometimes|exists:users,id',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'note' => 'nullable|string',
            'appointment_type' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return ['status' => 422, 'message' => 'Validation failed', 'data' => $validator->errors()];
        }

        try {
            $appointment = Appointment::findOrFail($id);
            
            $updateData = [
                'staff_user_id' => $data['staff_id'] ?? $appointment->staff_user_id,
                'start_time' => isset($data['start_time']) ? Carbon::parse($data['start_time'])->setTimezone(config('app.timezone')) : $appointment->start_time,
                'end_time' => isset($data['end_time']) ? Carbon::parse($data['end_time'])->setTimezone(config('app.timezone')) : $appointment->end_time,
                'status' => $data['status'] ?? $appointment->status,
                'note' => $data['note'] ?? $appointment->note,
                'appointment_type' => $data['appointment_type'] ?? $appointment->appointment_type,
            ];

            $appointment->update($updateData);

            return ['status' => 200, 'message' => 'Appointment updated successfully', 'data' => $appointment];
        } catch (\Exception $e) {
            return ['status' => 500, 'message' => 'An error occurred while updating the appointment', 'data' => null];
        }
    }

    // Implement other methods as needed...
}
