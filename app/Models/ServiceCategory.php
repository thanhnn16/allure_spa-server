<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ServiceCategory",
 *     title="Service Category",
 *     description="Model representing a service category",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the service category"),
 *     @OA\Property(property="name", type="string", description="The name of the service category"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, description="The ID of the parent category, if any"),
 *     @OA\Property(property="image_id", type="integer", nullable=true, description="The ID of the associated image, if any"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="The deletion timestamp, if soft deleted")
 * )
 */
class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'parent_id', 'image_id'];

    public function parent()
    {
        return $this->belongsTo(ServiceCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ServiceCategory::class, 'parent_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function translations()
    {
        return $this->hasMany(ServiceCategoryTranslation::class);
    }
}