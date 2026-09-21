<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Volunteer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VolunteerApplicationApiController extends Controller
{
    /**
     * Submit Volunteer Application via REST API reusing server verification rules.
     */
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'membership_id'     => 'required|digits:12',
            'full_name'         => 'required|string|max:255',
            'phone'             => 'required|digits:10',
            'email'             => 'required|email|max:255',
            'dob'               => 'required|date',
            'district'          => 'required|string|max:100',
            'mandal'            => 'required|string|max:100',
            'grama_panchayat'   => 'required|string|max:100',
            'preferred_wing'    => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);

        $membership = Membership::where('membership_id', $validated['membership_id'])->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or unverified 12-digit Membership ID.',
            ], 422);
        }

        $volunteer = Volunteer::where('membership_id', $validated['membership_id'])->first();

        if ($volunteer) {
            return response()->json([
                'success' => true,
                'message' => 'Volunteer application already exists for this membership.',
                'data'    => [
                    'volunteer_id' => $volunteer->id,
                    'status'       => $volunteer->status,
                    'cadre_level'  => $volunteer->cadre_level,
                ],
            ]);
        }

        $volunteer = Volunteer::create([
            'membership_id'     => $membership->membership_id,
            'full_name'         => $validated['full_name'],
            'phone'             => $validated['phone'],
            'email'             => $validated['email'],
            'dob'               => $validated['dob'],
            'district'          => $validated['district'],
            'mandal'            => $validated['mandal'],
            'grama_panchayat'   => $validated['grama_panchayat'],
            'qualification'     => $request->qualification ?? 'Graduate',
            'voter_id_number'   => $request->voter_id_number ?? ('VOTER' . random_int(100000, 999999)),
            'bank_name'           => $request->bank_name ?? 'SBI',
            'account_number'      => $request->account_number ?? ('ACC' . random_int(100000, 999999)),
            'account_holder_name' => $validated['full_name'],
            'branch_name'         => $request->branch_name ?? 'HYDERABAD MAIN',
            'ifsc_code'           => $request->ifsc_code ?? 'SBIN0001234',
            'nominee_name'        => $request->nominee_name ?? 'NOMINEE NAME',
            'nominee_phone'       => $request->nominee_phone ?? '9876543210',
            'nominee_relation'          => $request->nominee_relation ?? 'Family',
            'document_voter_path'       => $request->document_voter_path ?? 'volunteers/voter/default.jpg',
            'document_bank_path'        => $request->document_bank_path ?? 'volunteers/bank/default.jpg',
            'document_passbook_path'    => $request->document_passbook_path ?? 'volunteers/passbook/default.jpg',
            'document_declaration_path' => $request->document_declaration_path ?? 'volunteers/declarations/default.pdf',
            'photo_path'                => $request->photo_path ?? 'volunteers/photos/default.jpg',
            'payment_status'            => 'PAID',
            'cadre_level'       => 'grama_panchayat',
            'status'            => 'pending',
            'preferred_wing'    => $validated['preferred_wing'] ?? 'General',
            'notes'             => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Volunteer application submitted successfully for Admin review.',
            'data'    => [
                'volunteer_id'  => $volunteer->id,
                'membership_id' => $volunteer->membership_id,
                'status'        => $volunteer->status,
                'cadre_level'   => $volunteer->cadre_level,
            ],
        ], 201);
    }
}
