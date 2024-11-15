<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Enums\AuthErrorCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected $fcmTokenService;

    public function __construct(FcmTokenService $fcmTokenService)
    {
        $this->fcmTokenService = $fcmTokenService;
    }

    public function login(array $credentials)
    {
        $validator = Validator::make($credentials, [
            'phone_number' => 'required|string|regex:/^[0-9]{10}$/',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('phone_number')) {
                throw new \Exception(AuthErrorCode::INVALID_PHONE_FORMAT->value);
            }
            if ($errors->has('password')) {
                throw new \Exception(AuthErrorCode::INVALID_PASSWORD_FORMAT->value);
            }
            throw new \Exception(AuthErrorCode::VALIDATION_ERROR->value);
        }

        $user = User::where('phone_number', $credentials['phone_number'])->first();

        if (!$user) {
            throw new \Exception(AuthErrorCode::USER_NOT_FOUND->value);
        }

        if (!Auth::attempt($credentials)) {
            throw new \Exception(AuthErrorCode::WRONG_PASSWORD->value);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function register(array $data)
    {
        $validator = Validator::make($data, [
            'full_name' => 'required|string|max:255|regex:/^[\pL\s\-]+$/u',
            'phone_number' => 'required|string|regex:/^[0-9]{10}$/|unique:users,phone_number',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'email' => 'nullable|email|unique:users,email'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('full_name')) {
                throw new \Exception(AuthErrorCode::INVALID_NAME_FORMAT->value);
            }
            if ($errors->has('phone_number')) {
                if (User::where('phone_number', $data['phone_number'])->exists()) {
                    throw new \Exception(AuthErrorCode::PHONE_ALREADY_EXISTS->value);
                }
                throw new \Exception(AuthErrorCode::INVALID_PHONE_FORMAT->value);
            }
            if ($errors->has('password')) {
                throw new \Exception(AuthErrorCode::INVALID_PASSWORD_FORMAT->value);
            }
            if ($errors->has('password_confirmation')) {
                throw new \Exception(AuthErrorCode::PASSWORDS_NOT_MATCH->value);
            }
            if ($errors->has('email')) {
                if (User::where('email', $data['email'])->exists()) {
                    throw new \Exception(AuthErrorCode::EMAIL_ALREADY_EXISTS->value);
                }
                throw new \Exception(AuthErrorCode::INVALID_EMAIL_FORMAT->value);
            }
            throw new \Exception(AuthErrorCode::VALIDATION_ERROR->value);
        }

        $user = User::create([
            'id' => Str::uuid(),
            'full_name' => $data['full_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Store FCM token for authenticated user
     */
    public function storeFcmToken(string $userId, string $token, string $deviceType): array
    {
        try {
            // Validate device type
            if (!in_array($deviceType, ['android', 'ios', 'web'])) {
                throw new \Exception('Invalid device type');
            }

            $fcmToken = $this->fcmTokenService->storeFcmToken(
                $userId,
                $token,
                $deviceType
            );

            return [
                'data' => $fcmToken,
                'message' => 'FCM token stored successfully',
                'status_code' => 200
            ];
        } catch (\Exception $e) {
            Log::error('Error storing FCM token: ' . $e->getMessage(), [
                'user_id' => $userId,
                'device_type' => $deviceType
            ]);
            throw new \Exception(AuthErrorCode::SERVER_ERROR->value);
        }
    }

    public function changePassword(User $user, array $data)
    {
        $validator = Validator::make($data, [
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('current_password')) {
                throw new \Exception(AuthErrorCode::INVALID_PASSWORD_FORMAT->value);
            }
            if ($errors->has('new_password')) {
                throw new \Exception(AuthErrorCode::INVALID_PASSWORD_FORMAT->value);
            }
            throw new \Exception(AuthErrorCode::VALIDATION_ERROR->value);
        }

        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception(AuthErrorCode::WRONG_PASSWORD->value);
        }

        // Update password
        $user->password = Hash::make($data['new_password']);
        $user->save();

        return [
            'message' => 'Password changed successfully'
        ];
    }
}
