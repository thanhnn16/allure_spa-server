<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateRatingRequest",
 *     title="Yêu cầu tạo đánh giá",
 *     description="Yêu cầu tạo đánh giá mới",
 *     @OA\Property(property="user_id", type="string", format="uuid", description="ID của người dùng"),
 *     @OA\Property(property="rating_type", type="string", enum={"treatment", "product"}, description="Loại đánh giá"),
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
            'user_id' => 'required|exists:users,id',
            'rating_type' => 'required|in:treatment,product',
            'item_id' => 'required|integer',
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'image_id' => 'nullable|exists:images,id',
            'video_id' => 'nullable|exists:videos,id',
            'status' => 'required|in:pending,approved,rejected',
        ];
    }
}
