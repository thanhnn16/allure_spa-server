<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Events\NewMessage;

class ChatService
{
    protected $firebaseService;
    protected $fcmTokenService;

    public function __construct(
        FirebaseService $firebaseService,
        FcmTokenService $fcmTokenService
    ) {
        $this->firebaseService = $firebaseService;
        $this->fcmTokenService = $fcmTokenService;
    }

    public function getAllChatsForUser($userId)
    {
        return Chat::where('user_id', $userId)
            ->orWhere('staff_id', $userId)
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

        // Get chat
        $chat = Chat::find($chatId);

        // Get recipient's FCM tokens
        $recipientId = $chat->user_id === $senderId ? $chat->staff_id : $chat->user_id;
        $tokens = $this->fcmTokenService->getUserTokens($recipientId);

        // Send FCM notification
        foreach ($tokens as $token) {
            $this->firebaseService->sendMessage(
                $token,
                'New Message',
                $message,
                [
                    'type' => 'chat_message',
                    'chat_id' => $chatId,
                    'sender_id' => $senderId,
                    'message' => $message
                ]
            );
        }

        // Broadcast for web clients
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
