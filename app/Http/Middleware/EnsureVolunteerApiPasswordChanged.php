<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Volunteer;

class EnsureVolunteerApiPasswordChanged
{
    /**
     * Handle an incoming request.
     * Blocks access to general volunteer endpoints if must_change_password is true.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof Volunteer) {
            $freshVolunteer = Volunteer::find($user->id);

            if ($freshVolunteer && (bool)$freshVolunteer->must_change_password) {
                return response()->json([
                    'success' => false,
                    'must_change_password' => true,
                    'message' => 'Password change required before accessing this resource.',
                ], 403);
            }
        }

        return $next($request);
    }
}
