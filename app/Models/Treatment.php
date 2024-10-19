<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Treatment",
 *     title="Treatment",
 *     description="Treatment model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Treatment ID"),
 *     @OA\Property(property="name", type="string", description="Treatment name"),
 *     @OA\Property(property="price", type="number", format="float", description="Treatment price"),
 *     @OA\Property(property="description", type="string", description="Treatment description"),
 *     @OA\Property(property="duration", type="integer", description="Treatment duration in minutes"),
 *     @OA\Property(property="image_id", type="integer", format="int64", description="Image ID"),
 *     @OA\Property(property="category_id", type="integer", format="int64", description="Category ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion date")
 * )
 */
class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'description',
        'duration',
        'image_id'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(TreatmentPriceHistory::class);
    }

    public function combos()
    {
        return $this->hasMany(TreatmentCombo::class);
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
        return $this->hasMany(TreatmentTranslation::class);
    }

    public function category()
    {
        return $this->belongsTo(TreatmentCategory::class, 'category_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function userTreatmentPackages()
    {
        return $this->hasMany(UserTreatmentPackage::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
