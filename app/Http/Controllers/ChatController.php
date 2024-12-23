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

    public function index(Request $request)
    {
        $chats = $this->chatService->getAllChatsForUser(Auth::user()->id);

        if ($request->wantsJson()) {
            return $this->respondWithJson($chats);
        }

        return $this->respondWithInertia('Chats/ChatView', [
            'chats' => $chats
        ]);
    }

    public function getMessages(string $chatId, Request $request)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 20);
        
        $messages = $this->chatService->getChatMessages($chatId, $page, $perPage);
        
        // Transform response to include pagination info
        return $this->respondWithJson([
            'messages' => $messages->items(),
            'has_more' => $messages->hasMorePages(),
            'next_page' => $messages->currentPage() + 1
        ]);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240'
        ]);

        try {
            $message = $this->chatService->sendMessage(
                $validated['chat_id'],
                Auth::user()->id,
                $validated['message'],
                $request->file('attachments') ?? []
            );

            return $this->respondWithJson($message->load('sender'), 'Tin nhắn đã được gửi');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    public function markAsRead(string $chatId)
    {
        $this->chatService->markMessagesAsRead($chatId, Auth::user()->id);
        return $this->respondWithJson(null, 'Đã đánh dấu là đã đọc');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $existingChat = Chat::where(function ($query) use ($validated) {
            $query->where('user_id', $validated['user_id'])
                ->where('staff_id', Auth::id());
        })->first();

        if ($existingChat) {
            return $this->respondWithJson($existingChat, 'Chat đã tồn tại');
        }

        $chat = Chat::create([
            'user_id' => $validated['user_id'],
            'staff_id' => Auth::id(),
            'is_active' => true
        ]);

        return $this->respondWithJson($chat->load(['user', 'staff']), 'Chat đã được tạo', 201);
    }
}
