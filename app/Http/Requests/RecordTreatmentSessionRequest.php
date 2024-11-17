<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\UserServicePackage;
use App\Models\User;

class RecordTreatmentSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_service_package_id' => [
                'required',
                'exists:user_service_packages,id',
                function ($attribute, $value, $fail) {
                    $package = UserServicePackage::find($value);
                    if ($package->status === 'expired') {
                        $fail('Gói dịch vụ đã hết hạn');
                    }
                    if ($package->remaining_sessions <= 0) {
                        $fail('Gói dịch vụ đã hết số buổi sử dụng');
                    }
                },
            ],
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'staff_user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $staff = User::find($value);
                    if ($staff->role !== 'staff') {
                        $fail('Người thực hiện phải là nhân viên');
                    }
                },
            ],
            'result' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000'
        ];
    }
}
