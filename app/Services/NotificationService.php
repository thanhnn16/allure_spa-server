<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    protected $firebaseService;
    protected $fcmTokenService;

    // Add notification type constants
    const NOTIFICATION_TYPES = [
        'appointment' => [
            'new' => 'new_appointment',
            'status' => 'appointment_status',
            'cancelled' => 'appointment_cancelled',
            'reminder' => 'appointment_reminder',
        ],
        'order' => [
            'new' => 'new_order',
            'status' => 'order_status',
            'cancelled' => 'order_cancelled',
            'completed' => 'order_completed',
        ],
        'service' => [
            'expiring' => 'service_expiring',
            'low_sessions' => 'service_low_sessions',
            'completed' => 'service_completed',
        ],
        'payment' => [
            'success' => 'payment_success',
            'failed' => 'payment_failed',
            'pending' => 'payment_pending',
        ],
        'chat' => 'new_message',
        'review' => 'new_review',
        'promotion' => 'promotion',
        'system' => 'system'
    ];

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
        // Validate notification type
        if (!$this->isValidNotificationType($data['type'])) {
            throw new \Exception('Invalid notification type');
        }

        // Create notification record
        $notification = Notification::create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'content' => $data['content'], 
            'type' => $data['type'],
            'data' => $data['data'] ?? null,
            'is_read' => false
        ]);

        // Send FCM notification with standardized type
        $this->sendFCMNotification($notification);

        return $notification;
    }

    private function isValidNotificationType($type) 
    {
        $validTypes = array_merge(
            array_values(self::NOTIFICATION_TYPES['appointment']),
            array_values(self::NOTIFICATION_TYPES['order']),
            [
                self::NOTIFICATION_TYPES['chat'],
                self::NOTIFICATION_TYPES['review'],
                self::NOTIFICATION_TYPES['promotion'],
                self::NOTIFICATION_TYPES['payment'],
                self::NOTIFICATION_TYPES['system']
            ]
        );
        
        return in_array($type, $validTypes);
    }

    private function sendFCMNotification($notification)
    {
        // Get user's FCM tokens
        $tokens = $this->fcmTokenService->getUserTokens($notification->user_id);

        // Send to each token
        foreach ($tokens as $token) {
            $this->firebaseService->sendMessage(
                $token,
                $notification->title,
                $notification->content,
                [
                    'notification_id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data
                ]
            );
        }
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

    public function getUserNotifications($userId, $perPage, $page = 1)
    {
        $query = Notification::where('user_id', $userId)
            ->with('media')
            ->orderBy('created_at', 'desc');

        $unreadCount = $query->clone()->where('is_read', false)->count();

        $skip = ($page - 1) * $perPage;
        $notifications = $query->skip($skip)
            ->take($perPage)
            ->get()
            ->map(function ($notification) {
                return $this->formatNotification($notification);
            });

        $hasMore = $query->skip($skip + $perPage)->exists();

        return [
            'items' => $notifications,
            'hasMore' => $hasMore,
            'unreadCount' => $unreadCount
        ];
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);
        return $notification;
    }

    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->update(['is_read' => true]);
    }

    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    private function formatNotification($notification)
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'content' => $notification->content,
            'type' => $notification->type,
            'is_read' => (bool) $notification->is_read,
            'created_at' => $notification->created_at,
            'url' => $notification->url,
            'media' => $notification->media ? [
                'id' => $notification->media->id,
                'url' => $notification->media->url,
                'type' => $notification->media->type
            ] : null,
            'formatted_date' => $notification->created_at->diffForHumans()
        ];
    }
}
