<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ServiceCombo",
 *     title="Service Combo",
 *     description="Service Combo model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Unique identifier"),
 *     @OA\Property(property="name", type="string", description="Name of the service combo"),
 *     @OA\Property(property="price", type="number", format="float", description="Price of the service combo"),
 *     @OA\Property(property="description", type="string", description="Description of the service combo"),
 *     @OA\Property(property="image_id", type="integer", format="int64", description="ID of the associated image"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion timestamp")
 * )
 */
class ServiceCombo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'price', 'description', 'image_id'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_combo_items')
            ->withPivot('quantity');
    }

    public function priceHistory()
    {
        return $this->hasMany(ServiceComboPriceHistory::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
