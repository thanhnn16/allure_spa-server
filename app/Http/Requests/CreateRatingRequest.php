<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateRatingRequest",
 *     title="Yêu cầu tạo đánh giá",
 *     description="Yêu cầu tạo đánh giá mới",
 *     @OA\Property(property="user_id", type="string", format="uuid", description="ID của người dùng"),
 *     @OA\Property(property="rating_type", type="string", enum={"service", "product"}, description="Loại đánh giá"),
 *     @OA\Property(property="item_id", type="integer", description="ID của mục được đánh giá"),
 *     @OA\Property(property="stars", type="integer", minimum=1, maximum=5, description="Số sao đánh giá"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Bình luận đánh giá"),
 *     @OA\Property(property="image_id", type="integer", nullable=true, description="ID của hình ảnh đính kèm"),
 *     @OA\Property(property="video_id", type="integer", nullable=true, description="ID của video đính kèm"),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, description="Trạng thái đánh giá")
 * )
 */
class CreateRatingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'rating_type' => 'required|in:product,service',
            'item_id' => 'required|integer',
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ];
    }

    public function messages()
    {
        return [
            'images.max' => 'Tối đa chỉ được gửi 5 ảnh',
            'images.*.image' => 'File phải là ảnh',
            'images.*.mimes' => 'Ảnh phải có định dạng jpeg, png hoặc jpg',
            'images.*.max' => 'Mỗi ảnh không được vượt quá 5MB'
        ];
    }
}
