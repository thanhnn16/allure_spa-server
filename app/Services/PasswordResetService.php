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

        // Tạo token reset password
        $token = Password::createToken($user);

        // Tạo URL reset password
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $email,
            'lang' => $lang
        ], false));

        try {
            // Gửi email
            Mail::to($user->email)
                ->locale($lang)
                ->send(new ResetPasswordEmail($resetUrl, $lang));

            return [
                'message' => 'PASSWORD_RESET_LINK_SENT',
                'email' => $user->email
            ];
        } catch (\Exception $e) {
            Log::error('Error sending password reset email: ' . $e->getMessage());
            throw new \Exception('FAILED_TO_SEND_RESET_EMAIL');
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
