<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:' . now()->format('Y-m-d'),
            'time_slot_id' => 'required|exists:time_slots,id',
            'appointment_type' => 'required|string|in:service,package,consultation,others',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'note' => 'nullable|string',
            'slots' => 'required|integer|min:1|max:2',
        ];
    }

    public function messages()
    {
        return [
            'service_id.required' => 'Vui lòng chọn dịch vụ',
            'service_id.exists' => 'Dịch vụ không tồn tại',
            'appointment_date.required' => 'Vui lòng chọn ngày hẹn',
            'appointment_date.date' => 'Ngày hẹn không hợp lệ',
            'appointment_date.after_or_equal' => 'Ngày hẹn phải từ hôm nay trở đi',
            'time_slot_id.required' => 'Vui lòng chọn khung giờ',
            'time_slot_id.exists' => 'Khung giờ không tồn tại',
            'appointment_type.required' => 'Vui lòng chọn loại cuộc hẹn',
            'appointment_type.in' => 'Loại cuộc hẹn không hợp lệ',
            'status.required' => 'Vui lòng chọn trạng thái',
            'status.in' => 'Trạng thái không hợp lệ',
            'slots.required' => 'Vui lòng nhập số lượng slot',
            'slots.integer' => 'Số lượng slot phải là số nguyên',
            'slots.min' => 'Số lượng slot tối thiểu là 1',
            'slots.max' => 'Số lượng slot tối đa là 2',
        ];
    }
}
