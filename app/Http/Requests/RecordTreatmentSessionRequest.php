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
            'start_time' => 'required|date_format:Y-m-d\TH:i',
            'end_time' => [
                'required',
                'date_format:Y-m-d\TH:i',
                function ($attribute, $value, $fail) {
                    $start = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $this->start_time);
                    $end = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $value);
                    
                    if ($end->lessThanOrEqualTo($start)) {
                        $fail('Thời gian kết thúc phải sau thời gian bắt đầu');
                    }
                }
            ],
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

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_service_package_id.required' => 'Vui lòng chọn gói dịch vụ',
            'user_service_package_id.exists' => 'Gói dịch vụ không tồn tại',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu',
            'start_time.date_format' => 'Định dạng thời gian bắt đầu không hợp lệ',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc',
            'end_time.date_format' => 'Định dạng thời gian kết thúc không hợp lệ',
            'staff_user_id.required' => 'Vui lòng chọn nhân viên thực hiện',
            'staff_user_id.exists' => 'Nhân viên không tồn tại',
            'result.max' => 'Kết quả không được vượt quá 1000 ký tự',
            'notes.max' => 'Ghi chú không được vượt quá 1000 ký tự',
        ];
    }
}
