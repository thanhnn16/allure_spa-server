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
            'service_id' => 'nullable|exists:services,id',
            'user_treatment_package_id' => 'nullable|exists:user_treatment_packages,id',
            'appointment_date' => 'required|date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'appointment_type' => 'required|string',
            'status' => 'nullable|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
            'slots' => 'required|integer|min:1|max:2',
        ];
    }

    public function messages()
    {
        return [
            'user_id.exists' => 'Khách hàng không tồn tại',
            'service_id.exists' => 'Dịch vụ không tồn tại',
            'user_treatment_package_id.exists' => 'Gói điều trị không tồn tại',
            'appointment_date.date' => 'Ngày hẹn không hợp lệ',
            'time_slot_id.required' => 'Vui lòng chọn khung giờ',
            'time_slot_id.exists' => 'Khung giờ không tồn tại',
            'appointment_type.required' => 'Vui lòng chọn loại cuộc hẹn',
            'status.required' => 'Vui lòng chọn trạng thái',
            'status.in' => 'Trạng thái không hợp lệ',
            'slots.required' => 'Vui lòng nhập số lượng slot',
            'slots.integer' => 'Số lượng slot phải là số nguyên',
            'slots.min' => 'Số lượng slot tối thiểu là 1',
            'slots.max' => 'Số lượng slot tối đa là 2',
        ];
    }
}
