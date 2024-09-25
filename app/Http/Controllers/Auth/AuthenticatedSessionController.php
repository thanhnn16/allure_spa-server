<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="API Documentation", version="1.0.0")
 */

/**
 * @OA\Post(
 *     path="/login",
 *     summary="Login",
 *     description="Authenticates a user and returns a token",
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="phone_number", type="string", example="1234567890"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Login successful"),
 *             @OA\Property(property="status_code", type="integer", example=200),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user", type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", example="john@example.com")
 *                 ),
 *                 @OA\Property(property="token", type="string", example="1234567890")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="status_code", type="integer", example=401),
 *             @OA\Property(property="errors", type="array",
 *                 @OA\Items(type="string", example="Invalid credentials")
 *             )
 *         )
 *     )
 * )
 */

/**
 * @OA\Post(
 *     path="/logout",
 *     summary="Logout",
 *     description="Logs out the authenticated user",
 *     @OA\Response(
 *         response=200,
 *         description="Logout successful",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Logout successful"),
 *             @OA\Property(property="status_code", type="integer", example=200)
 *         )
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/user",
 *     summary="Get user details",
 *     description="Retrieves the details of the authenticated user",
 *     @OA\Response(
 *         response=200,
 *         description="User details retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="User details retrieved successfully"),
 *             @OA\Property(property="status_code", type="integer", example=200),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user", type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", example="john@example.com")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="status_code", type="integer", example=401),
 *             @OA\Property(property="errors", type="array",
 *                 @OA\Items(type="string", example="Unauthenticated")
 *             )
 *         )
 *     )
 * )
 */


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('LoginView', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        Log::info('User authenticated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'session_id' => session()->getId()
        ]);

        return redirect()->intended(route('dashboard'));
    }

    private function getDetailedErrorMessage(ValidationException $e): string
    {
        $user = Auth::getProvider()->retrieveByCredentials([
            'phone_number' => request('phone_number')
        ]);

        if (!$user) {
            return 'Không tìm thấy tài khoản với số điện thoại này.';
        }

        if (!Auth::getProvider()->validateCredentials($user, ['password' => request('password')])) {
            return 'Mật khẩu không chính xác.';
        }

        return 'Đã xảy ra lỗi không xác định trong quá trình đăng nhập.';
    }

    public function storeApi(Request $request): JsonResponse
    {
        try {
            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user) {
                throw new \Exception('Không tìm thấy người dùng');
            }

            if (!Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])) {
                throw new \Exception('Thông tin đăng nhập không chính xác');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Đ��ng nhập thành công',
                'status_code' => 200,
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đăng nhập thất bại',
                'status_code' => 401,
                'errors' => [$e->getMessage()]
            ], 401);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
