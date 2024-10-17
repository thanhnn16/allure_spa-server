<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportUsersRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Vui lòng chọn file để import.',
            'file.file' => 'Dữ liệu upload phải là một file.',
            'file.mimes' => 'File phải có định dạng xlsx, xls hoặc csv.',
            'file.max' => 'Kích thước file không được vượt quá 10MB.',
        ];
    }
}
