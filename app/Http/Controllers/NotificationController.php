<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
                'data' => [
                    'data' => $notifications['items'],
                    'hasMore' => $notifications['hasMore'],
                    'unreadCount' => $notifications['unreadCount']
                ]
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
}
