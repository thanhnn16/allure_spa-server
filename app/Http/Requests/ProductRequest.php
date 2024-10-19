<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ProductRequest",
 *     required={"name", "price", "category_id", "quantity"},
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="price", type="number", format="float", minimum=0),
 *     @OA\Property(property="category_id", type="integer"),
 *     @OA\Property(property="image_id", type="integer", nullable=true),
 *     @OA\Property(property="quantity", type="integer", minimum=0)
 * )
 */
class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
            'image_id' => 'nullable|exists:images,id',
            'quantity' => 'required|integer|min:0',
        ];
    }
}
