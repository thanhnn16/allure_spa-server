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
            'user_id' => 'required|exists:users,id',
            'service_id' => 'nullable|exists:services,id',
            'user_treatment_package_id' => 'nullable|exists:user_treatment_packages,id',
            'appointment_date' => 'required|date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'appointment_type' => 'required|string',
            'status' => 'required|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
            'slots' => 'required|default:1|integer|min:1|max:2',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Vui lòng chọn khách hàng',
            'appointment_date.required' => 'Vui lòng chọn ngày hẹn',
            'time_slot_id.required' => 'Vui lòng chọn khung giờ',
            'appointment_type.required' => 'Vui lòng chọn loại lịch hẹn',
            'status.required' => 'Vui lòng chọn trạng thái',
            'slots.required' => 'Vui lòng nhập số lượng slot',
            'slots.integer' => 'Số lượng slot phải là số nguyên',
            'slots.min' => 'Số lượng slot tối thiểu là 1',
            'slots.max' => 'Số lượng slot tối đa là 2',
        ];
    }
}
