<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ServiceTranslation",
 *     title="Service Translation",
 *     description="Model for service translations",
 *     @OA\Property(property="id", type="integer", format="int64", description="Unique identifier"),
 *     @OA\Property(property="service_id", type="integer", format="int64", description="ID of the related service"),
 *     @OA\Property(property="language", type="string", description="Language code of the translation"),
 *     @OA\Property(property="name", type="string", description="Translated name of the service"),
 *     @OA\Property(property="description", type="string", description="Translated description of the service"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class ServiceTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'language', 'name', 'description'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}