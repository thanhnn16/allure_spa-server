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
use App\Services\PasswordResetService;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Password;

class AuthController extends BaseController
{
    protected $authService;
    protected $fcmTokenService;
    protected $passwordResetService;
    protected $emailVerificationService;
    public function __construct(
        AuthService $authService,
        FcmTokenService $fcmTokenService,
        PasswordResetService $passwordResetService,
        EmailVerificationService $emailVerificationService
    ) {
        $this->authService = $authService;
        $this->fcmTokenService = $fcmTokenService;
        $this->passwordResetService = $passwordResetService;
        $this->emailVerificationService = $emailVerificationService;
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

            // Tạo lại token CSRF và gán vào session và cookie
            $newCsrfToken = csrf_token();
            $request->session()->put('_token', $newCsrfToken);

            $response = redirect()->intended(route('dashboard'))->with('auth_check', true);
            $response->withCookie(cookie()->forever(config('session.cookie'), session()->getId()));
            $response->withCookie(cookie('XSRF-TOKEN', $newCsrfToken));

            return $response;
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
     *     path="/api/fcm/token",
     *     summary="Lưu token FCM cho người dùng",
     *     description="Lưu token FCM và loại thiết bị cho người dùng đã xác thực (hỗ trợ cả web và mobile)",
     *     operationId="storeFcmToken", 
     *     tags={"Authentication"},
     *     security={{ "bearerAuth": {}, "cookieAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "device_type"},
     *             @OA\Property(property="token", type="string", example="fMxn8H2zQ9G....", description="FCM token"),
     *             @OA\Property(
     *                 property="device_type", 
     *                 type="string", 
     *                 example="web", 
     *                 description="Loại thiết bị (android/ios/web)",
     *                 enum={"android", "ios", "web"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token FCM đã được lưu thành công"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực"
     *     )
     * )
     */
    public function storeFcmToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'device_type' => 'required|string|in:android,ios,web'
            ]);

            $userId = Auth::guard('web')->check()
                ? Auth::guard('web')->id()
                : Auth::guard('sanctum')->id();

            if (!$userId) {
                return $this->respondWithError('Unauthorized', 401);
            }

            $result = $this->authService->storeFcmToken(
                $userId,
                $validated['token'],
                $validated['device_type']
            );

            return $this->respondWithJson($result['data'], $result['message']);
        } catch (\Exception $e) {
            Log::error('FCM Token Store Error:', [
                'error' => $e->getMessage(),
                'user' => Auth::user()?->id
            ]);
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/change-password",
     *     summary="Đổi mật khẩu người dùng",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string"),
     *             @OA\Property(property="new_password", type="string"),
     *             @OA\Property(property="new_password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đổi mật khẩu thành công"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi validation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực"
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        try {
            $user = Auth::user();
            Log::info('User changing password', [
                'user' => $user,
                'request' => $request->all()
            ]);

            if (!$user) {
                return $this->respondWithError('Unauthorized', 401);
            }

            $result = $this->authService->changePassword(
                $user,
                $request->all()
            );
            return $this->respondWithJson($result, 'Đổi mật khẩu thành công');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/email/verify-email",
     *     summary="Verify email address",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="lang", type="string", example="vi")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Email verified successfully")
     * )
     */
    public function verifyEmail($token)
    {
        $result = app(EmailVerificationService::class)->verifyEmail($token);

        // Nếu là request API
        if (request()->expectsJson()) {
            if ($result['success']) {
                return response()->json([
                    'message' => __('messages.verification_successful'),
                    'user' => $result['user']
                ]);
            }
            return response()->json([
                'message' => $result['message']
            ], 400);
        }

        // Nếu là request web
        if ($result['success']) {
            return view('auth.verify-email-success', [
                'lang' => $result['user']->locale ?? 'vi'
            ]);
        }

        return response()->view('auth.verify-email-error', [
            'message' => $result['message']
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/verify-phone",
     *     summary="Verify phone number",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"verification_id", "code"},
     *             @OA\Property(property="verification_id", type="string"),
     *             @OA\Property(property="code", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Phone number verified successfully")
     * )
     */
    public function verifyPhone(Request $request)
    {
        try {
            $result = $this->authService->verifyPhone(
                $request->verification_id,
                $request->code
            );
            return $this->respondWithJson($result, 'Phone number verified successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/resend-verification",
     *     summary="Resend verification email/SMS",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"type"},
     *             @OA\Property(property="type", type="string", enum={"email", "phone"}),
     *             @OA\Property(property="lang", type="string", example="vi")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Verification sent successfully")
     * )
     */
    public function resendVerification(Request $request)
    {
        try {
            $result = $this->authService->resendVerification(
                $request->user(),
                $request->type,
                $request->input('lang', 'vi')
            );
            return $this->respondWithJson($result, 'Verification sent successfully');
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



    /**
     * @OA\Post(
     *     path="/api/auth/forgot-password",
     *     summary="Gửi email khôi phục mật khẩu",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đã gửi email khôi phục mật khẩu"
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $result = $this->passwordResetService->sendResetLink($request->email);
            return $this->respondWithJson($result, 'Đã gửi email khôi phục mật khẩu');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/reset-password",
     *     summary="Đặt lại mật khẩu",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "token", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đặt lại mật khẩu thành công"
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed'
            ]);

            $status = $this->passwordResetService->reset($request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ));

            if ($status === Password::PASSWORD_RESET) {
                return $this->respondWithJson(null, 'Đặt lại mật khẩu thành công');
            }

            throw new \Exception(__($status));
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/email/verify/send",
     *     summary="Gửi email xác thực",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="lang", type="string", example="vi")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Email xác thực đã được gửi")
     * )
     */
    public function sendVerificationEmail(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->respondWithError('Unauthorized', 401);
            }

            if (!$user->email) {
                return $this->respondWithError('EMAIL_NOT_FOUND');
            }

            if ($user->email_verified_at) {
                return $this->respondWithError('EMAIL_ALREADY_VERIFIED');
            }

            // Lấy ngôn ngữ từ yêu cầu
            $lang = $request->input('lang', 'vi');

            $token = $this->emailVerificationService->sendVerificationEmail(
                $user,
                $lang // Truyền ngôn ngữ vào đây
            );

            return $this->respondWithJson([
                'message' => 'Verification email sent',
                'token' => $token->token
            ]);
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }
}
