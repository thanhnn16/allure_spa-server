<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Services\AuthService;
use Illuminate\Support\Facades\Log;

class ZaloAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function index(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');

        if ($code && $state) {
            // Thử redirect về app
            try {
                return redirect("allurespa://zalo-oauth?code={$code}&state={$state}");
            } catch (\Exception $e) {
                // Nếu không redirect được, render view với thông tin
                return Inertia::render('ZaloAuth/ProgressView', [
                    'code' => $code,
                    'state' => $state
                ]);
            }
        }

        return Inertia::render('ZaloAuth/ProgressView', [
            'error' => 'Không tìm thấy thông tin xác thực'
        ]);
    }

    // Thêm method để tạo code verifier
    public function generateCodeVerifier()
    {
        // Generate random string 43 chars long with letters and numbers
        $codeVerifier = Str::random(43);
        session(['code_verifier' => $codeVerifier]);

        // Generate code challenge
        $codeChallenge = base64_encode(hash('sha256', $codeVerifier, true));

        return response()->json([
            'code_challenge' => $codeChallenge
        ]);
    }

    public function callback(Request $request)
    {
        try {
            $code = $request->code;
            $codeVerifier = session('code_verifier'); // Get stored code verifier

            // Exchange code for tokens
            $response = Http::post('https://oauth.zaloapp.com/v4/access_token', [
                'code' => $code,
                'app_id' => config('services.zalo.client_id'),
                'grant_type' => 'authorization_code',
                'code_verifier' => $codeVerifier
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Không thể lấy access token'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleZaloCallback(Request $request)
    {
        try {
            $validated = $request->validate([
                'access_token' => 'required|string',
                'refresh_token' => 'required|string',
                'expires_in' => 'required|integer',
                'refresh_token_expires_in' => 'required|integer'
            ]);

            $result = $this->authService->loginWithZalo($validated);

            return response()->json([
                'user' => $result['user'],
                'token' => $result['token']
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xác thực Zalo: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getZaloUserInfo($accessToken)
    {
        try {
            $response = Http::get('https://graph.zalo.me/v2.0/me', [
                'access_token' => $accessToken,
                'fields' => 'id,name,picture,birthday,gender'
            ]);

            if (!$response->successful()) {
                throw new \Exception('Không thể kết nối với Zalo API');
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Zalo API error: ' . $e->getMessage());
            throw new \Exception('Lỗi khi lấy thông tin người dùng Zalo');
        }
    }
}
