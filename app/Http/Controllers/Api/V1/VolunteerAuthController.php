<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Http\Resources\Api\V1\VolunteerProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\AuditLogger;

class VolunteerAuthController extends Controller
{
    /**
     * Authenticate an approved, active volunteer and issue a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login_id'    => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:100',
        ]);

        $loginInput = trim($request->input('login_id'));
        $password = $request->input('password');
        $deviceName = trim($request->input('device_name'));

        $throttleKey = 'api_volunteer_login:' . $loginInput . '|' . $request->ip();

        // Rate limit: 5 attempts per minute
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditLogger::log('API_VOLUNTEER_LOGIN_THROTTLED', 'Volunteer', $loginInput, [
                'cooldown_seconds' => $seconds,
                'device_name'      => $deviceName,
            ], 'Anonymous', $loginInput);

            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        // Lookup volunteer by 6-digit volunteer_login_id OR volunteer_id
        $volunteer = Volunteer::where('volunteer_login_id', $loginInput)
            ->orWhere('volunteer_id', $loginInput)
            ->first();

        if (!$volunteer) {
            RateLimiter::hit($throttleKey, 60);
            AuditLogger::log('API_VOLUNTEER_LOGIN_FAILED', 'Volunteer', $loginInput, [
                'reason'      => 'ID_NOT_FOUND',
                'device_name' => $deviceName,
            ], 'Anonymous', $loginInput);

            return response()->json([
                'success' => false,
                'message' => 'Invalid Volunteer ID or credentials.',
            ], 422);
        }

        // Strict verification: only APPROVED & ACTIVE volunteers can receive mobile access
        if ($volunteer->status !== 'approved' || ($volunteer->is_active !== true && $volunteer->is_active !== 1 && $volunteer->is_active !== '1')) {
            RateLimiter::hit($throttleKey, 60);

            $reason = match ($volunteer->status) {
                'pending'  => 'Your volunteer application is currently pending review and has not yet been approved.',
                'rejected' => 'Your volunteer application was not approved. Please contact administration.',
                default    => 'Your volunteer account is currently inactive. Please contact administration.',
            };

            AuditLogger::log('API_VOLUNTEER_LOGIN_BLOCKED', 'Volunteer', $volunteer->volunteer_id, [
                'status'    => $volunteer->status,
                'is_active' => $volunteer->is_active,
            ], 'Volunteer', $volunteer->volunteer_id, $volunteer->id);

            return response()->json([
                'success' => false,
                'message' => $reason,
            ], 403);
        }

        // Verify password
        if (!Hash::check($password, $volunteer->password)) {
            RateLimiter::hit($throttleKey, 60);
            AuditLogger::log('API_VOLUNTEER_LOGIN_FAILED', 'Volunteer', $volunteer->volunteer_id, [
                'reason'      => 'PASSWORD_MISMATCH',
                'device_name' => $deviceName,
            ], 'Anonymous', $loginInput);

            return response()->json([
                'success' => false,
                'message' => 'The provided password is incorrect.',
            ], 422);
        }

        RateLimiter::clear($throttleKey);

        // Define token abilities based on must_change_password
        $mustChangePassword = (bool) $volunteer->must_change_password;

        if ($mustChangePassword) {
            // Restricted ability: strictly allows password change and me endpoint
            $abilities = [
                'mobile',
                'account:volunteer',
                'volunteer:change-password',
            ];
        } else {
            // Full mobile volunteer abilities
            $abilities = [
                'mobile',
                'account:volunteer',
                'volunteer:profile',
                'volunteer:dashboard',
            ];
        }

        $tokenResult = $volunteer->createToken($deviceName, $abilities);

        AuditLogger::log('API_VOLUNTEER_LOGIN_SUCCESS', 'Volunteer', $volunteer->volunteer_id, [
            'device_name'          => $deviceName,
            'must_change_password' => $mustChangePassword,
        ], 'Volunteer', $volunteer->volunteer_id, $volunteer->id);

        return response()->json([
            'success' => true,
            'data'    => [
                'account_type'          => 'volunteer',
                'token'                 => $tokenResult->plainTextToken,
                'must_change_password'  => $mustChangePassword,
                'profile'               => new VolunteerProfileResource($volunteer),
            ],
            'message' => 'Authenticated successfully.',
        ]);
    }

    /**
     * Change Volunteer Password via API.
     * When successful, clears must_change_password, revokes restricted token, and issues full volunteer token.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $volunteer = $request->user();

        if (!$volunteer || !($volunteer instanceof Volunteer)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $request->validate([
            'current_password'      => 'required|string',
            'new_password'          => 'required|string|min:8|confirmed|different:current_password',
            'device_name'           => 'nullable|string|max:100',
        ]);

        if (!Hash::check($request->current_password, $volunteer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Your current temporary password is incorrect.',
            ], 422);
        }

        // Update password
        $volunteer->password = Hash::make($request->new_password);
        $volunteer->must_change_password = false;
        $volunteer->save();

        AuditLogger::log('API_VOLUNTEER_PASSWORD_CHANGED', 'Volunteer', $volunteer->volunteer_id, [
            'changed_by' => 'Self_API'
        ], 'Volunteer', $volunteer->volunteer_id, $volunteer->id);

        // Revoke current restricted token and issue full token
        $currentToken = $request->user()->currentAccessToken();
        $deviceName = $request->input('device_name') ?: ($currentToken->name ?? 'Mobile Device');
        
        if ($currentToken) {
            $currentToken->delete();
        }

        $newToken = $volunteer->createToken($deviceName, [
            'mobile',
            'account:volunteer',
            'volunteer:profile',
            'volunteer:dashboard',
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'account_type'         => 'volunteer',
                'token'                => $newToken->plainTextToken,
                'must_change_password' => false,
                'profile'              => new VolunteerProfileResource($volunteer),
            ],
            'message' => 'Your password has been changed successfully. Full dashboard access is now enabled.',
        ]);
    }
}
