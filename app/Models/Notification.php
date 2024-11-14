<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Notification",
 *     title="Notification", 
 *     description="Notification model",
 *     @OA\Property(property="id", type="integer", format="int64", description="Notification ID"),
 *     @OA\Property(property="user_id", type="string", format="uuid", description="User ID"),
 *     @OA\Property(property="media_id", type="integer", format="int64", description="Media ID"),
 *     @OA\Property(property="title", type="string", description="Notification title"),
 *     @OA\Property(property="content", type="string", description="Notification content"),
 *     @OA\Property(property="type", type="string", description="Notification type"),
 *     @OA\Property(property="is_read", type="boolean", description="Whether the notification has been read"),
 *     @OA\Property(property="url", type="string", description="URL associated with notification"),
 *     @OA\Property(property="status", type="string", description="Notification status"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_id',
        'title',
        'content',
        'type',
        'is_read',
        'url',
        'status'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
