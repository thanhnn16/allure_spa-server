<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ServiceCategoryTranslation",
 *     title="Service Category Translation",
 *     description="Model representing a translation of a service category",
 *     @OA\Property(property="id", type="integer", description="The ID of the translation"),
 *     @OA\Property(property="service_category_id", type="integer", description="The ID of the related service category"),
 *     @OA\Property(property="language", type="string", description="The language code of the translation"),
 *     @OA\Property(property="name", type="string", description="The translated name of the service category"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of creation"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of last update")
 * )
 */
class ServiceCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['service_category_id', 'language', 'name'];

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }
}