<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Http\Resources\Api\V1\VolunteerProfileResource;
use App\Http\Resources\Api\V1\MemberProfileResource;
use App\Services\VolunteerCadreScopeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AuditLogger;

class MeController extends Controller
{
    /**
     * Return authenticated account information and capabilities.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user instanceof Volunteer) {
            $isPresident = VolunteerCadreScopeService::isVerifiedCadre($user) && $user->cadre_level !== 'volunteer';

            $capabilities = [
                'can_view_profile'         => true,
                'can_change_password'      => true,
                'can_view_events'          => true,
                'can_manage_hierarchy'     => $isPresident,
                'cadre_level'              => $user->cadre_level,
                'is_president'             => $isPresident,
                'must_change_password'     => (bool) $user->must_change_password,
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'account_type'          => 'volunteer',
                    'must_change_password'  => (bool) $user->must_change_password,
                    'profile'               => new VolunteerProfileResource($user),
                    'capabilities'          => $capabilities,
                ],
                'message' => null,
            ]);
        }

        if ($user instanceof Membership) {
            $capabilities = [
                'can_view_profile'       => true,
                'can_view_card'          => true,
                'is_completed'           => (bool) $user->is_completed,
                'is_identity_verified'   => $user->hasVerifiedIdentity(),
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'account_type' => 'member',
                    'profile'      => new MemberProfileResource($user),
                    'capabilities' => $capabilities,
                ],
                'message' => null,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unrecognized principal type.',
        ], 403);
    }

    /**
     * Revoke ONLY the current device's access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $request->user()->currentAccessToken()) {
            $identifier = $user instanceof Volunteer ? $user->volunteer_id : ($user->membership_id ?? $user->id);
            AuditLogger::log('API_TOKEN_REVOKED', get_class($user), $identifier, [
                'token_name' => $request->user()->currentAccessToken()->name,
            ], get_class($user), $identifier, $user->id);

            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully from this device.',
        ]);
    }

    /**
     * Revoke ALL mobile tokens for the authenticated account across all devices.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $identifier = $user instanceof Volunteer ? $user->volunteer_id : ($user->membership_id ?? $user->id);
            AuditLogger::log('API_ALL_TOKENS_REVOKED', get_class($user), $identifier, [], get_class($user), $identifier, $user->id);

            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully from all devices.',
        ]);
    }
}
