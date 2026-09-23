<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberApplicationApiController extends Controller
{
    /**
     * Submit Membership Application via REST API.
     */
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'                     => 'required|digits:10',
            'full_name'                 => 'required|string|max:255',
            'email'                     => 'nullable|email|max:255',
            'dob'                       => 'required|date',
            'gender'                    => 'required|string|in:male,female,other',
            'father_or_husband_name'    => 'required|string|max:255',
            'address'                   => 'required|string',
            'district'                  => 'required|string|max:100',
            'mandal'                    => 'required|string|max:100',
            'grama_panchayat'           => 'required|string|max:100',
            'pincode'                   => 'required|digits:6',
        ]);

        $phone = $validated['phone'];

        // Check if member record already exists for this phone number
        $member = Membership::where('phone', $phone)->first();

        if (!$member) {
            $membershipId = str_pad((string) random_int(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

            $member = Membership::create([
                'membership_id'          => $membershipId,
                'phone'                  => $phone,
                'full_name'              => $validated['full_name'],
                'email'                  => $validated['email'] ?? null,
                'dob'                    => $validated['dob'],
                'gender'                 => strtolower($validated['gender']),
                'father_or_husband_name' => $validated['father_or_husband_name'],
                'address'                => $validated['address'],
                'district'               => $validated['district'],
                'mandal'                 => $validated['mandal'],
                'grama_panchayat'        => $validated['grama_panchayat'],
                'pincode'                => $validated['pincode'],
                'status'                 => 'approved',
                'payment_status'         => 'PAID',
            ]);
        } else {
            $member->update([
                'full_name'              => $validated['full_name'],
                'email'                  => $validated['email'] ?? $member->email,
                'dob'                    => $validated['dob'],
                'gender'                 => strtolower($validated['gender']),
                'father_or_husband_name' => $validated['father_or_husband_name'],
                'address'                => $validated['address'],
                'district'               => $validated['district'],
                'mandal'                 => $validated['mandal'],
                'grama_panchayat'        => $validated['grama_panchayat'],
                'pincode'                => $validated['pincode'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Membership application processed successfully.',
            'data'    => [
                'membership_id'  => $member->membership_id,
                'full_name'      => $member->full_name,
                'phone'          => $member->phone,
                'status'         => $member->status ?? 'approved',
                'payment_status' => $member->payment_status ?? 'PAID',
                'card_url'       => url("/membership/view-card?id={$member->membership_id}"),
            ],
        ], 201);
    }
}
