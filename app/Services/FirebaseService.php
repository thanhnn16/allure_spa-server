<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    protected $messaging;
    
    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials_path'));
        $this->messaging = $factory->createMessaging();
    }
    
    public function sendMessage($token, $title, $body, $data = [])
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification([
                'title' => $title,
                'body' => $body
            ])
            ->withData($data);
            
        return $this->messaging->send($message);
    }
} 