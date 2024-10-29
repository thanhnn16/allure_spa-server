<?php

namespace App\Services;

use App\Models\FcmToken;

class FcmTokenService
{
    public function storeFcmToken($userId, $token, $deviceType)
    {
        return FcmToken::updateOrCreate(
            [
                'user_id' => $userId,
                'device_type' => $deviceType
            ],
            ['token' => $token]
        );
    }

    public function getUserTokens($userId)
    {
        return FcmToken::where('user_id', $userId)->pluck('token');
    }

    public function removeFcmToken($userId, $token = null)
    {
        $query = FcmToken::where('user_id', $userId);

        if ($token) {
            $query->where('token', $token);
        }

        return $query->delete();
    }
}
