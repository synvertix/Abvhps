<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Volunteer;

class EnsureVolunteerApiEligible
{
    /**
     * Handle an incoming request.
     * Enforces active & approved status on every protected volunteer API request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !($user instanceof Volunteer)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated or invalid volunteer token.',
            ], 401);
        }

        // Fresh DB check to ensure real-time state authority
        $freshVolunteer = Volunteer::find($user->id);

        if (!$freshVolunteer || $freshVolunteer->status !== 'approved' || ($freshVolunteer->is_active !== true && $freshVolunteer->is_active !== 1 && $freshVolunteer->is_active !== '1')) {
            return response()->json([
                'success' => false,
                'message' => 'Your volunteer account is not active or has not been approved.',
            ], 403);
        }

        return $next($request);
    }
}
