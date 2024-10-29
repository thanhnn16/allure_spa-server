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
 *     @OA\Property(property="user_id", type="string", format="uuid", description="User ID"),
 *     @OA\Property(property="staff_id", type="string", format="uuid", description="Staff ID"),
 *     @OA\Property(property="is_active", type="boolean", description="Chat active status"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 * )
 */
class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'staff_id', 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasParticipant(User $user): bool
    {
        return $this->user_id === $user->id || $this->staff_id === $user->id;
    }

    /**
     * Check if user can participate in chat based on their role
     */
    public function canParticipate(User $user): bool
    {
        // User is direct participant
        if ($this->hasParticipant($user)) {
            return true;
        }

        // Allow admin access to all chats
        if ($user->role === 'admin') {
            return true;
        }

        // Allow staff access if they are assigned or if chat has no staff yet
        if ($user->role === 'staff') {
            return $this->staff_id === null || $this->staff_id === $user->id;
        }

        return false;
    }

    /**
     * Assign staff to chat
     */
    public function assignStaff(User $staff): void
    {
        if (!in_array($staff->role, ['staff', 'admin'])) {
            throw new \InvalidArgumentException('Only staff and admin can be assigned to chat');
        }

        $this->staff_id = $staff->id;
        $this->save();
    }

    /**
     * Get the other participant in the chat relative to given user
     */
    public function getOtherParticipant(User $user): ?User
    {
        if ($this->user_id === $user->id) {
            return $this->staff;
        }
        return $this->user;
    }
}
