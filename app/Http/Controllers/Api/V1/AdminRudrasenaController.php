<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\RudrasenaEligibilityService;

class AdminRudrasenaController extends Controller
{
    /**
     * GET /api/v1/admin/rudrasena
     * Rudrasena Member Roster Matrix
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);

        $query = DB::table('rudrasena_members')
            ->leftJoin('memberships', 'rudrasena_members.membership_id', '=', 'memberships.membership_id')
            ->select(
                'rudrasena_members.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.grama_panchayat as member_grama_panchayat',
                'memberships.state as member_state'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('rudrasena_members.full_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('memberships.full_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('rudrasena_members.membership_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('rudrasena_members.rudrasena_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('rudrasena_members.mobile', 'LIKE', '%' . $search . '%')
                  ->orWhere('rudrasena_members.volunteer_type', 'LIKE', '%' . $search . '%')
                  ->orWhere('rudrasena_members.assigned_cadder', 'LIKE', '%' . $search . '%')
                  ->orWhere('rudrasena_members.assigned_locality', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($status)) {
            $query->where('rudrasena_members.status', $status);
        }

        $paginator = $query->orderBy('rudrasena_members.created_at', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($r) {
            $name = $r->member_full_name ?: $r->full_name;
            $photo = $r->member_photo_path;

            return [
                'id'                => $r->id,
                'rudrasena_id'      => $r->rudrasena_id ?? 'PENDING',
                'membership_id'     => $r->membership_id,
                'name'              => $name,
                'mobile'            => $r->mobile,
                'email'             => $r->email,
                'volunteer_type'    => $r->volunteer_type ?? 'General Volunteer',
                'assigned_cadder'   => $r->assigned_cadder ?? 'Dal Member',
                'assigned_locality' => $r->assigned_locality ?? 'HQ',
                'status'            => $r->status ?? 'pending',
                'dob'               => $r->dob,
                'age'               => $r->dob ? RudrasenaEligibilityService::calculateAge($r->dob) : null,
                'is_age_eligible'   => $r->dob ? RudrasenaEligibilityService::isAgeEligible($r->dob) : false,
                'photo_url'         => $photo ? Storage::disk('public')->url($photo) : null,
                'created_at'        => $r->created_at,
            ];
        });

        $stats = [
            'total_records' => DB::table('rudrasena_members')->count(),
            'verified'      => DB::table('rudrasena_members')->where('status', 'verified')->count(),
            'pending'       => DB::table('rudrasena_members')->where('status', 'pending')->count(),
            'rejected'      => DB::table('rudrasena_members')->where('status', 'rejected')->count(),
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
     * GET /api/v1/admin/rudrasena/{id}
     * Full Rudrasena Member Dossier
     */
    public function show($id)
    {
        $r = DB::table('rudrasena_members')
            ->leftJoin('memberships', 'rudrasena_members.membership_id', '=', 'memberships.membership_id')
            ->select(
                'rudrasena_members.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.blood_group as member_blood_group',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.grama_panchayat as member_grama_panchayat',
                'memberships.state as member_state',
                'memberships.pincode as member_pincode'
            )
            ->where('rudrasena_members.id', $id)
            ->first();

        if (!$r) {
            return response()->json(['success' => false, 'message' => 'Rudrasena member not found'], 404);
        }

        $familyDetails = DB::table('rudrasena_family_details')
            ->where('rudrasena_member_id', $id)
            ->get();

        $name = $r->member_full_name ?: $r->full_name;
        $photo = $r->member_photo_path;
        $age = $r->dob ? RudrasenaEligibilityService::calculateAge($r->dob) : null;
        $isEligible = $r->dob ? RudrasenaEligibilityService::isAgeEligible($r->dob) : false;

        $data = [
            'id'                => $r->id,
            'rudrasena_id'      => $r->rudrasena_id ?? 'PENDING',
            'membership_id'     => $r->membership_id,
            'name'              => $name,
            'mobile'            => $r->mobile,
            'email'             => $r->email,
            'volunteer_type'    => $r->volunteer_type ?? 'General Volunteer',
            'assigned_cadder'   => $r->assigned_cadder ?? 'Dal Member',
            'assigned_locality' => $r->assigned_locality ?? 'HQ',
            'status'            => $r->status ?? 'pending',
            'dob'               => $r->dob,
            'age'               => $age,
            'is_age_eligible'   => $isEligible,
            'blood_group'       => $r->blood_group ?: ($r->member_blood_group ?? 'N/A'),
            'district'          => $r->member_district,
            'mandal'            => $r->member_mandal,
            'grama_panchayat'   => $r->member_grama_panchayat,
            'state'             => $r->member_state,
            'pincode'           => $r->member_pincode,
            'photo_url'         => $photo ? Storage::disk('public')->url($photo) : null,
            'family_details'    => $familyDetails,
            'created_at'        => $r->created_at,
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * POST /api/v1/admin/rudrasena/{id}/status
     * Process Cadre & Status Update with 24-44 Age Invariant Enforcement
     */
    public function updateStatus(Request $request, $id)
    {
        $rawStatus = $request->input('status');
        $statusMapping = [
            'Verified' => 'verified',
            'verified' => 'verified',
            'approved' => 'verified',
            'Rejected' => 'rejected',
            'rejected' => 'rejected',
            'Pending'  => 'pending',
            'pending'  => 'pending',
        ];
        $mappedStatus = $statusMapping[$rawStatus] ?? $rawStatus;

        $validated = $request->validate([
            'status'            => 'required|string|in:verified,rejected,pending',
            'assigned_cadder'   => 'required|string|max:255',
            'assigned_locality' => 'required|string|max:255',
        ]);

        $member = DB::table('rudrasena_members')->where('id', $id)->first();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Rudrasena member not found'], 404);
        }

        if ($mappedStatus === 'verified') {
            if (empty($member->dob)) {
                return response()->json(['success' => false, 'message' => 'Cannot approve member: Date of Birth is missing.'], 422);
            }

            if (!RudrasenaEligibilityService::isAgeEligible($member->dob)) {
                $age = RudrasenaEligibilityService::calculateAge($member->dob);
                $msg = RudrasenaEligibilityService::validationMessage($age);
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
        }

        $assignedRsId = $member->rudrasena_id;
        if ($mappedStatus === 'verified' && empty($assignedRsId)) {
            // Generate sequential RS ID code (e.g. RS0001, RS0002)
            $lastMember = DB::table('rudrasena_members')
                ->whereNotNull('rudrasena_id')
                ->where('rudrasena_id', 'LIKE', 'RS%')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastMember && preg_match('/RS(\d+)/i', $lastMember->rudrasena_id, $matches)) {
                $nextNum = ((int) $matches[1]) + 1;
            } else {
                $nextNum = 1;
            }
            $assignedRsId = 'RS' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        DB::table('rudrasena_members')->where('id', $id)->update([
            'status'            => $mappedStatus,
            'assigned_cadder'   => $validated['assigned_cadder'],
            'assigned_locality' => $validated['assigned_locality'],
            'rudrasena_id'      => $assignedRsId,
            'updated_at'        => now(),
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Rudrasena status and cadre updated successfully.',
            'rudrasena_id' => $assignedRsId,
        ]);
    }

    /**
     * DELETE /api/v1/admin/rudrasena/{id}
     * Delete Rudrasena Member
     */
    public function destroy($id)
    {
        DB::table('rudrasena_family_details')->where('rudrasena_member_id', $id)->delete();
        DB::table('rudrasena_members')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rudrasena member record deleted successfully.',
        ]);
    }
}
