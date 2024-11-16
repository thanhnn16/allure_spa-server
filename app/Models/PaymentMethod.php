<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="PaymentMethod",
 *     title="Payment Method",
 *     description="Payment Method model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Payment method ID"),
 *     @OA\Property(property="method_name", type="string", description="Name of the payment method"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Deletion timestamp")
 * )
 */
class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['method_name'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
