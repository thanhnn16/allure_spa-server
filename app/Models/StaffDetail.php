<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StaffDetail",
 *     title="Staff Detail",
 *     description="Staff detail model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Staff detail ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="User ID"),
 *     @OA\Property(property="position", type="string", description="Staff position"),
 *     @OA\Property(property="hire_date", type="string", format="date", description="Hire date"),
 *     @OA\Property(property="salary", type="number", format="float", description="Staff salary"),
 *     @OA\Property(property="bank_account_number", type="string", description="Bank account number"),
 *     @OA\Property(property="bank_name", type="string", description="Bank name"),
 *     @OA\Property(property="emergency_contact_name", type="string", description="Emergency contact name"),
 *     @OA\Property(property="emergency_contact_phone", type="string", description="Emergency contact phone")
 * )
 */
class StaffDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'position', 'hire_date', 'salary', 'bank_account_number',
        'bank_name', 'emergency_contact_name', 'emergency_contact_phone'
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}