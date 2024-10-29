<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ChatMessage",
 *     title="Chat Message", 
 *     description="Chat message model"
 * )
 */
class ChatMessage extends Model
{
    use HasFactory;

    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     format="int64",
     *     description="The unique identifier of the chat message"
     * )
     * @OA\Property(
     *     property="chat_id", 
     *     type="integer",
     *     format="int64",
     *     description="The ID of the chat this message belongs to"
     * )
     * @OA\Property(
     *     property="sender_id",
     *     type="string",
     *     format="uuid",
     *     description="The UUID of the user who sent this message"
     * )
     * @OA\Property(
     *     property="message",
     *     type="string",
     *     description="The content of the chat message"
     * )
     * @OA\Property(
     *     property="is_read",
     *     type="boolean",
     *     description="Whether the message has been read or not"
     * )
     * @OA\Property(
     *     property="created_at",
     *     type="string", 
     *     format="date-time",
     *     description="The timestamp when the message was created"
     * )
     * @OA\Property(
     *     property="updated_at",
     *     type="string",
     *     format="date-time", 
     *     description="The timestamp when the message was last updated"
     * )
     */
    protected $fillable = [
        'chat_id', 'sender_id', 'message', 'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender() 
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}