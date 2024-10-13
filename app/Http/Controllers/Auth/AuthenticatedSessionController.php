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
use Illuminate\Support\Facades\Hash;

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
        try {
            $request->authenticate();

            $user = $request->user();

            if ($user->role !== 'admin') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'phone_number' => __('Bạn không có quyền truy cập hệ thống.'),
                ]);
            }

            $request->session()->regenerate();

            Log::info('User authenticated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'session_id' => session()->getId()
            ]);

            return redirect()->intended(route('dashboard'));
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors([
                'phone_number' => $this->getDetailedErrorMessage($e),
            ]);
        }
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

        if ($user->role !== 'admin') {
            return 'Bạn không có quyền truy cập hệ thống.';
        }

        return 'Đã xảy ra lỗi không xác định trong quá trình đăng nhập.';
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"phone_number", "password"},
     *             @OA\Property(property="phone_number", type="string", example="0123456789"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đăng nhập thành công"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                     @OA\Property(property="full_name", type="string", example="John Doe"),
     *                     @OA\Property(property="phone_number", type="string", example="0123456789"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="role", type="string", example="admin"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-04-15T09:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-04-15T09:00:00Z")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="2|zyxwvutsrqponmlkjihgfedcba654321")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Thông tin đăng nhập không chính xác"),
     *             @OA\Property(property="status_code", type="integer", example=401),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     )
     * )
     */
    public function storeApi(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone_number' => 'required|string',
                'password' => 'required|string',
            ]);

            if (Auth::attempt($request->only('phone_number', 'password'))) {
                $user = Auth::user();
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Đăng nhập thành công',
                    'status_code' => 200,
                    'success' => true,
                    'data' => [
                        'user' => $user,
                        'token' => $token
                    ]
                ], 200);
            }

            return response()->json([
                'message' => 'Thông tin đăng nhập không chính xác',
                'status_code' => 401,
                'success' => false,
                'data' => null
            ], 401);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => $e->errors()[array_key_first($e->errors())][0], // Get the first error message
                'status_code' => 422,
                'success' => false,
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đăng nhập thất bại',
                'status_code' => 500,
                'success' => false,
                'data' => null
            ], 500);
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
