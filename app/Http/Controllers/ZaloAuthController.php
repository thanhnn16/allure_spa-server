<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ZaloAuthController extends Controller
{

    public function index(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');

        if ($code && $state) {
            return redirect("allurespa://zalo-oauth?code={$code}&state={$state}");
        }

        return Inertia::render('Payments/ProgressView');
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
}
