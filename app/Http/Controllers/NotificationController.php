<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\NotificationGroup;
use App\Models\User;
use App\Models\UserGroup;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="API Endpoints for managing notifications"
 * )
 */
class NotificationController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get user notifications",
     *     tags={"Notifications"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Notification")),
     *             @OA\Property(property="hasMore", type="boolean")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = 10;
        $notifications = $this->notificationService->getUserNotifications(
            Auth::user()->id,
            $perPage,
            $request->input('page', 1)
        );

        if ($request->wantsJson()) {
            return $this->respondWithJson([
                'data' => $notifications['items'],
                'hasMore' => $notifications['hasMore'],
                'unreadCount' => $notifications['unreadCount']
            ]);
        }

        return $this->respondWithInertia('Notifications/Index', [
            'initialNotifications' => $notifications['items'],
            'hasMore' => $notifications['hasMore'],
            'unreadCount' => $notifications['unreadCount']
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/{id}/mark-as-read",
     *     summary="Mark notification as read",
     *     tags={"Notifications"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Notification")
     *     )
     * )
     */
    public function markAsRead($id)
    {
        $notification = $this->notificationService->markAsRead($id);
        return $this->respondWithJson($notification);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/mark-all-as-read",
     *     summary="Mark all notifications as read",
     *     tags={"Notifications"},
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::user()->id);
        return $this->respondWithJson(null, 'All notifications marked as read');
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread-count",
     *     summary="Get unread notifications count",
     *     tags={"Notifications"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="count", type="integer")
     *         )
     *     )
     * )
     */
    public function getUnreadCount()
    {
        $count = $this->notificationService->getUnreadCount(Auth::user()->id);
        return $this->respondWithJson(['count' => $count]);
    }

    public function manager()
    {
        return $this->respondWithInertia('MobileApp/NotificationManager', [
            'initialNotifications' => $this->notificationService->getUserNotifications(
                Auth::user()->id,
                10,
                1
            )
        ]);
    }

    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'type' => 'required|string',
            'target_users' => 'required|string|in:all,specific,group',
            'user_ids' => 'required_if:target_users,specific|array',
            'group_id' => 'required_if:target_users,group|exists:user_groups,id',
            'translations' => 'nullable|array'
        ]);

        try {
            $userIds = [];

            if ($validated['target_users'] === 'all') {
                $userIds = User::pluck('id')->toArray();
            } elseif ($validated['target_users'] === 'specific') {
                $userIds = $validated['user_ids'];
            } elseif ($validated['target_users'] === 'group') {
                $group = UserGroup::with('conditions')->findOrFail($validated['group_id']);
                $userIds = $this->notificationService->getUsersByGroupConditions($group->conditions);
            }

            foreach ($userIds as $userId) {
                $this->notificationService->createNotification([
                    'user_id' => $userId,
                    'type' => $validated['type'],
                    'title' => $validated['title'],
                    'content' => $validated['content'],
                    'translations' => $validated['translations'] ?? [],
                    'send_fcm' => true
                ]);
            }

            return $this->respondWithJson(null, 'Notifications sent successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    public function getAllNotifications(Request $request)
    {
        $perPage = 10;
        $page = $request->input('page', 1);

        $notifications = Notification::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->respondWithJson([
            'data' => $notifications->items(),
            'hasMore' => $notifications->hasMorePages()
        ]);
    }

    public function deleteNotification($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();
        return $this->respondWithJson(null, 'Notification deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/{id}",
     *     summary="Lấy chi tiết thông báo",
     *     tags={"Notifications"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Notification")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền truy cập thông báo này"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thông báo"
     *     )
     * )
     */
    public function getNotification($id)
    {
        $notification = Notification::with(['media', 'translations'])
            ->find($id);

        if (!$notification) {
            return $this->respondWithError('Không tìm thấy thông báo', 404);
        }

        // Kiểm tra xem thông báo có thuộc về người dùng hiện tại không
        if ($notification->user_id !== Auth::id()) {
            return $this->respondWithError('Bạn không có quyền xem thông báo này', 403);
        }

        return $this->respondWithJson($notification);
    }
}
