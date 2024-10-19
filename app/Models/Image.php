<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Image",
 *     title="Image",
 *     description="Image model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Image ID"),
 *     @OA\Property(property="url", type="string", description="Image URL"),
 *     @OA\Property(property="alt_text", type="string", description="Alternative text for the image"),
 *     @OA\Property(property="type", type="string", description="Type of the image"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class Image extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'alt_text', 'type'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function treatmentCategories()
    {
        return $this->hasMany(TreatmentCategory::class);
    }
}