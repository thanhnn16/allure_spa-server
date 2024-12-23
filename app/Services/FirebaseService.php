<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\ChatMessage;
use App\Events\NewMessage;
use App\Models\User;
use App\Models\FcmToken;

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

            // Notification payload
            $notification = [
                'title' => $title,
                'body' => $body,
            ];

            // Android config đúng cấu trúc
            $android = [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'appointment_channel',
                    'sound' => 'default',
                    'notification_priority' => 'PRIORITY_HIGH',
                    'default_vibrate_timings' => true,
                    'default_sound' => true
                ]
            ];

            // APNS (iOS) config
            $apns = [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                    ]
                ]
            ];

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data)
                ->withAndroidConfig($android)
                ->withApnsConfig($apns);

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

    public function sendNotificationToAdmin($title, $body, $data = [])
    {
        try {
            // Get all admin FCM tokens from fcm_tokens table through relationship
            $adminTokens = FcmToken::whereHas('user', function ($query) {
                $query->where('role', 'admin')
                    ->whereNull('deleted_at');
            })
                ->pluck('token')  // Lấy token từ bảng fcm_tokens
                ->toArray();

            if (empty($adminTokens)) {
                Log::info('No admin tokens found for notification');
                return;
            }

            // Send to multiple tokens
            $message = CloudMessage::new()
                ->withNotification([
                    'title' => $title,
                    'body' => $body
                ])
                ->withData($data);

            $this->messaging->sendMulticast($message, $adminTokens);
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification: ' . $e->getMessage());
            throw $e;
        }
    }
}
