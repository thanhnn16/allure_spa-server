<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="AttributeTranslation",
 *     title="AttributeTranslation",
 *     description="AttributeTranslation model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Unique identifier"),
 *     @OA\Property(property="attribute_id", type="integer", format="int64", description="ID of the related attribute"),
 *     @OA\Property(property="language", type="string", description="Language code"),
 *     @OA\Property(property="name", type="string", description="Translated name of the attribute"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class AttributeTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['attribute_id', 'language', 'name'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}