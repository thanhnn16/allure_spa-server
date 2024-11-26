<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

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

    // Add language constants
    const SUPPORTED_LANGUAGES = ['en', 'vi', 'ja'];
    const DEFAULT_LANGUAGE = 'en';

    // Add notification messages for different languages
    const NOTIFICATION_MESSAGES = [
        'appointment' => [
            'new' => [
                'en' => [
                    'title' => 'New Appointment',
                    'content' => 'Your appointment has been booked for {date}'
                ],
                'vi' => [
                    'title' => 'Lịch hẹn mới',
                    'content' => 'Lịch hẹn của bạn đã được đặt vào {date}'
                ],
                'ja' => [
                    'title' => '新しい予約',
                    'content' => '予約が{date}に設定されました'
                ]
            ],
            'status' => [
                'en' => [
                    'title' => 'Appointment Status Updated',
                    'content' => 'Your appointment #{id} has been {status}'
                ],
                'vi' => [
                    'title' => 'Cập nhật trạng thái lịch hẹn',
                    'content' => 'Lịch hẹn #{id} đã được {status}'
                ],
                'ja' => [
                    'title' => '予約状態が更新されました',
                    'content' => '予約 #{id}が{status}になりました'
                ]
            ],
            // ... add other status messages
        ],
        'order' => [
            'new' => [
                'en' => [
                    'title' => 'New Order',
                    'content' => 'Your order #{id} has been placed successfully'
                ],
                'vi' => [
                    'title' => 'Đơn hàng mới',
                    'content' => 'Đơn hàng #{id} đã được tạo thành công'
                ],
                'ja' => [
                    'title' => '新規注文',
                    'content' => '注文 #{id}が正常に作成されました'
                ]
            ],
            // ... add other order messages
        ]
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
        // Prepare multilingual content
        $notificationData = [
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'data' => $data['data'] ?? null,
            'is_read' => false
        ];

        // Set default English content
        $notificationData['title'] = is_array($data['title']) ? 
            ($data['title']['en'] ?? array_values($data['title'])[0]) : 
            $data['title'];
        
        $notificationData['content'] = is_array($data['content']) ? 
            ($data['content']['en'] ?? array_values($data['content'])[0]) : 
            $data['content'];

        // Create notification
        $notification = Notification::create($notificationData);

        // Create translations if multilingual content provided
        if (is_array($data['title'])) {
            foreach (self::SUPPORTED_LANGUAGES as $lang) {
                if ($lang === 'en' || !isset($data['title'][$lang])) continue;
                
                $notification->translations()->create([
                    'language' => $lang,
                    'field' => 'title',
                    'value' => $data['title'][$lang]
                ]);
            }
        }

        if (is_array($data['content'])) {
            foreach (self::SUPPORTED_LANGUAGES as $lang) {
                if ($lang === 'en' || !isset($data['content'][$lang])) continue;
                
                $notification->translations()->create([
                    'language' => $lang,
                    'field' => 'content',
                    'value' => $data['content'][$lang]
                ]);
            }
        }

        // Get user's preferred language
        $user = User::find($data['user_id']);
        $userLang = $user->preferred_language ?? self::DEFAULT_LANGUAGE;

        // Get translated content for FCM
        $fcmTitle = $this->getTranslatedField($notification, 'title', $userLang);
        $fcmContent = $this->getTranslatedField($notification, 'content', $userLang);

        // Send FCM with translated content
        $this->sendFCMNotification($notification, $fcmTitle, $fcmContent);

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

    private function getTranslatedField($notification, $field, $language)
    {
        if ($language === 'en') {
            return $notification->$field;
        }

        $translation = $notification->translations()
            ->where('field', $field)
            ->where('language', $language)
            ->first();

        return $translation ? $translation->value : $notification->$field;
    }

    private function sendFCMNotification($notification, $title, $content)
    {
        // Get user's FCM tokens
        $tokens = $this->fcmTokenService->getUserTokens($notification->user_id);

        // Send to each token
        foreach ($tokens as $token) {
            $this->firebaseService->sendMessage(
                $token,
                $title,
                $content,
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

    // Helper method to get translated message
    private function getNotificationMessage($type, $subType, $language, $params = [])
    {
        $messages = self::NOTIFICATION_MESSAGES[$type][$subType][$language] ?? 
                   self::NOTIFICATION_MESSAGES[$type][$subType][self::DEFAULT_LANGUAGE];
                   
        return [
            'title' => $messages['title'],
            'content' => $this->replacePlaceholders($messages['content'], $params)
        ];
    }

    // Helper method to replace placeholders
    private function replacePlaceholders($text, $params)
    {
        foreach ($params as $key => $value) {
            $text = str_replace("{{$key}}", $value, $text);
        }
        return $text;
    }
}
