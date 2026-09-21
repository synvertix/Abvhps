<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Services\AuditLogger;

class AdminAuthController extends Controller
{
    /**
     * Authenticate an authorized administrator and issue a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email|max:255',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:100',
        ]);

        $emailInput = Str::lower(trim($request->input('email')));
        $password = $request->input('password');
        $deviceName = trim($request->input('device_name'));

        $throttleKey = 'api_admin_login:' . $emailInput . '|' . $request->ip();

        // Rate limit: 5 attempts per 60 seconds
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditLogger::log('API_ADMIN_LOGIN_THROTTLED', 'User', $emailInput, [
                'cooldown_seconds' => $seconds,
                'device_name'      => $deviceName,
            ], 'Anonymous', $emailInput);

            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        // Lookup administrator by email
        $user = User::where('email', $emailInput)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            AuditLogger::log('API_ADMIN_LOGIN_FAILED', 'User', $emailInput, [
                'reason'      => 'INVALID_CREDENTIALS',
                'device_name' => $deviceName,
            ], 'Anonymous', $emailInput);

            return response()->json([
                'success' => false,
                'message' => 'Invalid administrator email or security credentials.',
            ], 422);
        }

        RateLimiter::clear($throttleKey);

        // Explicit admin mobile token abilities
        $abilities = [
            'mobile',
            'account:admin',
            'admin:profile',
            'admin:dashboard',
        ];

        $tokenResult = $user->createToken($deviceName, $abilities);

        AuditLogger::log('API_ADMIN_LOGIN_SUCCESS', 'User', (string) $user->id, [
            'device_name' => $deviceName,
            'email'       => $user->email,
        ], 'Admin', $user->email, $user->id);

        return response()->json([
            'success' => true,
            'data'    => [
                'account_type' => 'admin',
                'token'        => $tokenResult->plainTextToken,
                'profile'      => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ],
            'message' => 'Administrator authenticated successfully.',
        ]);
    }
}
