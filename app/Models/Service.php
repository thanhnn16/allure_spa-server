<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Service",
 *     title="Service",
 *     description="Service model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Service ID"),
 *     @OA\Property(property="name", type="string", description="Service name"),
 *     @OA\Property(property="price", type="number", format="float", description="Service price"),
 *     @OA\Property(property="description", type="string", description="Service description"),
 *     @OA\Property(property="duration", type="integer", description="Service duration in minutes"),
 *     @OA\Property(property="image_id", type="integer", format="int64", description="Image ID"),
 *     @OA\Property(property="category_id", type="integer", format="int64", description="Category ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion date")
 * )
 */
class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'description',
        'duration',
        'image_id',
        'category_id'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(ServicePriceHistory::class);
    }

    public function combos()
    {
        return $this->hasMany(ServiceCombo::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function translations()
    {
        return $this->hasMany(ServiceTranslation::class);
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function userServicePackages()
    {
        return $this->hasMany(UserServicePackage::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
