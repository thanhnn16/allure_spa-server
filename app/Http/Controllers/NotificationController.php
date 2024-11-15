<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'type' => $notification->type,
                    'is_read' => (bool) $notification->is_read,
                    'created_at' => $notification->created_at,
                    'url' => $notification->url
                ];
            });

        if ($request->wantsJson()) {
            return $this->respondWithJson([
                'data' => $notifications
            ]);
        }

        return $this->respondWithInertia('Notifications/Index', [
            'data' => $notifications
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return $this->respondWithJson($notification);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::user()->id)
            ->update(['is_read' => true]);

        return $this->respondWithJson(null, 'All notifications marked as read');
    }
}
