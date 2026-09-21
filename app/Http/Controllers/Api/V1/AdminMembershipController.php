<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Membership;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminMembershipController extends Controller
{
    /**
     * GET /api/v1/admin/memberships
     * Approved Lifetime Membership Ledger Matrix
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $query = Membership::where('is_completed', true)
            ->where('payment_status', 'success');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('membership_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone', 'LIKE', '%' . $search . '%')
                  ->orWhere('district', 'LIKE', '%' . $search . '%');
            });
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($m) {
            return [
                'id'                      => $m->id,
                'membership_id'           => $m->membership_id,
                'full_name'               => $m->identity_verified_name ?? $m->full_name,
                'phone'                   => $m->phone,
                'email'                   => $m->email,
                'district'                => $m->district,
                'mandal'                  => $m->mandal,
                'grama_panchayat'         => $m->grama_panchayat,
                'state'                   => $m->state,
                'blood_group'             => $m->blood_group,
                'photo_url'               => $m->photo_path ? Storage::disk('public')->url($m->photo_path) : null,
                'is_completed'            => (bool) $m->is_completed,
                'identity_verified'       => (bool) $m->identity_verified,
                'identity_verified_type'  => $m->identity_verified_type,
                'created_at'              => $m->created_at ? $m->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'success' => true,
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
     * GET /api/v1/admin/memberships/pending
     * Pending Incomplete Applications (Paid Fee but Incomplete Profile)
     */
    public function pending(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $query = Membership::where('payment_status', 'success')
            ->where(function ($q) {
                $q->where('is_completed', false)
                  ->orWhere('is_completed', 0)
                  ->orWhereNull('is_completed');
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('membership_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone', 'LIKE', '%' . $search . '%')
                  ->orWhere('payment_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('district', 'LIKE', '%' . $search . '%');
            });
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($m) {
            return [
                'id'                 => $m->id,
                'membership_id'      => $m->membership_id ?? 'PENDING',
                'phone'              => $m->phone,
                'payment_id'         => $m->payment_id,
                'amount'             => 100.0,
                'fee_status'         => 'PAID ₹100',
                'payment_status'     => $m->payment_status,
                'paid_date'          => $m->payment_completed_at ? $m->payment_completed_at->format('Y-m-d H:i:s') : ($m->updated_at ? $m->updated_at->format('Y-m-d H:i:s') : null),
                'identity_verified'  => (bool) $m->identity_verified,
                'created_at'         => $m->created_at ? $m->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'success' => true,
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
     * GET /api/v1/admin/memberships/{id}
     * Full member detail dossier
     */
    public function show($id)
    {
        $m = Membership::findOrFail($id);

        $data = [
            'id'                     => $m->id,
            'membership_id'          => $m->membership_id,
            'formatted_id'           => $m->membership_id ? implode(' ', str_split($m->membership_id, 4)) : 'PENDING',
            'full_name'              => $m->full_name,
            'identity_verified_name' => $m->identity_verified_name,
            'gender'                 => $m->gender,
            'dob'                    => $m->dob,
            'phone'                  => $m->phone,
            'email'                  => $m->email,
            'father_or_husband_name' => $m->father_or_husband_name,
            'gotram'                 => $m->gotram,
            'occupation'             => $m->occupation,
            'blood_group'            => $m->blood_group,
            'pincode'                => $m->pincode,
            'grama_panchayat'        => $m->grama_panchayat,
            'mandal'                 => $m->mandal,
            'assembly_segment'       => $m->assembly_segment,
            'district'               => $m->district,
            'state'                  => $m->state,
            'country'                => $m->country ?? 'India',
            'permanent_address'      => $m->permanent_address,
            'present_address'        => $m->present_address,
            'photo_url'              => $m->photo_path ? Storage::disk('public')->url($m->photo_path) : null,
            'is_completed'           => (bool) $m->is_completed,
            'payment_status'         => $m->payment_status,
            'payment_id'             => $m->payment_id,
            'identity_verified'      => (bool) $m->identity_verified,
            'identity_verified_type' => $m->identity_verified_type,
            'masked_identity_number' => $m->aadhaar_number ? ('XXXX-XXXX-' . substr($m->aadhaar_number, -4)) : null,
            'created_at'             => $m->created_at ? $m->created_at->format('Y-m-d H:i:s') : null,
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * PUT /api/v1/admin/memberships/{id}
     * Update member profile
     */
    public function update(Request $request, $id)
    {
        $member = Membership::findOrFail($id);

        $validated = $request->validate([
            'full_name'              => 'required|string|max:255',
            'gender'                 => 'nullable|string|in:Male,Female,Other',
            'dob'                    => 'nullable|string|max:20',
            'phone'                  => 'required|digits:10|unique:memberships,phone,' . $member->id,
            'father_or_husband_name' => 'required|string|max:255',
            'gotram'                 => 'required|string|max:255',
            'occupation'             => 'required|string|max:255',
            'blood_group'            => 'nullable|string|max:5',
            'email'                  => 'nullable|email|max:255',
            'pincode'                => 'required|digits:6',
            'grama_panchayat'        => 'required|string|max:255',
            'mandal'                 => 'required|string|max:255',
            'assembly_segment'       => 'nullable|string|max:255',
            'district'               => 'required|string|max:255',
            'state'                  => 'required|string|max:255',
            'country'                => 'nullable|string|max:255',
            'permanent_address'      => 'nullable|string',
            'present_address'        => 'nullable|string',
        ]);

        $member->full_name = strtoupper($validated['full_name']);
        $member->gender = $validated['gender'] ?? $member->gender;
        $member->dob = $validated['dob'] ?? $member->dob;
        $member->phone = $validated['phone'];
        $member->father_or_husband_name = $validated['father_or_husband_name'];
        $member->gotram = $validated['gotram'];
        $member->occupation = $validated['occupation'];
        $member->blood_group = $validated['blood_group'] ?? $member->blood_group;
        $member->email = $validated['email'] ?? $member->email;
        $member->pincode = $validated['pincode'];
        $member->grama_panchayat = $validated['grama_panchayat'];
        $member->mandal = $validated['mandal'];
        $member->assembly_segment = $validated['assembly_segment'] ?? $member->assembly_segment;
        $member->district = $validated['district'];
        $member->state = $validated['state'];
        $member->country = $validated['country'] ?? ($member->country ?? 'India');
        $member->permanent_address = $validated['permanent_address'] ?? $member->permanent_address;
        $member->present_address = $validated['present_address'] ?? $member->present_address;

        $member->save();

        return response()->json([
            'success' => true,
            'message' => 'Membership record updated successfully.',
            'data'    => $member,
        ]);
    }

    /**
     * DELETE /api/v1/admin/memberships/{id}
     * Delete member record
     */
    public function destroy($id)
    {
        $member = Membership::findOrFail($id);

        if ($member->photo_path && Storage::disk('public')->exists($member->photo_path)) {
            Storage::disk('public')->delete($member->photo_path);
        }

        $memberName = $member->full_name;
        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Membership record for ' . $memberName . ' permanently deleted.',
        ]);
    }
}
