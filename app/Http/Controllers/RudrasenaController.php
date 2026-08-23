<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\RudrasenaMember;
use App\Mail\RudrasenaWelcomeMail;

class RudrasenaController extends Controller
{
    /**
     * Display the Advanced Rudrasena Dal Application Desk
     */
    public function showApplicationDesk()
    {
        return view('rudrasena_application');
    }

    /**
     * Verify Core 12-Digit Membership Status and Calculate Strict Age Constraints (24-45)
     */
    public function verifyCoreMembership(Request $request)
    {
        $request->validate([
            'membership_id' => 'required|string|size:12'
        ]);

        // Secure Lookup against master membership registry desk
        $member = DB::table('memberships')
            ->where('membership_id', $request->membership_id)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Given 12-Digit Membership ID is not registered in our central portal.'
            ]);
        }

        // Carbon Matrix Age Verification Layer (Strictly 24 to 45 Years window)
        $dobField = $member->date_of_birth ?? '1990-08-15';
        $dob = Carbon::parse($dobField);
        $age = $dob->age;

        if ($age < 24 || $age > 45) {
            return response()->json([
                'success' => false,
                'message' => "Eligibility Restriction: Age must be between 24 and 45. Your current calculated age is {$age}."
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Membership status verified & Age clearance granted!',
            'member' => [
                'membership_id' => $member->membership_id,
                'full_name' => $member->full_name,
                'email' => $member->email ?? 'N/A',
                'mobile' => $member->phone,
                'dob' => $dobField,
                'age' => $age,
                'blood_group' => $member->blood_group ?? 'N/A',
                'gotram' => $member->gotram ?? 'N/A'
            ]
        ]);
    }

    /**
     * Submit Advanced Relational Rudrasena Application Packet into Database Vault
     */
    public function submitApplicationPacket(Request $request)
    {
        $request->validate([
            'membership_id' => 'required|string|size:12',
            'full_name' => 'required|string',
            'email' => 'required|email',
            'mobile' => 'required|string',
            'volunteer_type' => 'required|string|max:255',
            'dob' => 'required|date',
            'age' => 'required|integer|between:24,45',
            
            // Nominee Validation Parameters
            'nominee_name' => 'required|string|max:255',
            'nominee_relation' => 'required|string|max:255',
            'nominee_age' => 'required|integer|min:1',
            'nominee_contact' => 'required|string',

            // Bank Account Validation Parameters
            'bank_holder_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_ifsc_code' => 'required|string|max:50',
            'bank_name_branch' => 'required|string|max:255',

            // 4 Comprehensive Legal Asset Documents Uploads
            'document_health_declaration' => 'required|image|max:2048',
            'document_family_declaration' => 'required|image|max:2048',
            'document_id_proof' => 'required|image|max:2048',
            'document_bank_proof' => 'required|image|max:2048',
            
            'disclaimer_accepted' => 'required|accepted',
            'family' => 'nullable|array'
        ]);

        // Anti-Fraud Duplication Layer Check
        $exists = DB::table('rudrasena_members')
            ->where('membership_id', $request->membership_id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted an active registration packet for Rudrasena Dal.'
            ]);
        }

        // File Repository Heavy Upload Handling Matrix
        $pathHealth = $request->file('document_health_declaration')->store('rudrasena/health', 'public');
        $pathFamily = $request->file('document_family_declaration')->store('rudrasena/family_sheets', 'public');
        $pathId = $request->file('document_id_proof')->store('rudrasena/ids', 'public');
        $pathBank = $request->file('document_bank_proof')->store('rudrasena/bank_proofs', 'public');

        DB::beginTransaction();

        try {
            $masterId = DB::table('rudrasena_members')->insertGetId([
                'membership_id' => $request->membership_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'volunteer_type' => $request->volunteer_type,
                'dob' => $request->dob,
                'age' => $request->age,
                'blood_group' => $request->blood_group,
                'gotram' => $request->gotram,
                
                'nominee_name' => $request->nominee_name,
                'nominee_relation' => $request->nominee_relation,
                'nominee_age' => $request->nominee_age,
                'nominee_contact' => $request->nominee_contact,

                'bank_holder_name' => $request->bank_holder_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_ifsc_code' => strtoupper($request->bank_ifsc_code),
                'bank_name_branch' => $request->bank_name_branch,

                'document_health_declaration' => $pathHealth,
                'document_family_declaration' => $pathFamily,
                'document_id_proof' => $pathId,
                'document_bank_proof' => $pathBank,
                
                'status' => 'pending',
                'disclaimer_accepted' => true,
                'terms_accepted_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($request->has('family') && is_array($request->family)) {
                foreach ($request->family as $row) {
                    if (!empty($row['name']) && !empty($row['relation'])) {
                        DB::table('rudrasena_family_details')->insert([
                            'rudrasena_member_id' => $masterId,
                            'member_name' => $row['name'],
                            'member_relation' => $row['relation'],
                            'member_age' => (int)$row['age'],
                            'member_gender' => $row['gender'] ?? 'Other',
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '🎉 Advanced Rudrasena Application Packet Secured Successfully! Family structural tree mapped & Central Admin Desk notified.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Critical relational transaction block error occurred. Refused packet ingestion: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Central Administrative Panel Rudrasena Roster (Screen 1)
     */
    public function adminIndex(Request $request)
    {
        $searchQuery = $request->input('search');

        $query = DB::table('rudrasena_members')
            ->leftJoin('memberships', 'rudrasena_members.membership_id', '=', 'memberships.membership_id')
            ->select(
                'rudrasena_members.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal'
            );

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('rudrasena_members.full_name', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('rudrasena_members.membership_id', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('rudrasena_members.mobile', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('rudrasena_members.email', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('rudrasena_members.rudrasena_id', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('rudrasena_members.volunteer_type', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('rudrasena_members.assigned_cadder', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('rudrasena_members.assigned_locality', 'LIKE', '%' . $searchQuery . '%');
            });
        }

        $members = $query->orderBy('rudrasena_members.created_at', 'desc')->paginate(15);

        return view('admin.rudrasena_admin_grid', compact('members', 'searchQuery'));
    }

    /**
     * View Read-Only Rudrasena Member Dossier
     */
    public function viewMember($id)
    {
        $member = DB::table('rudrasena_members')
            ->leftJoin('memberships', 'rudrasena_members.membership_id', '=', 'memberships.membership_id')
            ->select(
                'rudrasena_members.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.aadhaar_number as member_aadhaar_number',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.grama_panchayat as member_grama_panchayat',
                'memberships.state as member_state',
                'memberships.pincode as member_pincode'
            )
            ->where('rudrasena_members.id', $id)
            ->first();

        if (!$member) {
            abort(404, 'Rudrasena member record not found');
        }

        $familyDetails = DB::table('rudrasena_family_details')
            ->where('rudrasena_member_id', $id)
            ->get();

        return view('admin.rudrasena_profile_view', compact('member', 'familyDetails'));
    }

    /**
     * Render Vertical PVC Rudrasena ID Card Screen
     */
    public function viewCard($id)
    {
        $member = DB::table('rudrasena_members')
            ->leftJoin('memberships', 'rudrasena_members.membership_id', '=', 'memberships.membership_id')
            ->select(
                'rudrasena_members.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.blood_group as member_blood_group',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal'
            )
            ->where('rudrasena_members.id', $id)
            ->first();

        if (!$member) {
            abort(404, 'Rudrasena member record not found');
        }

        $cardData = [
            'id' => $member->id,
            'full_name' => $member->member_full_name ?: $member->full_name,
            'rudrasena_id' => $member->rudrasena_id ?: '',
            'membership_id' => $member->membership_id,
            'volunteer_type' => $member->volunteer_type ?? 'Standard',
            'assigned_cadder' => $member->assigned_cadder,
            'assigned_locality' => $member->assigned_locality,
            'blood_group' => $member->blood_group ?: ($member->member_blood_group ?? 'N/A'),
            'mobile' => $member->mobile,
            'email' => $member->email,
            'photo_path' => $member->member_photo_path ?? null,
            'status' => $member->status,
        ];

        return view('admin.rudrasena_card_view', compact('cardData'));
    }

    /**
     * Edit Cadre / Locality / Status Form (Screen 2/3)
     */
    public function editMemberForm($id)
    {
        $member = DB::table('rudrasena_members')
            ->leftJoin('memberships', 'rudrasena_members.membership_id', '=', 'memberships.membership_id')
            ->select(
                'rudrasena_members.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal'
            )
            ->where('rudrasena_members.id', $id)
            ->first();

        if (!$member) {
            abort(404, 'Rudrasena member record not found');
        }

        return view('admin.rudrasena_cadre_update', compact('member'));
    }

    /**
     * Process Cadder, Locality & Status Update with Sequential ID Generation (RS0001...) & Automated Delivery
     */
    public function updateMember(Request $request, $id)
    {
        $rawStatus = $request->input('status');
        
        $statusMapping = [
            'Verified' => 'verified',
            'verified' => 'verified',
            'approved' => 'verified',
            'Rejected' => 'rejected',
            'rejected' => 'rejected',
            'Pending'  => 'pending',
            'pending'  => 'pending'
        ];

        $mappedStatus = $statusMapping[$rawStatus] ?? $rawStatus;
        $request->merge(['status' => $mappedStatus]);

        $request->validate([
            'status' => 'required|string|in:verified,rejected,pending',
            'assigned_cadder' => 'required|string|max:255',
            'assigned_locality' => 'required|string|max:255'
        ]);

        $cadre = $request->input('assigned_cadder');
        $locality = $request->input('assigned_locality');

        $member = DB::table('rudrasena_members')->where('id', $id)->first();
        if (!$member) {
            abort(404, 'Rudrasena member not found');
        }

        if ($mappedStatus === 'verified') {
            $isFirstTimeApproval = empty($member->rudrasena_id);
            $generatedId = $member->rudrasena_id;

            if ($isFirstTimeApproval) {
                // Sequential ID in format RS0001, RS0002... incrementing off highest existing rudrasena_id
                $lastMember = DB::table('rudrasena_members')
                    ->whereNotNull('rudrasena_id')
                    ->where('rudrasena_id', 'LIKE', 'RS%')
                    ->orderByRaw('CAST(SUBSTRING(rudrasena_id, 3) AS UNSIGNED) DESC')
                    ->first();

                $nextNumber = 1;
                if ($lastMember && preg_match('/^RS(\d+)$/', $lastMember->rudrasena_id, $matches)) {
                    $nextNumber = (int)$matches[1] + 1;
                }
                $generatedId = sprintf('RS%04d', $nextNumber);

                DB::table('rudrasena_members')->where('id', $id)->update([
                    'status' => 'verified',
                    'assigned_cadder' => $cadre,
                    'assigned_locality' => $locality,
                    'rudrasena_id' => $generatedId,
                    'updated_at' => Carbon::now()
                ]);

                // Dispatch Welcome Email with Attached PDF ID Card (Idempotent)
                $membership = DB::table('memberships')->where('membership_id', $member->membership_id)->first();
                $cardData = [
                    'id' => $member->id,
                    'full_name' => $membership->full_name ?? $member->full_name,
                    'rudrasena_id' => $generatedId,
                    'membership_id' => $member->membership_id,
                    'volunteer_type' => $member->volunteer_type ?? 'Standard',
                    'assigned_cadder' => $cadre,
                    'assigned_locality' => $locality,
                    'blood_group' => $member->blood_group ?: ($membership->blood_group ?? 'N/A'),
                    'mobile' => $member->mobile,
                    'email' => $member->email,
                    'photo_path' => $membership->photo_path ?? null,
                    'status' => 'verified'
                ];

                if (!empty($member->email) && !\App\Models\NotificationLog::alreadySent(\App\Models\RudrasenaMember::class, $id, 'email', 'rudrasena_welcome')) {
                    $pdfContent = null;
                    try {
                        $pdf = Pdf::loadView('pdf.rudrasena_card_pdf', compact('cardData'));
                        $pdfContent = $pdf->output();

                        Mail::to($member->email)->send(new RudrasenaWelcomeMail($cardData, $pdfContent));

                        $mailStatus = config('mail.default') === 'log' ? 'logged' : 'sent';
                        \App\Models\NotificationLog::record([
                            'event_type'      => 'rudrasena_welcome',
                            'notifiable_type' => \App\Models\RudrasenaMember::class,
                            'notifiable_id'   => $id,
                            'channel'         => 'email',
                            'recipient_email' => $member->email,
                            'subject'         => 'Welcome to ABVHPS Rudrasena Dal',
                            'message'         => 'Rudrasena welcome email sent with ID: ' . $generatedId,
                            'status'          => $mailStatus,
                            'sent_at'         => now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Rudrasena welcome email generation/dispatch error: ' . $e->getMessage());
                    }

                    session(['last_rudrasena_email_log' => [
                        'recipient_email' => $member->email,
                        'rudrasena_id' => $generatedId,
                        'status' => 'dispatched'
                    ]]);
                }
            } else {
                DB::table('rudrasena_members')->where('id', $id)->update([
                    'status' => 'verified',
                    'assigned_cadder' => $cadre,
                    'assigned_locality' => $locality,
                    'updated_at' => Carbon::now()
                ]);
            }

            return redirect()->route('admin.rudrasena.index')
                ->with('success', "🎉 Rudrasena member #{$id} verified successfully with ID {$generatedId}!");
        }

        // If Rejected or Pending status
        DB::table('rudrasena_members')->where('id', $id)->update([
            'status' => $mappedStatus,
            'assigned_cadder' => $cadre,
            'assigned_locality' => $locality,
            'updated_at' => Carbon::now()
        ]);

        $statusText = $mappedStatus === 'rejected' ? 'rejected' : 'marked as pending';
        return redirect()->route('admin.rudrasena.index')
            ->with('success', "Rudrasena member #{$id} status has been {$statusText} successfully.");
    }

    public function approveMember(Request $request, $id)
    {
        return $this->updateMember($request, $id);
    }

    /**
     * Delete Rudrasena Member
     */
    public function deleteMember($id)
    {
        $member = DB::table('rudrasena_members')->where('id', $id)->first();
        if (!$member) {
            abort(404, 'Rudrasena member not found');
        }

        // Remove files
        if ($member->document_health_declaration && Storage::disk('public')->exists($member->document_health_declaration)) {
            Storage::disk('public')->delete($member->document_health_declaration);
        }
        if ($member->document_family_declaration && Storage::disk('public')->exists($member->document_family_declaration)) {
            Storage::disk('public')->delete($member->document_family_declaration);
        }
        if ($member->document_id_proof && Storage::disk('public')->exists($member->document_id_proof)) {
            Storage::disk('public')->delete($member->document_id_proof);
        }
        if ($member->document_bank_proof && Storage::disk('public')->exists($member->document_bank_proof)) {
            Storage::disk('public')->delete($member->document_bank_proof);
        }

        DB::table('rudrasena_family_details')->where('rudrasena_member_id', $id)->delete();
        DB::table('rudrasena_members')->where('id', $id)->delete();

        return redirect()->route('admin.rudrasena.index')
            ->with('success', '🗑️ Rudrasena member record permanently deleted.');
    }
}
