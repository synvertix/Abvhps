<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Volunteer;
use App\Models\Membership;

class EnsureApiAccountType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $accountType  'volunteer' or 'member'
     */
    public function handle(Request $request, Closure $next, string $accountType): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($accountType === 'volunteer') {
            if (!($user instanceof Volunteer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: This endpoint requires a Volunteer account token.',
                ], 403);
            }

            // Check volunteer token ability if tokens are used
            if ($user->currentAccessToken() && !$user->tokenCan('account:volunteer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: Token lacks volunteer account permissions.',
                ], 403);
            }
        } elseif ($accountType === 'member') {
            if (!($user instanceof Membership)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: This endpoint requires a Member account token.',
                ], 403);
            }

            // Check member token ability if tokens are used
            if ($user->currentAccessToken() && !$user->tokenCan('account:member')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: Token lacks member account permissions.',
                ], 403);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Invalid account type requirement.',
            ], 403);
        }

        return $next($request);
    }
}
