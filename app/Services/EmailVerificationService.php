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

        // Tạo verification URL sử dụng route web
        $verificationUrl = route('verification.verify', ['token' => $token->token]);

        // Send verification email with language
        Mail::to($user->email)
            ->locale($lang)
            ->send(new EmailVerification($token, $verificationUrl));

        return $token;
    }

    public function verifyEmail(string $token, string $lang = 'vi')
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

        // Sử dụng ngôn ngữ được truyền vào thay vì lấy từ user
        Mail::to($user->email)
            ->locale($lang)
            ->send(new EmailVerificationSuccess($lang));

        $verificationToken->delete();

        return [
            'success' => true,
            'user' => $user
        ];
    }
}
