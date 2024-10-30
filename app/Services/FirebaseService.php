<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = storage_path('app/allure-spa-app-firebase-adminsdk-r64oy-b723cdb13c.json');

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
        }
    }

    public function sendMessage($token, $title, $body, $data = [])
    {
        try {
            if (!$this->messaging) {
                throw new \Exception('Firebase messaging not initialized');
            }

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification([
                    'title' => $title,
                    'body' => $body
                ])
                ->withData($data);

            return $this->messaging->send($message);
        } catch (\Exception $e) {
            Log::error('Failed to send Firebase message: ' . $e->getMessage());
            throw $e;
        }
    }
}
