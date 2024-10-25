<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): Response
    {
        return Inertia::render('Auth/ConfirmPassword');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::guard('web')->validate([
            'phone_number' => $request->user()->phone_number,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Confirm the user's password from API.
     */

    public function storeApi(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|max:15',
            'password' => ['required'],
        ]);

        if (!Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])) {
            return response()->json([
                'message' => 'Login failed',
                'status_code' => 401,
                'errors' => ['phone_number' => ['The provided credentials are incorrect.']]
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'status_code' => 200,
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }
}
