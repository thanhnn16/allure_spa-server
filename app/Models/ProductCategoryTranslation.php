<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ProductCategoryTranslation",
 *     title="Product Category Translation",
 *     description="Model representing a translation of a product category"
 * )
 */
class ProductCategoryTranslation extends Model
{
    use HasFactory;

    /**
     * @OA\Property(
     *     property="product_category_id",
     *     type="integer",
     *     description="ID of the related product category"
     * )
     * @OA\Property(
     *     property="language",
     *     type="string",
     *     description="Language code of the translation"
     * )
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="Translated name of the product category"
     * )
     */
    protected $fillable = ['product_category_id', 'language', 'name'];

    /**
     * @OA\Property(
     *     property="productCategory",
     *     ref="#/components/schemas/ProductCategory"
     * )
     */
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}