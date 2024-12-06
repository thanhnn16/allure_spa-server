<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;
use App\Mail\EmailVerificationSuccess;

class EmailVerificationService
{
    public function sendVerificationEmail(User $user, string $lang = 'vi')
    {
        // Delete any existing tokens for this user
        VerificationToken::where('user_id', $user->id)
            ->where('type', 'email')
            ->delete();

        // Create new token
        $token = VerificationToken::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'type' => 'email',
            'expires_at' => now()->addHours(24)
        ]);

        // Send verification email with language
        Mail::to($user->email)
            ->locale($lang)
            ->send(new EmailVerification($token));

        return $token;
    }

    public function verifyEmail(string $token)
    {
        $verificationToken = VerificationToken::where('token', $token)
            ->where('type', 'email')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationToken) {
            return [
                'success' => false,
                'message' => 'Invalid or expired token'
            ];
        }

        $user = User::find($verificationToken->user_id);
        $user->email_verified_at = now();
        $user->save();

        // Gửi email xác thực thành công
        Mail::to($user->email)
            ->locale($user->locale ?? 'vi')
            ->send(new EmailVerificationSuccess($user->locale ?? 'vi'));

        $verificationToken->delete();

        return [
            'success' => true,
            'user' => $user
        ];
    }
}
