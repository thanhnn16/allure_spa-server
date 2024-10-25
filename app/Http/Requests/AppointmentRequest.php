<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AppointmentRequest",
 *     type="object",
 *     title="Appointment Request",
 *     required={"user_id", "staff_id", "service_id", "start_date", "end_date", "appointment_type", "status"},
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="staff_id", type="integer", example=2),
 *     @OA\Property(property="service_id", type="integer", example=3),
 *     @OA\Property(property="start_date", type="string", format="date-time", example="2023-05-01T09:00:00+07:00"),
 *     @OA\Property(property="end_date", type="string", format="date-time", example="2023-05-01T10:00:00+07:00"),
 *     @OA\Property(property="appointment_type", type="string", example="consultation"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="note", type="string", example="Ghi chú cho cuộc hẹn")
 * )
 */

/**
 * @OA\Schema(
 *     schema="AppointmentUpdateRequest",
 *     type="object",
 *     title="Appointment Update Request",
 *     @OA\Property(property="staff_id", type="integer", example=2),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-01T09:30:00+07:00"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-01T10:30:00+07:00"),
 *     @OA\Property(property="status", type="string", example="confirmed"),
 *     @OA\Property(property="note", type="string", example="Cập nhật ghi chú cho cuộc hẹn"),
 *     @OA\Property(property="appointment_type", type="string", example="service")
 * )
 */

/**
 * @OA\Schema(
 *     schema="MobileAppointmentUpdateRequest",
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-01T09:30:00+07:00"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-01T10:30:00+07:00"),
 *     @OA\Property(property="note", type="string", example="Cập nhật ghi chú cho cuộc hẹn từ ứng dụng di động")
 * )
 */

class AppointmentRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép thực hiện request này không.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'staff_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'appointment_type' => 'required|string',
            'status' => 'required|in:pending,confirmed,cancelled,completed,Pending,Confirmed,Cancelled,Completed',
            'note' => 'nullable|string',
        ];
    }

    /**
     * Lấy các thông báo lỗi tùy chỉnh cho các quy tắc xác thực.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'user_id.required' => 'ID người dùng là bắt buộc.',
            'user_id.exists' => 'ID người dùng không tồn tại.',
            'staff_id.required' => 'ID nhân viên là bắt buộc.',
            'staff_id.exists' => 'ID nhân viên không tồn tại.',
            'service_id.required' => 'ID dịch vụ là bắt buộc.',
            'service_id.exists' => 'ID dịch vụ không tồn tại.',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'start_date.date' => 'Ngày bắt đầu phải là một ngày hợp lệ.',
            'end_date.required' => 'Ngày kết thúc là bắt buộc.',
            'end_date.date' => 'Ngày kết thúc phải là một ngày hợp lệ.',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'appointment_type.required' => 'Loại cuộc hẹn là bắt buộc.',
            'status.required' => 'Trạng thái cuộc hẹn là bắt buộc.',
            'status.in' => 'Trạng thái cuộc hẹn không hợp lệ.',
        ];
    }
}
