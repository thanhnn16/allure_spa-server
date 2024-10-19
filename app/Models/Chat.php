<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Chat",
 *     title="Chat",
 *     description="Chat model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Chat ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="User ID"),
 *     @OA\Property(property="staff_user_id", type="integer", format="int64", description="Staff User ID"),
 *     @OA\Property(property="status", type="string", description="Chat status"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 * )
 */
class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'staff_user_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}