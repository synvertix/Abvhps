<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Volunteer;
use App\Services\VolunteerCadreScopeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminVolunteerController extends Controller
{
    /**
     * GET /api/v1/admin/volunteers
     * Volunteer Desk Roster Matrix
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);

        $query = Volunteer::with('membership');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('cadre', 'LIKE', '%' . $search . '%')
                  ->orWhere('locality', 'LIKE', '%' . $search . '%')
                  ->orWhere('volunteer_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('volunteer_login_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('membership_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('membership', function ($mq) use ($search) {
                      $mq->where('full_name', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($v) {
            $memberName = $v->membership->identity_verified_name ?? ($v->membership->full_name ?? ($v->designation ?? 'Volunteer'));
            $memberPhoto = $v->membership->photo_path ?? null;

            return [
                'id'                 => $v->id,
                'volunteer_id'       => $v->volunteer_id ?? 'PENDING',
                'volunteer_login_id' => $v->volunteer_login_id,
                'membership_id'      => $v->membership_id,
                'name'               => $memberName,
                'email'              => $v->email,
                'phone'              => $v->phone,
                'cadre'              => $v->cadre ?? 'Volunteer',
                'cadre_level'        => $v->cadre_level ?? 'volunteer',
                'locality'           => $v->locality ?? 'HQ',
                'status'             => $v->status ?? 'pending',
                'is_active'          => (bool) $v->is_active,
                'photo_url'          => $memberPhoto ? Storage::disk('public')->url($memberPhoto) : null,
                'created_at'         => $v->created_at ? $v->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        $stats = [
            'total_records' => Volunteer::count(),
            'approved'      => Volunteer::where('status', 'approved')->count(),
            'pending'       => Volunteer::where('status', 'pending')->count(),
            'rejected'      => Volunteer::where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/volunteers/{id}
     * Full Volunteer Profile & Cadre Dossier
     */
    public function show($id)
    {
        $v = Volunteer::with('membership')->findOrFail($id);
        $memberName = $v->membership->identity_verified_name ?? ($v->membership->full_name ?? ($v->designation ?? 'Volunteer'));
        $memberPhoto = $v->membership->photo_path ?? null;

        $data = [
            'id'                   => $v->id,
            'volunteer_id'         => $v->volunteer_id ?? 'PENDING',
            'volunteer_login_id'   => $v->volunteer_login_id,
            'membership_id'        => $v->membership_id,
            'name'                 => $memberName,
            'email'                => $v->email,
            'phone'                => $v->phone,
            'cadre'                => $v->cadre ?? 'Volunteer',
            'cadre_level'          => $v->cadre_level ?? 'volunteer',
            'locality'             => $v->locality ?? 'HQ',
            'designation'          => $v->designation,
            'status'               => $v->status ?? 'pending',
            'is_active'            => (bool) $v->is_active,
            'must_change_password' => (bool) $v->must_change_password,
            'state_id'             => $v->state_id,
            'district_id'          => $v->district_id,
            'assembly_segment_id'  => $v->assembly_segment_id,
            'mandal_id'            => $v->mandal_id,
            'panchayat_id'         => $v->panchayat_id,
            'state'                => $v->state,
            'district'             => $v->district,
            'assembly_segment'     => $v->assembly_segment,
            'mandal'               => $v->mandal,
            'grama_panchayat'      => $v->grama_panchayat,
            'photo_url'            => $memberPhoto ? Storage::disk('public')->url($memberPhoto) : null,
            'created_at'           => $v->created_at ? $v->created_at->format('Y-m-d H:i:s') : null,
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * POST /api/v1/admin/volunteers/{id}/cadre
     * Process Cadre & Status Update
     */
    public function cadreUpdate(Request $request, $id)
    {
        $rawStatus = $request->input('status');
        $statusMapping = [
            'Verified' => 'approved',
            'approved' => 'approved',
            'Rejected' => 'rejected',
            'rejected' => 'rejected',
            'Pending'  => 'pending',
            'pending'  => 'pending',
        ];
        $mappedStatus = $statusMapping[$rawStatus] ?? $rawStatus;
        $request->merge(['status' => $mappedStatus]);

        $validated = $request->validate([
            'status'              => 'required|string|in:approved,rejected,pending',
            'cadre_level'         => 'nullable|string|in:national_president,state_president,district_president,assembly_president,mandal_president,panchayat_president,volunteer',
            'cadre'               => 'nullable|string|max:255',
            'locality'            => 'nullable|string|max:255',
            'state_id'            => 'nullable|integer|exists:geo_states,id',
            'district_id'         => 'nullable|integer|exists:geo_districts,id',
            'assembly_segment_id' => 'nullable|integer|exists:geo_assembly_segments,id',
            'mandal_id'           => 'nullable|integer|exists:geo_mandals,id',
            'panchayat_id'        => 'nullable|integer|exists:geo_panchayats,id',
        ]);

        $volunteer = Volunteer::findOrFail($id);
        $cadreLevel = $validated['cadre_level'] ?? 'volunteer';
        $stateId = $request->input('state_id') ? (int) $request->input('state_id') : null;
        $districtId = $request->input('district_id') ? (int) $request->input('district_id') : null;
        $assemblyId = $request->input('assembly_segment_id') ? (int) $request->input('assembly_segment_id') : null;
        $mandalId = $request->input('mandal_id') ? (int) $request->input('mandal_id') : null;
        $panchayatId = $request->input('panchayat_id') ? (int) $request->input('panchayat_id') : null;

        if ($mappedStatus === 'approved') {
            $hierarchyError = VolunteerCadreScopeService::validateParentChildGeography($stateId, $districtId, $assemblyId, $mandalId, $panchayatId);
            if ($hierarchyError) {
                return response()->json(['success' => false, 'message' => $hierarchyError], 422);
            }
            $dupError = VolunteerCadreScopeService::checkDuplicateActivePresident($cadreLevel, $stateId, $districtId, $assemblyId, $mandalId, $panchayatId, $volunteer->id);
            if ($dupError) {
                return response()->json(['success' => false, 'message' => $dupError], 422);
            }
        }

        $cadreTitle = $validated['cadre'] ?? Volunteer::cadreLevelToPublicTitle($cadreLevel);
        $locality = $validated['locality'] ?? ($volunteer->jurisdiction_summary ?? 'HQ');

        if ($mappedStatus === 'approved') {
            $isFirstTime = empty($volunteer->volunteer_id) || empty($volunteer->volunteer_login_id);
            if ($isFirstTime) {
                $vId = \App\Http\Controllers\VolunteerController::generateNextVolunteerId();
                $plainPass = \Illuminate\Support\Str::password(10, true, true, false, false);
                $volunteer->volunteer_id = $vId;
                $volunteer->volunteer_login_id = $vId;
                $volunteer->password = \Illuminate\Support\Facades\Hash::make($plainPass);
                $volunteer->must_change_password = true;
                $volunteer->credentials_created_at = now();
            }
            $volunteer->status = 'approved';
            $volunteer->is_active = true;
        } elseif ($mappedStatus === 'rejected') {
            $volunteer->status = 'rejected';
            $volunteer->is_active = false;
        } else {
            $volunteer->status = 'pending';
        }

        $volunteer->cadre = $cadreTitle;
        $volunteer->cadre_level = $cadreLevel;
        $volunteer->locality = $locality;
        $volunteer->designation = $cadreTitle;
        $volunteer->state_id = $stateId;
        $volunteer->district_id = $districtId;
        $volunteer->assembly_segment_id = $assemblyId;
        $volunteer->mandal_id = $mandalId;
        $volunteer->panchayat_id = $panchayatId;
        $volunteer->save();

        return response()->json([
            'success' => true,
            'message' => 'Volunteer cadre and status updated successfully.',
            'data'    => $volunteer,
        ]);
    }

    /**
     * DELETE /api/v1/admin/volunteers/{id}
     * Delete volunteer record
     */
    public function destroy($id)
    {
        $volunteer = Volunteer::findOrFail($id);
        $volunteer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Volunteer record deleted successfully.',
        ]);
    }
}
