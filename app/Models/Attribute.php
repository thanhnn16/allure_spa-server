<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Attribute",
 *     title="Attribute",
 *     description="Attribute model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Attribute ID"),
 *     @OA\Property(property="name", type="string", description="Attribute name"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 * )
 */
class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attributes')
            ->withPivot('attribute_value');
    }

    public function translations()
    {
        return $this->hasMany(AttributeTranslation::class);
    }
}