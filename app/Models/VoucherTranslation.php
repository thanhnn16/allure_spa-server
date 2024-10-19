<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="VoucherTranslation",
 *     title="VoucherTranslation",
 *     description="Model representing a voucher translation",
 *     @OA\Property(property="id", type="integer", description="The unique identifier of the voucher translation"),
 *     @OA\Property(property="voucher_id", type="integer", description="The ID of the associated voucher"),
 *     @OA\Property(property="language", type="string", description="The language code of the translation"),
 *     @OA\Property(property="description", type="string", description="The translated description of the voucher"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of creation"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of last update")
 * )
 */
class VoucherTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['voucher_id', 'language', 'description'];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}