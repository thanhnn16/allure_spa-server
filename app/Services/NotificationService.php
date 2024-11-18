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
