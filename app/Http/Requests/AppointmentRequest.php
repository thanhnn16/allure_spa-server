<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Appointment;

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
            'staff_id' => 'nullable|exists:staffs,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|exists:time_slots,id',
            'appointment_type' => 'required|in:service,service_package,consultation,others',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'note' => 'nullable|string',
            'slots' => 'required|integer|min:1|max:2',
            'service_id' => 'nullable|required_without:user_service_package_id|exists:services,id',
            'user_service_package_id' => 'nullable|required_without:service_id|exists:user_service_packages,id',
        ];
    }

    public function messages()
    {
        return [
            'slots.required' => 'Vui lòng nhập số lượng slot',
            'slots.integer' => 'Số lượng slot phải là số nguyên',
            'slots.min' => 'Số lượng slot tối thiểu là 1',
            'slots.max' => 'Số lượng slot tối đa là 2',
            'service_id.required_without' => 'Vui lòng chọn dịch vụ hoặc gói điều trị',
            'user_service_package_id.required_without' => 'Vui lòng chọn dịch vụ hoặc gói điều trị',
        ];
    }
}
