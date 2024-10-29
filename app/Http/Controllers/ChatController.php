<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class ChatController extends BaseController
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(): Response
    {
        $chats = $this->chatService->getAllChatsForUser(Auth::user()->id);
        
        return $this->respondWithInertia('Chats/ChatView', [
            'chats' => $chats
        ]);
    }

    public function getMessages(string $chatId)
    {
        $messages = $this->chatService->getChatMessages($chatId);
        return $this->respondWithJson($messages);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240'
        ]);

        $message = $this->chatService->sendMessage(
            $validated['chat_id'],
            Auth::user()->id,
            $validated['message'],
            $request->file('attachments') ?? []
        );

        return $this->respondWithJson($message, 'Tin nhắn đã được gửi');
    }

    public function markAsRead(string $chatId)
    {
        $this->chatService->markMessagesAsRead($chatId, Auth::user()->id);
        return $this->respondWithJson(null, 'Đã đánh dấu là đã đọc');
    }
}
