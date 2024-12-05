<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;

class PasswordResetService
{
    public function sendResetLink(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->email_verified_at) {
            throw new \Exception('Email chưa được xác thực hoặc không tồn tại.');
        }

        $token = Password::createToken($user);

        // Gửi email
        Mail::to($user->email)->send(new ResetPassword($token));

        return [
            'success' => true,
            'message' => 'Đã gửi email khôi phục mật khẩu.'
        ];
    }

    public function reset(array $data)
    {
        return Password::reset($data, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->setRememberToken(Str::random(60));
            $user->save();
        });
    }
}
