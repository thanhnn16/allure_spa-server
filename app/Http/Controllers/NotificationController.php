<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\NotificationGroup;
use App\Models\User;

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

    public function getGroups()
    {
        try {
            $groups = NotificationGroup::with(['conditions'])
                ->get()
                ->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                        'conditions' => $group->conditions,
                        'user_count' => $group->users_count
                    ];
                });

            return $this->respondWithJson($groups);
        } catch (\Exception $e) {
            \Log::error('Error in getGroups: ' . $e->getMessage());
            return $this->respondWithError('Lỗi khi lấy danh sách nhóm', 500);
        }
    }

    public function createGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'conditions' => 'required|array|min:1',
            'conditions.*.field' => 'required|string',
            'conditions.*.operator' => 'required|string',
            'conditions.*.value' => 'required'
        ]);

        $group = NotificationGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        foreach ($validated['conditions'] as $condition) {
            $group->conditions()->create($condition);
        }

        return $this->respondWithJson($group->load('conditions'));
    }

    public function updateGroup(Request $request, $id)
    {
        $group = NotificationGroup::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'conditions' => 'required|array|min:1',
            'conditions.*.field' => 'required|string',
            'conditions.*.operator' => 'required|string',
            'conditions.*.value' => 'required'
        ]);

        $group->update([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        // Xóa điều kiện cũ và tạo mới
        $group->conditions()->delete();
        foreach ($validated['conditions'] as $condition) {
            $group->conditions()->create($condition);
        }

        return $this->respondWithJson($group->load('conditions'));
    }

    public function deleteGroup($id)
    {
        $group = NotificationGroup::findOrFail($id);
        $group->delete();
        return $this->respondWithJson(null, 'Group deleted successfully');
    }

    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'type' => 'required|string',
            'target_users' => 'required|string|in:all,specific,group',
            'user_ids' => 'required_if:target_users,specific|array',
            'group_id' => 'required_if:target_users,group|exists:notification_groups,id',
            'translations' => 'nullable|array'
        ]);

        try {
            $userIds = [];
            
            if ($validated['target_users'] === 'all') {
                $userIds = User::pluck('id')->toArray();
            } elseif ($validated['target_users'] === 'specific') {
                $userIds = $validated['user_ids'];
            } elseif ($validated['target_users'] === 'group') {
                $group = NotificationGroup::with('conditions')->findOrFail($validated['group_id']);
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
        
        $notifications = Notification::orderBy('created_at', 'desc')
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
}
