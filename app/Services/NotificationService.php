<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
            'cancelled' => [
                'en' => [
                    'title' => 'Appointment Cancelled',
                    'content' => 'Your appointment #{id} has been cancelled'
                ],
                'vi' => [
                    'title' => 'Lịch hẹn đã bị hủy',
                    'content' => 'Lịch hẹn #{id} đã bị hủy'
                ],
                'ja' => [
                    'title' => '予約がキャンセルされました',
                    'content' => '予約 #{id}がキャンセルされました'
                ]
            ]
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
            'status' => [
                'en' => [
                    'title' => 'Order Status Updated',
                    'content' => 'Your order #{id} has been {status}'
                ],
                'vi' => [
                    'title' => 'Cập nhật trạng thái đơn hàng',
                    'content' => 'Đơn hàng #{id} đã được {status}'
                ],
                'ja' => [
                    'title' => '注文状態が更新されました',
                    'content' => '注文 #{id}が{status}になりました'
                ]
            ],
            'completed' => [
                'en' => [
                    'title' => 'Order Completed',
                    'content' => 'Your order #{id} has been completed'
                ],
                'vi' => [
                    'title' => 'Đơn hàng hoàn thành',
                    'content' => 'Đơn hàng #{id} đã hoàn thành'
                ],
                'ja' => [
                    'title' => '注文完了',
                    'content' => '注文 #{id}が完了しました'
                ]
            ]
        ],
        'service' => [
            'low_sessions' => [
                'en' => [
                    'title' => 'Service Package Running Low',
                    'content' => 'Your {service} package has only {remaining} sessions remaining'
                ],
                'vi' => [
                    'title' => 'Gói dịch vụ sắp hết',
                    'content' => 'Gói dịch vụ {service} của bạn chỉ còn {remaining} buổi'
                ],
                'ja' => [
                    'title' => 'サービスパッケージの残りが少なくなっています',
                    'content' => '{service}パッケージの残りセッションが{remaining}回となっています'
                ]
            ],
            'completed' => [
                'en' => [
                    'title' => 'Treatment Session Completed',
                    'content' => 'Your {service} treatment session with {staff_name} has been completed. You have {remaining} sessions remaining.'
                ],
                'vi' => [
                    'title' => 'Hoàn thành buổi điều trị',
                    'content' => 'Buổi điều trị {service} với {staff_name} đã hoàn thành. Bạn còn {remaining} buổi.'
                ],
                'ja' => [
                    'title' => '施術セッション完了',
                    'content' => '{staff_name}による{service}の施術が完了しました。残りセッションは{remaining}回です。'
                ]
            ]
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
        try {
            // Get user's preferred language
            $user = User::find($data['user_id']);
            $userLang = $user->preferred_language ?? self::DEFAULT_LANGUAGE;

            // Check if this is a predefined notification type
            $type = explode('_', $data['type']);
            if (
                count($type) >= 2 &&
                isset(self::NOTIFICATION_MESSAGES[$type[0]][$type[1]])
            ) {
                // Get translated messages for all supported languages
                $translations = [];
                foreach (self::SUPPORTED_LANGUAGES as $lang) {
                    $message = $this->getNotificationMessage(
                        $type[0],
                        $type[1],
                        $lang,
                        $data['data'] ?? []
                    );
                    $translations['title'][$lang] = $message['title'];
                    $translations['content'][$lang] = $message['content'];
                }

                // Set default content from user's preferred language
                $defaultMessage = $this->getNotificationMessage(
                    $type[0],
                    $type[1],
                    $userLang,
                    $data['data'] ?? []
                );
                $data['title'] = $defaultMessage['title'];
                $data['content'] = $defaultMessage['content'];
            } else {
                // Use provided translations if available
                $translations = [
                    'title' => is_array($data['title']) ? $data['title'] : [],
                    'content' => is_array($data['content']) ? $data['content'] : []
                ];

                // Set default content
                $data['title'] = is_array($data['title']) ?
                    ($data['title'][$userLang] ?? $data['title']['en'] ?? array_values($data['title'])[0]) :
                    $data['title'];

                $data['content'] = is_array($data['content']) ?
                    ($data['content'][$userLang] ?? $data['content']['en'] ?? array_values($data['content'])[0]) :
                    $data['content'];
            }

            // Create notification
            $notification = Notification::create([
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'title' => $data['title'],
                'content' => $data['content'],
                'data' => $data['data'] ?? null,
                'is_read' => false
            ]);

            // Create translations
            foreach (self::SUPPORTED_LANGUAGES as $lang) {
                if ($lang === $userLang) continue;

                if (isset($translations['title'][$lang])) {
                    $notification->translations()->create([
                        'language' => $lang,
                        'field' => 'title',
                        'value' => $translations['title'][$lang]
                    ]);
                }

                if (isset($translations['content'][$lang])) {
                    $notification->translations()->create([
                        'language' => $lang,
                        'field' => 'content',
                        'value' => $translations['content'][$lang]
                    ]);
                }
            }

            // Send FCM notification
            $this->sendFCMNotification(
                $notification,
                $data['title'],
                $data['content']
            );

            return $notification;
        } catch (\Exception $e) {
            Log::error('Error creating notification:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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
            ->with(['media', 'translations'])
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
        // Get translations for all supported languages
        $translations = [
            'title' => [],
            'content' => []
        ];

        // Group translations by field and language
        foreach ($notification->translations as $translation) {
            if ($translation instanceof \App\Models\Translation) {
                $translations[$translation->field][$translation->language] = $translation->value;
            }
        }

        // Always include original text as English translation
        $translations['title']['en'] = $notification->title;
        $translations['content']['en'] = $notification->content;

        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'content' => $notification->content,
            'type' => $notification->type,
            'is_read' => (bool) $notification->is_read,
            'created_at' => $notification->created_at,
            'url' => $notification->url,
            'translations' => $translations,
            'media' => $notification->media ? [
                'id' => $notification->media->id,
                'url' => $notification->media->url,
                'type' => $notification->media->type
            ] : null,
            'formatted_date' => $notification->created_at->diffForHumans(),
            'data' => $notification->data
        ];
    }

    // Helper method to get translated message
    private function getNotificationMessage($type, $subType, $language, $params = [])
    {
        // Kiểm tra xem type và subType có tồn tại trong NOTIFICATION_MESSAGES không
        if (!isset(self::NOTIFICATION_MESSAGES[$type]) || 
            !isset(self::NOTIFICATION_MESSAGES[$type][$subType])) {
            // Trả về message mặc định nếu không tìm thấy
            return [
                'title' => 'Notification',
                'content' => 'You have a new notification'
            ];
        }

        // Lấy message theo ngôn ngữ, fallback về DEFAULT_LANGUAGE nếu không có
        $messages = self::NOTIFICATION_MESSAGES[$type][$subType][$language] ?? 
                   self::NOTIFICATION_MESSAGES[$type][$subType][self::DEFAULT_LANGUAGE] ?? 
                   ['title' => 'Notification', 'content' => 'You have a new notification'];
                   
        return [
            'title' => $this->replacePlaceholders($messages['title'], $params),
            'content' => $this->replacePlaceholders($messages['content'], $params)
        ];
    }

    // Helper method to replace placeholders
    private function replacePlaceholders($text, $params)
    {
        if (empty($params)) {
            return $text;
        }

        foreach ($params as $key => $value) {
            // Đảm bảo value là string
            $value = is_string($value) || is_numeric($value) ? (string)$value : '';
            $text = str_replace("{{$key}}", $value, $text);
        }
        return $text;
    }
}
