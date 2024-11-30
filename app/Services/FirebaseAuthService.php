<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class FirebaseAuthService
{
    protected $auth;

    public function __construct()
    {
        $credentialsPath = storage_path('app/allure-spa-app-firebase-adminsdk-r64oy-b723cdb13c.json');
        $this->auth = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->createAuth();
    }

    /**
     * Send OTP verification code to phone number
     */
    public function sendVerificationCode(string $phoneNumber)
    {
        try {
            // Create custom token for phone auth
            $customToken = $this->auth->createCustomToken($phoneNumber);

            // Store the token temporarily (you might want to use cache or session)
            $verificationId = Str::random(32);
            Cache::put('phone_verification_' . $verificationId, $phoneNumber, now()->addMinutes(5));

            return [
                'success' => true,
                'verification_id' => $verificationId,
                'custom_token' => $customToken->toString()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send verification code: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyPhoneNumber(string $verificationId, string $code)
    {
        try {
            // Get phone number from cache
            $phoneNumber = Cache::get('phone_verification_' . $verificationId);

            if (!$phoneNumber) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired verification ID'
                ];
            }

            // Verify the ID token from Firebase client
            $idToken = $this->auth->verifyIdToken($code);
            $uid = $idToken->claims()->get('sub');

            if (!$uid) {
                return [
                    'success' => false,
                    'message' => 'Invalid verification code'
                ];
            }

            // Clear verification data
            Cache::forget('phone_verification_' . $verificationId);

            return [
                'success' => true,
                'phone_number' => $phoneNumber
            ];
        } catch (\Exception $e) {
            Log::error('Phone verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateUserPhoneVerification(User $user)
    {
        $user->phone_verified_at = now();
        $user->save();

        return $user;
    }
}
