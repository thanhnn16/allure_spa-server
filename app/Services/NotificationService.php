<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
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

    // Create notification and send FCM
    public function createNotification($data)
    {
        // Create notification record
        $notification = Notification::create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'content' => $data['content'], 
            'type' => $data['type'],
            'is_read' => false
        ]);

        // Get user's FCM tokens
        $tokens = $this->fcmTokenService->getUserTokens($data['user_id']);

        // Send FCM notification
        foreach ($tokens as $token) {
            $this->firebaseService->sendMessage(
                $token,
                $data['title'],
                $data['content'],
                [
                    'notification_id' => $notification->id,
                    'type' => $data['type']
                ]
            );
        }

        return $notification;
    }

    // Send notification to all admins
    public function notifyAdmins($title, $content, $type, $data = [])
    {
        $this->firebaseService->sendNotificationToAdmin(
            $title,
            $content,
            array_merge(['type' => $type], $data)
        );
    }
}
