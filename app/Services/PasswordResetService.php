<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordResetService
{
    public function sendResetLink(string $email, string $lang = 'vi')
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->email_verified_at) {
            throw new \Exception('Email chưa được xác thực hoặc không tồn tại.');
        }

        try {
            // Tạo token reset password
            $token = Password::createToken($user);

            // Tạo URL reset password cho mobile app
            $appUrl = "allurespa://reset-password?token={$token}&email={$email}&lang={$lang}";

            // Tạo URL fallback cho web
            $webUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $email,
                'lang' => $lang
            ], false));

            // URL redirect sẽ thử mở app trước, nếu không được sẽ chuyển sang web
            $resetUrl = url(route('password.redirect', [
                'token' => $token,
                'email' => $email,
                'lang' => $lang
            ], false));

            // Gửi email với cả 2 URL
            Mail::to($user->email)
                ->locale($lang)
                ->send(new ResetPasswordEmail($resetUrl, $token, $lang));

            return [
                'success' => true,
                'data' => [
                    'message' => 'PASSWORD_RESET_LINK_SENT',
                    'email' => $user->email
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error sending password reset email: ' . $e->getMessage(), [
                'email' => $email,
                'error' => $e
            ]);

            return [
                'success' => false,
                'message' => 'FAILED_TO_SEND_RESET_EMAIL',
                'status_code' => 400
            ];
        }
    }

    public function reset(array $data)
    {
        return Password::reset($data, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });
    }
}
