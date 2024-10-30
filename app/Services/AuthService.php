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
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'email' => 'nullable|email|unique:users,email'
        ]);

        if ($validator->fails()) {
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
            Log::error('Error storing FCM token: ' . $e->getMessage());
            throw new \Exception(AuthErrorCode::SERVER_ERROR->value);
        }
    }
}
