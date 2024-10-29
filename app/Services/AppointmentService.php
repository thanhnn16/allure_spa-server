<?php

namespace App\Services;

use App\Models\Appointment;
use App\Events\AppointmentCreated;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppointmentService
{
    public function getAppointments($request)
    {
        return Appointment::with(['user', 'service', 'staff'])->get();
    }

    public function createAppointment($data)
    {
        return DB::transaction(function () use ($data) {
            // Kiểm tra slot còn trống
            $existingBookings = Appointment::where('appointment_date', $data['appointment_date'])
                ->where('time_slot_id', $data['time_slot_id'])
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($existingBookings >= 2) {
                return [
                    'status' => 422,
                    'message' => 'Khung giờ này đã đầy',
                    'data' => null
                ];
            }

            $appointment = Appointment::create([
                'user_id' => $data['user_id'],
                'service_id' => $data['service_id'],
                'staff_user_id' => $data['staff_id'],
                'appointment_date' => $data['appointment_date'],
                'time_slot_id' => $data['time_slot_id'],
                'appointment_type' => $data['appointment_type'],
                'status' => 'pending',
                'note' => $data['note'] ?? null,
            ]);

            // Broadcast event
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
            $appointment = Appointment::findOrFail($id);

            // Kiểm tra xem người dùng có quyền hủy cuộc hẹn không
            if ($appointment->user_id !== Auth::id()) {
                return [
                    'status' => 403,
                    'message' => 'Bạn không có quyền hủy cuộc hẹn này',
                    'data' => null,
                    'success' => false
                ];
            }

            // Kiểm tra xem cuộc hẹn có thể hủy không (ví dụ: chưa bắt đầu)
            if ($appointment->start_time <= now()) {
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

    // Implement other methods as needed...
}
