<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FirebaseWebhookController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function handleMessage(Request $request)
    {
        try {
            // Verify Firebase authentication if needed
            
            $payload = $request->all();
            $message = $this->firebaseService->handleIncomingMessage($payload);
            
            return response()->json([
                'success' => true,
                'message' => 'Message processed successfully',
                'data' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Firebase webhook error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process message'
            ], 500);
        }
    }
} 