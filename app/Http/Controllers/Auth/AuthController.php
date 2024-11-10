<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\AuthService;
use App\Services\FcmTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\LoginRequest;

class AuthController extends BaseController
{
    protected $authService;
    protected $fcmTokenService;

    public function __construct(
        AuthService $authService,
        FcmTokenService $fcmTokenService
    ) {
        $this->authService = $authService;
        $this->fcmTokenService = $fcmTokenService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Đăng nhập người dùng",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_number", "password"},
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đăng nhập thành công"
     *     )
     * )
     */
    public function login(Request $request)
    {
        // Handle API login
        if ($request->expectsJson()) {
            try {
                $credentials = $request->validate([
                    'phone_number' => 'required|string',
                    'password' => 'required|string',
                ]);

                $result = $this->authService->login($credentials);
                
                return $this->respondWithJson($result, 'Đăng nhập thành công');
            } catch (\Exception $e) {
                return $this->respondWithError($e->getMessage());
            }
        }

        // Handle Web login
        try {
            $loginRequest = LoginRequest::createFrom($request);
            $loginRequest->authenticate();

            $user = $request->user();
            Log::info('User authenticated', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email,
            ]);

            if ($user->role !== 'admin') {
                Log::warning('Non-admin user attempted to log in', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                ]);
                Auth::logout();
                throw ValidationException::withMessages([
                    'phone_number' => __('Bạn không có quyền truy cập hệ thống.'),
                ]);
            }

            $request->session()->regenerate();

            Log::info('User session regenerated', [
                'user_id' => $user->id,
                'session_id' => session()->getId()
            ]);

            return redirect()->intended(route('dashboard'))->with('auth_check', true);
        } catch (ValidationException $e) {
            Log::error('Login validation failed', [
                'errors' => $e->errors(),
            ]);
            return redirect()->back()->withErrors([
                'phone_number' => $this->getDetailedErrorMessage($e),
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Đăng ký người dùng mới",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name", "phone_number", "password", "password_confirmation"},
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đăng ký thành công"
     *     )
     * )
     */
    public function register(Request $request)
    {
        try {
            $result = $this->authService->register($request->all());
            return $this->respondWithJson($result, 'Đăng ký thành công', 201);
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Đăng xuất người dùng",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="fcm_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đăng xuất thành công"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            $userId = Auth::id();
            $fcmToken = $request->input('fcm_token');

            if ($fcmToken) {
                $this->fcmTokenService->removeFcmToken($userId, $fcmToken);
            }

            if ($request->expectsJson()) {
                $request->user()->currentAccessToken()->delete();
                return $this->respondWithJson(null, 'Đăng xuất thành công');
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return $this->respondWithError('SERVER_ERROR');
            }
            return redirect('/')->withErrors(['error' => 'Có lỗi xảy ra khi đăng xuất.']);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/fcm-token",
     *     summary="Lưu token FCM cho người dùng",
     *     description="Lưu token FCM và loại thiết bị cho người dùng đã xác thực",
     *     operationId="storeFcmToken", 
     *     tags={"Authentication"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "device_type"},
     *             @OA\Property(property="token", type="string", example="fMxn8H2zQ9G....", description="FCM token"),
     *             @OA\Property(
     *                 property="device_type", 
     *                 type="string", 
     *                 example="android", 
     *                 description="Loại thiết bị (android/ios)",
     *                 enum={"android", "ios"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token FCM đã được lưu thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="FCM token stored successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="token", type="string", example="fMxn8H2zQ9G...."),
     *                 @OA\Property(property="device_type", type="string", example="android"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ"
     *     )
     * )
     */
    public function storeFcmToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_type' => 'required|string|in:android,ios'
        ]);

        try {
            $result = $this->authService->storeFcmToken(
                Auth::id(),
                $validated['token'],
                $validated['device_type']
            );

            return $this->respondWithJson($result['data'], $result['message']);
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * Get detailed error message from validation exception
     */
    private function getDetailedErrorMessage(ValidationException $e): string
    {
        $errors = $e->errors();
        return isset($errors['phone_number']) ? $errors['phone_number'][0] : 'Đăng nhập không thành công';
    }
}
