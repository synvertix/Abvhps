<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamDirectoryController extends Controller
{
    /**
     * Public Our Team Leadership Directory API.
     * Reuses the exact query, cascading filters, and scopes from HomeController@team.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $selectedCadre    = $request->query('cadre');
        $selectedCountry  = $request->query('country');
        $selectedState    = $request->query('state');
        $selectedDistrict = $request->query('district');
        $selectedAssembly = $request->query('assembly_segment') ?: $request->query('taluk');
        $selectedMandal   = $request->query('mandal');
        $selectedPanchayat= $request->query('panchayat') ?: $request->query('grama_panchayat');
        $searchQuery      = $request->query('search');

        $perPage = (int) $request->query('per_page', 20);
        if ($perPage < 1 || $perPage > 50) {
            $perPage = 20;
        }

        // Base approved pool for cascading options
        $approvedBase = Volunteer::with('membership')->approved()->get();

        $cadres = $approvedBase->map(fn($v) => $v->cadre_label)->filter()->unique()->values();
        $countries = $approvedBase->map(fn($v) => $v->resolved_country)->filter()->unique()->values();

        $states = $approvedBase->filter(function ($v) use ($selectedCountry) {
            return empty($selectedCountry) || strcasecmp($v->resolved_country, $selectedCountry) === 0;
        })->map(fn($v) => $v->resolved_state)->filter()->unique()->values();

        $districts = $approvedBase->filter(function ($v) use ($selectedCountry, $selectedState) {
            $matchCountry = empty($selectedCountry) || strcasecmp($v->resolved_country, $selectedCountry) === 0;
            $matchState   = empty($selectedState)   || strcasecmp($v->resolved_state, $selectedState) === 0;
            return $matchCountry && $matchState;
        })->map(fn($v) => $v->resolved_district)->filter()->unique()->values();

        $assemblies = $approvedBase->filter(function ($v) use ($selectedCountry, $selectedState, $selectedDistrict) {
            $matchCountry  = empty($selectedCountry)  || strcasecmp($v->resolved_country, $selectedCountry) === 0;
            $matchState    = empty($selectedState)    || strcasecmp($v->resolved_state, $selectedState) === 0;
            $matchDistrict = empty($selectedDistrict) || strcasecmp($v->resolved_district, $selectedDistrict) === 0;
            return $matchCountry && $matchState && $matchDistrict;
        })->map(fn($v) => $v->resolved_assembly_segment)->filter()->unique()->values();

        $mandals = $approvedBase->filter(function ($v) use ($selectedCountry, $selectedState, $selectedDistrict, $selectedAssembly) {
            $matchCountry  = empty($selectedCountry)  || strcasecmp($v->resolved_country, $selectedCountry) === 0;
            $matchState    = empty($selectedState)    || strcasecmp($v->resolved_state, $selectedState) === 0;
            $matchDistrict = empty($selectedDistrict) || strcasecmp($v->resolved_district, $selectedDistrict) === 0;
            $matchAssembly = empty($selectedAssembly) || strcasecmp($v->resolved_assembly_segment, $selectedAssembly) === 0;
            return $matchCountry && $matchState && $matchDistrict && $matchAssembly;
        })->map(fn($v) => $v->resolved_mandal)->filter()->unique()->values();

        $panchayats = $approvedBase->filter(function ($v) use ($selectedCountry, $selectedState, $selectedDistrict, $selectedAssembly, $selectedMandal) {
            $matchCountry  = empty($selectedCountry)  || strcasecmp($v->resolved_country, $selectedCountry) === 0;
            $matchState    = empty($selectedState)    || strcasecmp($v->resolved_state, $selectedState) === 0;
            $matchDistrict = empty($selectedDistrict) || strcasecmp($v->resolved_district, $selectedDistrict) === 0;
            $matchAssembly = empty($selectedAssembly) || strcasecmp($v->resolved_assembly_segment, $selectedAssembly) === 0;
            $matchMandal   = empty($selectedMandal)   || strcasecmp($v->resolved_mandal, $selectedMandal) === 0;
            return $matchCountry && $matchState && $matchDistrict && $matchAssembly && $matchMandal;
        })->map(fn($v) => $v->resolved_grama_panchayat)->filter()->unique()->values();

        // Paginated query
        $volunteersQuery = Volunteer::with('membership')->approved();

        if (!empty($selectedCadre)) {
            $volunteersQuery->where(function ($q) use ($selectedCadre) {
                $q->where('cadre', $selectedCadre)
                  ->orWhere('designation', $selectedCadre);
            });
        }

        if (!empty($selectedCountry)) {
            $volunteersQuery->where(function ($q) use ($selectedCountry) {
                $q->where('country', $selectedCountry)
                  ->orWhereHas('membership', fn($mq) => $mq->where('country', $selectedCountry));
            });
        }

        if (!empty($selectedState)) {
            $volunteersQuery->where(function ($q) use ($selectedState) {
                $q->where('state', $selectedState)
                  ->orWhereHas('membership', fn($mq) => $mq->where('state', $selectedState));
            });
        }

        if (!empty($selectedDistrict)) {
            $volunteersQuery->where(function ($q) use ($selectedDistrict) {
                $q->where('district', $selectedDistrict)
                  ->orWhereHas('membership', fn($mq) => $mq->where('district', $selectedDistrict));
            });
        }

        if (!empty($selectedAssembly)) {
            $volunteersQuery->where(function ($q) use ($selectedAssembly) {
                $q->where('assembly_segment', $selectedAssembly)
                  ->orWhereHas('membership', fn($mq) => $mq->where('assembly_segment', $selectedAssembly));
            });
        }

        if (!empty($selectedMandal)) {
            $volunteersQuery->where(function ($q) use ($selectedMandal) {
                $q->where('mandal', $selectedMandal)
                  ->orWhereHas('membership', fn($mq) => $mq->where('mandal', $selectedMandal));
            });
        }

        if (!empty($selectedPanchayat)) {
            $volunteersQuery->where(function ($q) use ($selectedPanchayat) {
                $q->where('grama_panchayat', $selectedPanchayat)
                  ->orWhereHas('membership', fn($mq) => $mq->where('grama_panchayat', $selectedPanchayat));
            });
        }

        if (!empty($searchQuery)) {
            $volunteersQuery->where(function ($q) use ($searchQuery) {
                $q->where('volunteer_id', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('volunteer_login_id', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('cadre', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('designation', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('locality', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('district', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('mandal', 'LIKE', "%{$searchQuery}%")
                  ->orWhereHas('membership', function ($mq) use ($searchQuery) {
                      $mq->where('full_name', 'LIKE', "%{$searchQuery}%")
                         ->orWhere('district', 'LIKE', "%{$searchQuery}%")
                         ->orWhere('mandal', 'LIKE', "%{$searchQuery}%")
                         ->orWhere('grama_panchayat', 'LIKE', "%{$searchQuery}%");
                  });
            });
        }

        $paginated = $volunteersQuery->orderBy('id', 'asc')->paginate($perPage);

        $items = collect($paginated->items())->map(function ($v) {
            return [
                'id'                   => $v->id,
                'volunteer_id'         => $v->volunteer_id ?? $v->volunteer_login_id,
                'name'                 => $v->full_name,
                'cadre_label'          => $v->cadre_label,
                'jurisdiction_summary' => $v->jurisdiction_summary,
                'country'              => $v->resolved_country,
                'state'                => $v->resolved_state,
                'district'             => $v->resolved_district,
                'assembly_segment'     => $v->resolved_assembly_segment,
                'mandal'               => $v->resolved_mandal,
                'grama_panchayat'      => $v->resolved_grama_panchayat,
                'photo_url'            => ApiMediaHelper::resolveUrl($v->photo_path),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
            'filters' => [
                'cadres'      => $cadres,
                'countries'   => $countries,
                'states'      => $states,
                'districts'   => $districts,
                'assemblies'  => $assemblies,
                'mandals'     => $mandals,
                'panchayats'  => $panchayats,
            ],
            'message' => null,
        ]);
    }
}
