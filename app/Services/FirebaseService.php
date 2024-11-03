<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\ChatMessage;
use App\Events\NewMessage;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = storage_path('app/allure-spa-app-firebase-adminsdk-r64oy-b723cdb13c.json');

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
        }
    }

    public function sendMessage($token, $title, $body, $data = [])
    {
        try {
            if (!$this->messaging) {
                throw new \Exception('Firebase messaging not initialized');
            }

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification([
                    'title' => $title,
                    'body' => $body
                ])
                ->withData($data);

            return $this->messaging->send($message);
        } catch (\Exception $e) {
            Log::error('Failed to send Firebase message: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleIncomingMessage($payload)
    {
        try {
            // Extract message data from payload
            $data = $payload['data'] ?? [];
            
            // Validate required fields
            if (empty($data['chat_id']) || empty($data['sender_id']) || empty($data['message'])) {
                throw new \Exception('Invalid message payload');
            }

            // Create new chat message
            $chatMessage = ChatMessage::create([
                'chat_id' => $data['chat_id'],
                'sender_id' => $data['sender_id'],
                'message' => $data['message'],
                'is_read' => false
            ]);

            // Broadcast the new message to web clients
            broadcast(new NewMessage($chatMessage))->toOthers();

            return $chatMessage;
        } catch (\Exception $e) {
            Log::error('Failed to handle incoming Firebase message: ' . $e->getMessage());
            throw $e;
        }
    }
}
