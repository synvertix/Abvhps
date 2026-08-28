<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Http\Resources\Api\V1\VolunteerProfileResource;
use App\Services\VolunteerCadreScopeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VolunteerProfileController extends Controller
{
    /**
     * Get authenticated Volunteer profile.
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var Volunteer $volunteer */
        $volunteer = $request->user();

        return response()->json([
            'success' => true,
            'data'    => new VolunteerProfileResource($volunteer),
            'message' => null,
        ]);
    }

    /**
     * Get authenticated Volunteer dashboard metrics and scoped jurisdiction statistics.
     */
    public function dashboard(Request $request): JsonResponse
    {
        /** @var Volunteer $volunteer */
        $volunteer = $request->user();

        $isPresident = VolunteerCadreScopeService::isVerifiedCadre($volunteer) && $volunteer->cadre_level !== 'volunteer';
        $subordinateUnits = $isPresident ? VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        // Own event stats
        $stats = [
            'events_conducted'    => $volunteer->conductedEventsCount(),
            'upcoming_events'     => $volunteer->upcomingEventsCount(),
            'total_events'        => $volunteer->totalEventsCount(),
            'total_participants'  => $volunteer->totalParticipantsCount(),
            'total_beneficiaries' => $volunteer->totalBeneficiariesCount(),
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'profile'             => new VolunteerProfileResource($volunteer),
                'is_president'        => $isPresident,
                'cadre_level'         => $volunteer->cadre_level,
                'cadre_label'         => $volunteer->cadre_label,
                'jurisdiction_summary'=> $volunteer->jurisdiction_summary,
                'stats'               => $stats,
                'subordinate_units'   => $subordinateUnits->values(),
            ],
            'message' => null,
        ]);
    }
}
