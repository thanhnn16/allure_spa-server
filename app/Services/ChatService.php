<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Events\NewMessage;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    public function getAllChatsForUser($userId)
    {
        return Chat::where('user_id', $userId)
            ->orWhere('staff_user_id', $userId)
            ->with(['user', 'staff', 'messages' => function ($query) {
                $query->latest()->first();
            }])
            ->latest()
            ->get();
    }

    public function getChatMessages($chatId)
    {
        return ChatMessage::where('chat_id', $chatId)
            ->with('sender')
            ->orderBy('created_at')
            ->get();
    }

    public function sendMessage($chatId, $senderId, $message, $attachments = [])
    {
        $chatMessage = ChatMessage::create([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'message' => $message,
            'is_read' => false
        ]);

        // Handle attachments if any
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $path = $attachment->store('chat-attachments', 'public');
                // Store attachment info in database
            }
        }

        // Broadcast new message event
        broadcast(new NewMessage($chatMessage))->toOthers();

        return $chatMessage;
    }

    public function markMessagesAsRead($chatId, $userId)
    {
        return ChatMessage::where('chat_id', $chatId)
            ->where('sender_id', '!=', $userId)
            ->update(['is_read' => true]);
    }
}
