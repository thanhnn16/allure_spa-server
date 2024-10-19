<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *     title="Yêu cầu cập nhật thông tin người dùng",
 *     description="Dữ liệu yêu cầu để cập nhật thông tin người dùng",
 *     @OA\Property(property="full_name", type="string", maxLength=255, description="Họ và tên đầy đủ của người dùng"),
 *     @OA\Property(property="phone_number", type="string", maxLength=255, description="Số điện thoại của người dùng"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, description="Địa chỉ email của người dùng"),
 *     @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, description="Giới tính của người dùng"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", description="Ngày sinh của người dùng"),
 *     @OA\Property(property="skin_condition", type="string", maxLength=255, description="Tình trạng da của người dùng")
 * )
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'full_name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:255|unique:users,phone_number,' . $this->route('id'),
            'email' => 'sometimes|email|max:255|unique:users,email,' . $this->route('id'),
            'gender' => 'sometimes|in:male,female,other',
            'date_of_birth' => 'sometimes|date',
            'skin_condition' => 'sometimes|string|max:255',
        ];
    }
}
