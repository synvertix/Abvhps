<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\GeoState;
use App\Models\GeoDistrict;
use App\Models\GeoAssemblySegment;
use App\Models\GeoMandal;
use App\Models\GeoPanchayat;
use App\Services\VolunteerCadreScopeService;
use App\Services\GeoHierarchyBackfillService;
use App\Mail\VolunteerWelcomeMail;
use App\Mail\VolunteerApplicationReceivedMail;
use App\Mail\VolunteerPendingStatusMail;
use App\Mail\VolunteerRejectedStatusMail;
use App\Mail\VolunteerAssignmentUpdatedMail;
use Illuminate\Support\Facades\Auth;

class VolunteerController extends Controller
{
    // 1. Show the Membership ID & Mobile verification form
    public function showCheckForm()
    {
        return view('volunteer_check');
    }

    // 2. Verify Membership details from server to open registration application
    public function verifyMembership(Request $request)
    {
        $request->validate([
            'membership_id' => 'required|string|max:12',
            'phone' => 'required|digits:10'
        ]);

        $membershipId = $request->input('membership_id');
        $phone = $request->input('phone');

        // Check if both membership_id and phone numbers match perfectly inside database server records
        $member = DB::table('memberships')
            ->where('membership_id', $membershipId)
            ->where('phone', $phone)
            ->where('payment_status', 'success')
            ->first();

        if (!$member) {
            return redirect()->back()->with('error', 'Membership ID and Mobile Number do not match our server records. Please check.');
        }

        // Keep verified parameters inside session to authorize form load step
        session([
            'verified_volunteer_membership_id' => $membershipId,
            'verified_volunteer_phone' => $phone
        ]);

        return redirect('/volunteer/application');
    }

    // 3. Render Volunteer Registration Form loading data directly from memberships row tracking
    public function showApplicationForm()
    {
        $membershipId = session('verified_volunteer_membership_id');
        $phone = session('verified_volunteer_phone');

        if (!$membershipId || !$phone) {
            return redirect('/volunteer')->with('error', 'Please verify your membership credentials first.');
        }

        // Fetching profile metrics rows mapped from verified membership fields setup
        $member = DB::table('memberships')->where('membership_id', $membershipId)->first();

        // Safe tracking payload container
        $mappedData = [
            'full_name' => $member->full_name,
            'membership_id' => $member->membership_id,
            'phone' => $member->phone,
            'blood_group' => $member->blood_group,
            'pincode' => $member->pincode,
            'grama_panchayat' => $member->grama_panchayat,
            'mandal' => $member->mandal,
            'assembly_segment' => $member->assembly_segment,
            'district' => $member->district,
            'state' => $member->state,
            'country' => $member->country
        ];

        // Placeholder view trigger for the next part setup stage
        return view('volunteer_application', compact('mappedData'));
    }

    // 4. Store Volunteer Application Form Data into database as Pending Status
    public function submitApplication(Request $request)
    {
        $membershipId = session('verified_volunteer_membership_id');
        $phone = session('verified_volunteer_phone');

        if (!$membershipId || !$phone) {
            return redirect('/volunteer')->with('error', 'Please verify your membership credentials first.');
        }

        // Strict validation metrics for checking mandatory inputs and file attachments
        $request->validate([
            'qualification' => 'required|string|max:255',
            'voter_id_number' => 'required|string|max:50',
            'email' => 'required|email|max:255', // Strictly mandatory for volunteers
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:11',
            'branch_name' => 'required|string|max:255',
            'nominee_name' => 'required|string|max:255',
            'nominee_relation' => 'required|string|max:255',
            'nominee_phone' => 'required|digits:10',
            'doc_declaration' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'doc_voter' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'doc_bank' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        // Check if volunteer application already submitted for this membership
        $existingVolunteer = DB::table('volunteers')->where('membership_id', $membershipId)->first();
        if ($existingVolunteer) {
            return redirect('/volunteer/success-notice')->with('info', 'Your volunteer application is already submitted and currently under review.');
        }

        // File upload tracking logic saving physical attachments into public storage folders
        $declarationPath = $request->file('doc_declaration')->store('volunteer_docs/declarations', 'public');
        $voterPath = $request->file('doc_voter')->store('volunteer_docs/voters', 'public');
        $bankPath = $request->file('doc_bank')->store('volunteer_docs/banks', 'public');

        // Fetch member location data to associate with volunteer profile
        $member = DB::table('memberships')->where('membership_id', $membershipId)->first();

        // Inserting pristine form details into volunteers table with dynamic pending status configuration
        $volunteerInsertId = DB::table('volunteers')->insertGetId([
            'membership_id' => $membershipId,
            'phone' => $phone,
            'qualification' => $request->input('qualification'),
            'voter_id_number' => strtoupper($request->input('voter_id_number')),
            'email' => $request->input('email'),
            'bank_name' => $request->input('bank_name'),
            'account_holder_name' => $request->input('account_holder_name'),
            'account_number' => $request->input('account_number'),
            'ifsc_code' => strtoupper($request->input('ifsc_code')),
            'branch_name' => $request->input('branch_name'),
            'nominee_name' => $request->input('nominee_name'),
            'nominee_relation' => $request->input('nominee_relation'),
            'nominee_phone' => $request->input('nominee_phone'),
            'document_declaration_path' => $declarationPath,
            'document_voter_path' => $voterPath,
            'document_bank_path' => $bankPath,
            'status' => 'pending', // Trailing pending state waiting strictly for central admin desk clearance
            'is_active' => true,
            'country' => $member->country ?? 'India',
            'state' => $member->state ?? null,
            'district' => $member->district ?? null,
            'assembly_segment' => $member->assembly_segment ?? null,
            'mandal' => $member->mandal ?? null,
            'grama_panchayat' => $member->grama_panchayat ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $volunteerEmail = $request->input('email');
        if (!empty($volunteerEmail) && filter_var($volunteerEmail, FILTER_VALIDATE_EMAIL)) {
            $volunteerData = [
                'volunteer_name'   => $member->full_name ?? 'Volunteer',
                'full_name'        => $member->full_name ?? 'Volunteer',
                'membership_id'    => $membershipId,
                'application_date' => now('Asia/Kolkata')->format('d-M-Y'),
            ];

            $idempotencyKey = "volunteer_application_received:{$volunteerInsertId}";
            $claim = \App\Models\NotificationLog::claim(
                $idempotencyKey,
                'volunteer_application_received',
                \App\Models\Volunteer::class,
                (int)$volunteerInsertId,
                'email',
                $volunteerEmail,
                $phone,
                'ABVHPS Volunteer Application Received',
                'Volunteer application received confirmation sent for ID: ' . $membershipId
            );

            if ($claim) {
                try {
                    Mail::to($volunteerEmail)->send(new VolunteerApplicationReceivedMail($volunteerData));
                    $claim->markSent('ABVHPS Volunteer Application Received', 'Volunteer application received confirmation sent for ID: ' . $membershipId);
                } catch (\Throwable $e) {
                    Log::error('Volunteer application received email error: ' . $e->getMessage());
                    $claim->markFailed($e->getMessage());
                }
            }
        }

        return redirect('/volunteer/success-notice');
    }

    // 5. Show Pending Form Submission Notice view component
    public function showSuccessNotice()
    {
        return view('volunteer_success_notice');
    }

    // 6. Central Administrative Panel Volunteer List Screen (Screen 1)
    public function adminIndex(Request $request)
    {
        $searchQuery = $request->input('search');

        $query = DB::table('volunteers')
            ->leftJoin('memberships', 'volunteers.membership_id', '=', 'memberships.membership_id')
            ->select(
                'volunteers.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.aadhaar_number as member_aadhaar_number',
                'memberships.blood_group as member_blood_group',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.grama_panchayat as member_grama_panchayat',
                'memberships.state as member_state'
            );

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('memberships.full_name', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('volunteers.membership_id', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('volunteers.phone', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('volunteers.email', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('volunteers.volunteer_id', 'LIKE', '%' . $searchQuery . '%');
            });
        }

        $volunteers = $query->orderBy('volunteers.created_at', 'desc')->paginate(15);

        $total = $volunteers->total();
        $latestRecord = DB::table('volunteers')->orderByDesc('created_at')->first(['id', 'updated_at']);
        $firstId = $volunteers->first()->id ?? 0;
        $rowSignature = $volunteers->map(fn($v) => $v->id . ':' . $v->status . ':' . ($v->volunteer_id ?? ''))->join('|');
        $initialSignature = md5($total . '_' . ($latestRecord->id ?? 0) . '_' . ($latestRecord->updated_at ?? '') . '_' . $firstId . '_' . $rowSignature);

        return view('admin.volunteer_admin_grid', compact('volunteers', 'searchQuery', 'initialSignature'));
    }

    // 6b. Live Synchronization JSON Endpoint for Admin Volunteer Desk
    public function liveSync(Request $request)
    {
        $searchQuery = $request->input('search');

        $query = DB::table('volunteers')
            ->leftJoin('memberships', 'volunteers.membership_id', '=', 'memberships.membership_id')
            ->select(
                'volunteers.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.aadhaar_number as member_aadhaar_number',
                'memberships.blood_group as member_blood_group',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.grama_panchayat as member_grama_panchayat',
                'memberships.state as member_state'
            );

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('memberships.full_name', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('volunteers.membership_id', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('volunteers.phone', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('volunteers.email', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('volunteers.volunteer_id', 'LIKE', '%' . $searchQuery . '%');
            });
        }

        $volunteers = $query->orderBy('volunteers.created_at', 'desc')->paginate(15);

        $total = $volunteers->total();
        $totalAll = DB::table('volunteers')->count();
        $pendingCount = DB::table('volunteers')->where('status', 'pending')->count();
        $latestRecord = DB::table('volunteers')->orderByDesc('created_at')->first(['id', 'updated_at']);
        $firstId = $volunteers->first()->id ?? 0;
        $rowSignature = $volunteers->map(fn($v) => $v->id . ':' . $v->status . ':' . ($v->volunteer_id ?? ''))->join('|');
        $datasetSignature = md5($total . '_' . ($latestRecord->id ?? 0) . '_' . ($latestRecord->updated_at ?? '') . '_' . $firstId . '_' . $rowSignature);

        return response()->json([
            'success' => true,
            'signature' => $datasetSignature,
            'total' => $total,
            'total_all' => $totalAll,
            'pending_count' => $pendingCount,
            'current_page' => $volunteers->currentPage(),
            'last_page' => $volunteers->lastPage(),
            'has_pages' => $volunteers->hasPages(),
            'html' => view('admin.partials.volunteer_table_rows', compact('volunteers'))->render(),
            'pagination_html' => $volunteers->hasPages() ? $volunteers->appends(['search' => $searchQuery])->links()->render() : '',
            'synced_at' => now()->format('h:i:s A')
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    // 7. Full Volunteer Profile Edit Form (Screen 2)
    public function editFull($id)
    {
        $volunteer = DB::table('volunteers')
            ->leftJoin('memberships', 'volunteers.membership_id', '=', 'memberships.membership_id')
            ->select(
                'volunteers.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.grama_panchayat as member_grama_panchayat'
            )
            ->where('volunteers.id', $id)
            ->first();

        if (!$volunteer) {
            abort(404, 'Volunteer record not found');
        }

        return view('admin.volunteer_edit_full', compact('volunteer'));
    }

    // 8. Process Full Volunteer Profile Update (Screen 2)
    public function updateFull(Request $request, $id)
    {
        $request->validate([
            'qualification' => 'required|string|max:255',
            'voter_id_number' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:11',
            'branch_name' => 'required|string|max:255',
            'nominee_name' => 'required|string|max:255',
            'nominee_relation' => 'required|string|max:255',
            'nominee_phone' => 'required|digits:10',
            'doc_declaration' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'doc_voter' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'doc_bank' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        $volunteer = DB::table('volunteers')->where('id', $id)->first();
        if (!$volunteer) {
            abort(404, 'Volunteer not found');
        }

        $updateData = [
            'qualification' => $request->input('qualification'),
            'voter_id_number' => strtoupper($request->input('voter_id_number')),
            'email' => $request->input('email'),
            'bank_name' => $request->input('bank_name'),
            'account_holder_name' => $request->input('account_holder_name'),
            'account_number' => $request->input('account_number'),
            'ifsc_code' => strtoupper($request->input('ifsc_code')),
            'branch_name' => $request->input('branch_name'),
            'nominee_name' => $request->input('nominee_name'),
            'nominee_relation' => $request->input('nominee_relation'),
            'nominee_phone' => $request->input('nominee_phone'),
            'updated_at' => now()
        ];

        // Handle re-upload/replacement of uploaded files if provided
        if ($request->hasFile('doc_declaration')) {
            if ($volunteer->document_declaration_path && Storage::disk('public')->exists($volunteer->document_declaration_path)) {
                Storage::disk('public')->delete($volunteer->document_declaration_path);
            }
            $updateData['document_declaration_path'] = $request->file('doc_declaration')->store('volunteer_docs/declarations', 'public');
        }

        if ($request->hasFile('doc_voter')) {
            if ($volunteer->document_voter_path && Storage::disk('public')->exists($volunteer->document_voter_path)) {
                Storage::disk('public')->delete($volunteer->document_voter_path);
            }
            $updateData['document_voter_path'] = $request->file('doc_voter')->store('volunteer_docs/voters', 'public');
        }

        if ($request->hasFile('doc_bank')) {
            if ($volunteer->document_bank_path && Storage::disk('public')->exists($volunteer->document_bank_path)) {
                Storage::disk('public')->delete($volunteer->document_bank_path);
            }
            $updateData['document_bank_path'] = $request->file('doc_bank')->store('volunteer_docs/banks', 'public');
        }

        DB::table('volunteers')->where('id', $id)->update($updateData);

        return redirect()->route('admin.volunteers.index')
            ->with('success', 'Volunteer #' . $id . ' profile updated successfully.');
    }

    // 9. Cadder / Status Update Form (Screen 3)
    public function cadreEditForm($id)
    {
        $volunteer = Volunteer::with([
            'membership',
            'stateRelation',
            'districtRelation',
            'assemblySegmentRelation',
            'mandalRelation',
            'panchayatRelation'
        ])->find($id);

        if (!$volunteer) {
            abort(404, 'Volunteer record not found');
        }

        // Prefill logic: if canonical IDs are not yet set, attempt deterministic lookup from legacy strings
        $prefilledStateId = $volunteer->state_id;
        $prefilledDistrictId = $volunteer->district_id;
        $prefilledAssemblyId = $volunteer->assembly_segment_id;
        $prefilledMandalId = $volunteer->mandal_id;
        $prefilledPanchayatId = $volunteer->panchayat_id;

        if (!$prefilledStateId && $volunteer->resolved_state) {
            $matchedState = GeoState::whereRaw('LOWER(name) = ?', [GeoHierarchyBackfillService::normalize($volunteer->resolved_state)])->first();
            $prefilledStateId = $matchedState?->id;
        }

        if (!$prefilledDistrictId && $volunteer->resolved_district && $prefilledStateId) {
            $normDist = GeoHierarchyBackfillService::normalize($volunteer->resolved_district);
            $matchedDistrict = GeoDistrict::where('state_id', $prefilledStateId)->whereRaw('LOWER(name) = ?', [$normDist])->first();
            if (!$matchedDistrict) {
                $alias = \App\Models\GeoAlias::where('entity_type', 'district')->where('state_id', $prefilledStateId)->whereRaw('LOWER(alias_name) = ?', [$normDist])->first();
                if ($alias) {
                    $matchedDistrict = GeoDistrict::find($alias->canonical_id);
                }
            }
            $prefilledDistrictId = $matchedDistrict?->id;
        }

        if (!$prefilledAssemblyId && $volunteer->resolved_assembly_segment && $prefilledDistrictId) {
            $matchedAssembly = GeoAssemblySegment::where('district_id', $prefilledDistrictId)->whereRaw('LOWER(name) = ?', [GeoHierarchyBackfillService::normalize($volunteer->resolved_assembly_segment)])->first();
            $prefilledAssemblyId = $matchedAssembly?->id;
        }

        if (!$prefilledMandalId && $volunteer->resolved_mandal && $prefilledDistrictId) {
            $matchedMandal = GeoMandal::where('district_id', $prefilledDistrictId)->whereRaw('LOWER(name) = ?', [GeoHierarchyBackfillService::normalize($volunteer->resolved_mandal)])->first();
            $prefilledMandalId = $matchedMandal?->id;
            if (!$prefilledAssemblyId && $matchedMandal?->assembly_segment_id) {
                $prefilledAssemblyId = $matchedMandal->assembly_segment_id;
            }
        }

        if (!$prefilledPanchayatId && $volunteer->resolved_grama_panchayat && $prefilledMandalId) {
            $matchedPanchayat = GeoPanchayat::where('mandal_id', $prefilledMandalId)->whereRaw('LOWER(name) = ?', [GeoHierarchyBackfillService::normalize($volunteer->resolved_grama_panchayat)])->first();
            $prefilledPanchayatId = $matchedPanchayat?->id;
        }

        $states = GeoState::where('is_active', true)->orderBy('name')->get();
        $districts = $prefilledStateId ? GeoDistrict::where('state_id', $prefilledStateId)->where('is_active', true)->orderBy('name')->get() : collect();
        $assemblySegments = $prefilledDistrictId ? GeoAssemblySegment::where('district_id', $prefilledDistrictId)->where('is_active', true)->orderBy('name')->get() : collect();
        $mandals = $prefilledDistrictId ? GeoMandal::where('district_id', $prefilledDistrictId)->where('is_active', true)->orderBy('name')->get() : collect();
        $panchayats = $prefilledMandalId ? GeoPanchayat::where('mandal_id', $prefilledMandalId)->where('is_active', true)->orderBy('name')->get() : collect();

        $cadreLevels = [
            'panchayat_president' => 'Panchayat President',
            'mandal_president'    => 'Mandal President',
            'assembly_president'  => 'Taluka President / Assembly Segment President',
            'district_president'  => 'District President',
            'state_president'     => 'State President',
            'national_president'  => 'National President',
            'volunteer'           => 'Volunteer',
        ];

        return view('admin.volunteer_cadre_update', compact(
            'volunteer',
            'states',
            'districts',
            'assemblySegments',
            'mandals',
            'panchayats',
            'cadreLevels',
            'prefilledStateId',
            'prefilledDistrictId',
            'prefilledAssemblyId',
            'prefilledMandalId',
            'prefilledPanchayatId'
        ));
    }

    // 9b. AJAX endpoint for cascading geography selectors
    public function getGeoHierarchyAjax(Request $request)
    {
        $stateId = $request->query('state_id');
        $districtId = $request->query('district_id');
        $assemblyId = $request->query('assembly_segment_id');
        $mandalId = $request->query('mandal_id');

        if ($mandalId) {
            $panchayats = GeoPanchayat::where('mandal_id', $mandalId)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'pincode']);
            return response()->json(['panchayats' => $panchayats]);
        }

        if ($assemblyId) {
            $mandals = GeoMandal::where('assembly_segment_id', $assemblyId)->where('is_active', true)->orderBy('name')->get(['id', 'name']);
            return response()->json(['mandals' => $mandals]);
        }

        if ($districtId) {
            $assemblies = GeoAssemblySegment::where('district_id', $districtId)->where('is_active', true)->orderBy('name')->get(['id', 'name']);
            $mandals = GeoMandal::where('district_id', $districtId)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'assembly_segment_id']);
            return response()->json(['assembly_segments' => $assemblies, 'mandals' => $mandals]);
        }

        if ($stateId) {
            $districts = GeoDistrict::where('state_id', $stateId)->where('is_active', true)->orderBy('name')->get(['id', 'name']);
            return response()->json(['districts' => $districts]);
        }

        $states = GeoState::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return response()->json(['states' => $states]);
    }

    // 10. Process Cadder / Status Update & ID Generation (Screen 3)
    public function cadreUpdate(Request $request, $id)
    {
        $rawStatus = $request->input('status');
        
        // Map display labels (Verified/Rejected/Pending) to column values (approved/rejected/pending)
        $statusMapping = [
            'Verified' => 'approved',
            'approved' => 'approved',
            'Rejected' => 'rejected',
            'rejected' => 'rejected',
            'Pending'  => 'pending',
            'pending'  => 'pending'
        ];

        $mappedStatus = $statusMapping[$rawStatus] ?? $rawStatus;
        $request->merge(['status' => $mappedStatus]);

        $request->validate([
            'status'               => 'required|string|in:approved,rejected,pending',
            'cadre_level'          => 'nullable|string|in:national_president,state_president,district_president,assembly_president,mandal_president,panchayat_president,volunteer',
            'cadre'                => 'nullable|string|max:255',
            'locality'             => 'nullable|string|max:255',
            'state_id'             => 'nullable|integer|exists:geo_states,id',
            'district_id'          => 'nullable|integer|exists:geo_districts,id',
            'assembly_segment_id'  => 'nullable|integer|exists:geo_assembly_segments,id',
            'mandal_id'            => 'nullable|integer|exists:geo_mandals,id',
            'panchayat_id'         => 'nullable|integer|exists:geo_panchayats,id',
        ]);

        $volunteer = Volunteer::findOrFail($id);
        $cadreLevel = $request->input('cadre_level') ?: 'volunteer';
        $stateId = $request->input('state_id') ? (int)$request->input('state_id') : null;
        $districtId = $request->input('district_id') ? (int)$request->input('district_id') : null;
        $assemblyId = $request->input('assembly_segment_id') ? (int)$request->input('assembly_segment_id') : null;
        $mandalId = $request->input('mandal_id') ? (int)$request->input('mandal_id') : null;
        $panchayatId = $request->input('panchayat_id') ? (int)$request->input('panchayat_id') : null;

        if ($mappedStatus === 'approved') {
            // Validate required jurisdiction IDs based on cadre level
            if ($cadreLevel === 'panchayat_president' && (!$panchayatId || !$mandalId || !$assemblyId || !$districtId || !$stateId)) {
                return back()->withInput()->withErrors(['panchayat_id' => 'Panchayat President requires valid State, District, Assembly Segment, Mandal, and Panchayat selections.']);
            }
            if ($cadreLevel === 'mandal_president' && (!$mandalId || !$assemblyId || !$districtId || !$stateId)) {
                return back()->withInput()->withErrors(['mandal_id' => 'Mandal President requires valid State, District, Assembly Segment, and Mandal selections.']);
            }
            if ($cadreLevel === 'assembly_president' && (!$assemblyId || !$districtId || !$stateId)) {
                return back()->withInput()->withErrors(['assembly_segment_id' => 'Taluka / Assembly Segment President requires valid State, District, and Assembly Segment selections.']);
            }
            if ($cadreLevel === 'district_president' && (!$districtId || !$stateId)) {
                return back()->withInput()->withErrors(['district_id' => 'District President requires valid State and District selections.']);
            }
            if ($cadreLevel === 'state_president' && !$stateId) {
                return back()->withInput()->withErrors(['state_id' => 'State President requires a valid State selection.']);
            }

            // Parent-child hierarchy check
            $hierarchyError = VolunteerCadreScopeService::validateParentChildGeography($stateId, $districtId, $assemblyId, $mandalId, $panchayatId);
            if ($hierarchyError) {
                return back()->withInput()->withErrors(['hierarchy' => $hierarchyError]);
            }

            // Duplicate active president check
            $dupError = VolunteerCadreScopeService::checkDuplicateActivePresident($cadreLevel, $stateId, $districtId, $assemblyId, $mandalId, $panchayatId, $volunteer->id);
            if ($dupError) {
                return back()->withInput()->withErrors(['duplicate' => $dupError]);
            }
        }

        $cadreTitle = $request->input('cadre') ?: Volunteer::cadreLevelToPublicTitle($cadreLevel);
        $locality = $request->input('locality') ?: ($volunteer->jurisdiction_summary ?? 'HQ');

        if ($mappedStatus === 'approved') {
            $assignedVolunteerId = null;
            $assignedLoginId = null;
            $isFirstTimeApproval = empty($volunteer->volunteer_id) || empty($volunteer->volunteer_login_id);
            $plainPassword = null;
            $member = null;

            DB::transaction(function () use ($id, $cadreLevel, $cadreTitle, $locality, $stateId, $districtId, $assemblyId, $mandalId, $panchayatId, $volunteer, $isFirstTimeApproval, &$assignedVolunteerId, &$assignedLoginId, &$plainPassword, &$member) {
                $member = DB::table('memberships')->where('membership_id', $volunteer->membership_id)->first();

                $syncLocation = [
                    'state_id' => $stateId,
                    'district_id' => $districtId,
                    'assembly_segment_id' => $assemblyId,
                    'mandal_id' => $mandalId,
                    'panchayat_id' => $panchayatId,
                    'cadre_level' => $cadreLevel,
                    'geo_mapping_status' => 'verified',
                    'geo_mapping_notes' => 'Approved and verified by Admin on ' . now()->format('Y-m-d H:i:s'),
                ];

                if ($member) {
                    $syncLocation['country'] = $volunteer->country ?: ($member->country ?: 'India');
                    $syncLocation['state'] = $volunteer->state ?: $member->state;
                    $syncLocation['district'] = $volunteer->district ?: $member->district;
                    $syncLocation['assembly_segment'] = $volunteer->assembly_segment ?: $member->assembly_segment;
                    $syncLocation['mandal'] = $volunteer->mandal ?: $member->mandal;
                    $syncLocation['grama_panchayat'] = $volunteer->grama_panchayat ?: $member->grama_panchayat;
                }

                if ($isFirstTimeApproval) {
                    // Generate official unique 6-digit numeric Volunteer ID (e.g. 100001, 100002, ...)
                    $assignedVolunteerId = $volunteer->volunteer_id;
                    if (!$assignedVolunteerId || !preg_match('/^[0-9]{6}$/', trim($assignedVolunteerId))) {
                        $assignedVolunteerId = self::generateNextVolunteerId();
                    }
                    $assignedLoginId = $assignedVolunteerId;

                    $plainPassword = \Illuminate\Support\Str::password(10, true, true, false, false);
                    $encryptedPassword = \Illuminate\Support\Facades\Hash::make($plainPassword);

                    DB::table('volunteers')->where('id', $id)->update(array_merge([
                        'status' => 'approved',
                        'is_active' => true,
                        'cadre' => $cadreTitle,
                        'locality' => $locality,
                        'designation' => $cadreTitle,
                        'volunteer_id' => $assignedVolunteerId,
                        'volunteer_login_id' => $assignedLoginId,
                        'password' => $encryptedPassword,
                        'must_change_password' => true,
                        'credentials_created_at' => now(),
                        'updated_at' => now()
                    ], $syncLocation));
                } else {
                    $assignedVolunteerId = $volunteer->volunteer_id;
                    if (!$assignedVolunteerId || !preg_match('/^[0-9]{6}$/', trim($assignedVolunteerId))) {
                        $assignedVolunteerId = self::generateNextVolunteerId();
                    }
                    $assignedLoginId = $volunteer->volunteer_login_id ?: $assignedVolunteerId;

                    DB::table('volunteers')->where('id', $id)->update(array_merge([
                        'status' => 'approved',
                        'is_active' => true,
                        'cadre' => $cadreTitle,
                        'locality' => $locality,
                        'designation' => $cadreTitle,
                        'updated_at' => now()
                    ], $syncLocation));
                }
            });

            if ($isFirstTimeApproval && $assignedVolunteerId && $plainPassword) {
                // Compile PDF ID Card & Dispatch Welcome Email to Volunteer
                $volunteerData = [
                    'volunteer_name'       => $member->full_name ?? 'Volunteer',
                    'full_name'            => $member->full_name ?? 'Volunteer',
                    'membership_id'        => $volunteer->membership_id,
                    'volunteer_id'         => $assignedVolunteerId,
                    'volunteer_login_id'   => $assignedLoginId,
                    'temporary_password'   => $plainPassword,
                    'plainPassword'        => $plainPassword,
                    'volunteer_login_url'  => route('volunteer.login'),
                    'cadre_title'          => $cadreTitle,
                    'designation'          => $cadreTitle,
                    'jurisdiction'         => $locality,
                    'locality'             => $locality,
                    'blood_group'          => $member->blood_group ?? 'N/A',
                    'photo_path'           => $member->photo_path ?? null,
                ];

                $pdfContent = null;
                try {
                    $pdf = Pdf::loadView('pdf.volunteer_card_pdf', compact('volunteerData'));
                    $pdfContent = $pdf->output();
                } catch (\Throwable $e) {
                    Log::warning('Volunteer PDF generation fallback: ' . $e->getMessage());
                }

                $idempotencyKey = "volunteer_welcome:{$id}";
                $claim = \App\Models\NotificationLog::claim(
                    $idempotencyKey,
                    'volunteer_welcome',
                    \App\Models\Volunteer::class,
                    $id,
                    'email',
                    $volunteer->email,
                    $volunteer->phone,
                    'Welcome to ABVHPS Volunteer Service – Volunteer ID ' . $assignedVolunteerId,
                    'Volunteer welcome credentials dispatched for ID: ' . $assignedVolunteerId
                );

                if ($claim) {
                    try {
                        Mail::to($volunteer->email)->send(new VolunteerWelcomeMail($volunteerData, $pdfContent));
                        $claim->markSent('Welcome to ABVHPS Volunteer Service – Volunteer ID ' . $assignedVolunteerId, 'Volunteer welcome credentials dispatched for ID: ' . $assignedVolunteerId);
                        DB::table('volunteers')->where('id', $id)->update(['welcome_email_sent_at' => now()]);
                    } catch (\Throwable $e) {
                        Log::error('Volunteer welcome email dispatch error: ' . $e->getMessage());
                        $claim->markFailed($e->getMessage());
                    }
                }
            } elseif (!$isFirstTimeApproval && $volunteer->email && filter_var($volunteer->email, FILTER_VALIDATE_EMAIL)) {
                // Check if assignment actually changed before sending notification
                $oldAssignment = [
                    'cadre_level'         => (string)($volunteer->cadre_level ?? 'volunteer'),
                    'state_id'            => (string)($volunteer->state_id ?? ''),
                    'district_id'         => (string)($volunteer->district_id ?? ''),
                    'assembly_segment_id' => (string)($volunteer->assembly_segment_id ?? ''),
                    'mandal_id'           => (string)($volunteer->mandal_id ?? ''),
                    'panchayat_id'        => (string)($volunteer->panchayat_id ?? ''),
                ];
                $newAssignment = [
                    'cadre_level'         => (string)($cadreLevel ?? 'volunteer'),
                    'state_id'            => (string)($stateId ?? ''),
                    'district_id'         => (string)($districtId ?? ''),
                    'assembly_segment_id' => (string)($assemblyId ?? ''),
                    'mandal_id'           => (string)($mandalId ?? ''),
                    'panchayat_id'        => (string)($panchayatId ?? ''),
                ];

                if ($oldAssignment !== $newAssignment) {
                    // Cadre/Jurisdiction Reassignment Email (NO temporary password)
                    $volunteerData = [
                        'volunteer_name'       => $member->full_name ?? ($volunteer->full_name ?? 'Volunteer'),
                        'full_name'            => $member->full_name ?? ($volunteer->full_name ?? 'Volunteer'),
                        'membership_id'        => $volunteer->membership_id,
                        'volunteer_id'         => $assignedVolunteerId,
                        'volunteer_login_id'   => $assignedLoginId,
                        'volunteer_login_url'  => route('volunteer.login'),
                        'cadre_title'          => $cadreTitle,
                        'jurisdiction'         => $locality,
                        'effective_date'       => now('Asia/Kolkata')->format('d-M-Y'),
                    ];

                    $assignmentHash = md5(implode('|', $newAssignment));
                    $idempotencyKey = "volunteer_assignment_updated:{$id}:{$assignmentHash}";

                    $claim = \App\Models\NotificationLog::claim(
                        $idempotencyKey,
                        'volunteer_assignment_updated',
                        \App\Models\Volunteer::class,
                        $id,
                        'email',
                        $volunteer->email,
                        $volunteer->phone,
                        'ABVHPS Volunteer Assignment Updated – Volunteer ID ' . $assignedVolunteerId,
                        'Volunteer assignment update notification sent for ID: ' . $assignedVolunteerId
                    );

                    if ($claim) {
                        try {
                            Mail::to($volunteer->email)->send(new VolunteerAssignmentUpdatedMail($volunteerData));
                            $claim->markSent('ABVHPS Volunteer Assignment Updated – Volunteer ID ' . $assignedVolunteerId, 'Volunteer assignment update notification sent for ID: ' . $assignedVolunteerId);
                        } catch (\Throwable $e) {
                            Log::error('Volunteer assignment updated email error: ' . $e->getMessage());
                            $claim->markFailed($e->getMessage());
                        }
                    }
                }
            }

            \App\Services\AuditLogger::log($isFirstTimeApproval ? 'VOLUNTEER_APPROVED' : 'VOLUNTEER_CADRE_UPDATED', 'Volunteer', (string)$assignedVolunteerId, [
                'cadre_level' => $cadreLevel,
                'cadre' => $cadreTitle,
                'locality' => $locality,
                'state_id' => $stateId,
                'district_id' => $districtId,
                'mandal_id' => $mandalId,
                'panchayat_id' => $panchayatId,
                'volunteer_id' => $assignedVolunteerId
            ]);

            return redirect('/admin/volunteer/view-card/' . $assignedVolunteerId)
                ->with('success', 'Volunteer status verified & approved successfully as ' . $cadreTitle . ' (Login ID #' . $assignedLoginId . ')!');
        }

        $oldStatus = $volunteer->status;

        // Processing Rejected or Pending states
        DB::table('volunteers')->where('id', $id)->update([
            'status' => $mappedStatus,
            'is_active' => ($mappedStatus === 'approved'),
            'updated_at' => now()
        ]);

        $member = DB::table('memberships')->where('membership_id', $volunteer->membership_id)->first();
        $volunteerEmail = $volunteer->email;

        if (!empty($volunteerEmail) && filter_var($volunteerEmail, FILTER_VALIDATE_EMAIL)) {
            $volunteerData = [
                'volunteer_name'    => $member->full_name ?? ($volunteer->full_name ?? 'Volunteer'),
                'full_name'         => $member->full_name ?? ($volunteer->full_name ?? 'Volunteer'),
                'membership_id'     => $volunteer->membership_id,
                'status_updated_at' => now('Asia/Kolkata')->format('d-M-Y'),
            ];

            if ($mappedStatus === 'pending' && $oldStatus !== 'pending') {
                $transitionKey = "volunteer_pending:{$id}:" . md5("{$oldStatus}_to_pending_" . ($volunteer->updated_at ?? ''));
                $claim = \App\Models\NotificationLog::claim(
                    $transitionKey,
                    'volunteer_status_pending',
                    \App\Models\Volunteer::class,
                    $id,
                    'email',
                    $volunteerEmail,
                    $volunteer->phone,
                    'ABVHPS Volunteer Application Status Update – Pending Review',
                    'Volunteer pending status notice sent for membership: ' . $volunteer->membership_id
                );

                if ($claim) {
                    try {
                        Mail::to($volunteerEmail)->send(new VolunteerPendingStatusMail($volunteerData));
                        $claim->markSent('ABVHPS Volunteer Application Status Update – Pending Review', 'Volunteer pending status notice sent for membership: ' . $volunteer->membership_id);
                    } catch (\Throwable $e) {
                        Log::error('Volunteer pending status email error: ' . $e->getMessage());
                        $claim->markFailed($e->getMessage());
                    }
                }
            } elseif ($mappedStatus === 'rejected' && $oldStatus !== 'rejected') {
                $transitionKey = "volunteer_rejected:{$id}:" . md5("{$oldStatus}_to_rejected_" . ($volunteer->updated_at ?? ''));
                $claim = \App\Models\NotificationLog::claim(
                    $transitionKey,
                    'volunteer_status_rejected',
                    \App\Models\Volunteer::class,
                    $id,
                    'email',
                    $volunteerEmail,
                    $volunteer->phone,
                    'ABVHPS Volunteer Application Status Update',
                    'Volunteer rejection notice sent for membership: ' . $volunteer->membership_id
                );

                if ($claim) {
                    try {
                        Mail::to($volunteerEmail)->send(new VolunteerRejectedStatusMail($volunteerData));
                        $claim->markSent('ABVHPS Volunteer Application Status Update', 'Volunteer rejection notice sent for membership: ' . $volunteer->membership_id);
                    } catch (\Throwable $e) {
                        Log::error('Volunteer rejected status email error: ' . $e->getMessage());
                        $claim->markFailed($e->getMessage());
                    }
                }
            }
        }

        \App\Services\AuditLogger::log($mappedStatus === 'rejected' ? 'VOLUNTEER_REJECTED' : 'VOLUNTEER_PENDING', 'Volunteer', (string)$volunteer->id, [
            'status' => $mappedStatus
        ]);

        $statusText = $mappedStatus === 'rejected' ? 'rejected' : 'marked as pending';
        return redirect()->route('admin.volunteers.index')
            ->with('success', 'Volunteer #' . $volunteer->id . ' status has been ' . $statusText . ' successfully.');
    }

    /**
     * Admin Resend Login Credentials Workflow
     */
    public function resendCredentials($id)
    {
        $volunteer = DB::table('volunteers')->where('id', $id)->first();
        if (!$volunteer || $volunteer->status !== 'approved') {
            return redirect()->back()->with('error', 'Only approved volunteers can receive login credentials.');
        }

        $member = DB::table('memberships')->where('membership_id', $volunteer->membership_id)->first();

        // Ensure 6-digit official Volunteer ID exists
        $officialId = $volunteer->volunteer_id;
        if (!$officialId || !preg_match('/^[0-9]{6}$/', trim($officialId))) {
            $officialId = self::generateNextVolunteerId();
        }
        $loginId = $officialId;

        // Generate fresh cryptographically secure random temporary password
        $plainPassword = \Illuminate\Support\Str::password(10, true, true, false, false);
        $encryptedPassword = \Illuminate\Support\Facades\Hash::make($plainPassword);

        DB::table('volunteers')->where('id', $id)->update([
            'volunteer_login_id' => $loginId,
            'volunteer_id' => $officialId,
            'password' => $encryptedPassword,
            'must_change_password' => true,
            'credentials_created_at' => now(),
            'updated_at' => now()
        ]);

        \App\Services\AuditLogger::log('VOLUNTEER_CREDENTIALS_RESET', 'Volunteer', (string)$officialId, [
            'volunteer_id' => $officialId,
            'email' => $volunteer->email
        ]);

        $volunteerData = [
            'volunteer_name'       => $member->full_name ?? 'Volunteer',
            'full_name'            => $member->full_name ?? 'Volunteer',
            'membership_id'        => $volunteer->membership_id,
            'volunteer_id'         => $officialId,
            'volunteer_login_id'   => $loginId,
            'temporary_password'   => $plainPassword,
            'plainPassword'        => $plainPassword,
            'volunteer_login_url'  => route('volunteer.login'),
            'cadre_title'          => $volunteer->cadre ?? ($volunteer->designation ?? 'Volunteer'),
            'designation'          => $volunteer->cadre ?? ($volunteer->designation ?? 'Volunteer'),
            'jurisdiction'         => $volunteer->locality ?? 'HQ',
            'locality'             => $volunteer->locality ?? 'HQ',
            'blood_group'          => $member->blood_group ?? 'N/A',
            'photo_path'           => $member->photo_path ?? null,
        ];

        $pdfContent = null;
        try {
            $pdf = Pdf::loadView('pdf.volunteer_card_pdf', compact('volunteerData'));
            $pdfContent = $pdf->output();
        } catch (\Throwable $e) {
            Log::warning('Volunteer PDF generation fallback: ' . $e->getMessage());
        }

        $idempotencyKey = "volunteer_welcome_resend:{$volunteer->id}:" . now()->timestamp;
        $claim = \App\Models\NotificationLog::claim(
            $idempotencyKey,
            'volunteer_welcome_resend',
            \App\Models\Volunteer::class,
            $volunteer->id,
            'email',
            $volunteer->email,
            $volunteer->phone,
            'Welcome to ABVHPS Volunteer Service – Volunteer ID ' . $officialId,
            'Volunteer credentials resent for ID: ' . $officialId
        );

        $mailStatus = 'failed';
        if ($claim) {
            try {
                Mail::to($volunteer->email)->send(new VolunteerWelcomeMail($volunteerData, $pdfContent));
                $mailStatus = config('mail.default') === 'log' ? 'logged' : 'sent';
                $claim->markSent('Welcome to ABVHPS Volunteer Service – Volunteer ID ' . $officialId, 'Volunteer credentials resent for ID: ' . $officialId);
                DB::table('volunteers')->where('id', $id)->update(['welcome_email_sent_at' => now()]);
            } catch (\Throwable $e) {
                Log::error('Volunteer resend email error: ' . $e->getMessage());
                $claim->markFailed($e->getMessage());
            }
        }

        $statusMsg = $mailStatus === 'logged' ? ' (Written to storage/logs/laravel.log)' : '';
        return redirect()->back()->with('success', "Login credentials for Volunteer #{$officialId} reset and welcome email {$mailStatus}{$statusMsg}.");
    }

    /**
     * Generate the next official unique 6-digit numeric Volunteer ID (e.g. 100001, 100002, ...).
     * Generate a unique, non-sequential randomized 6-digit numeric Volunteer ID (e.g. 583214, 741905, 216438).
     * Strictly satisfies ^[0-9]{6}$ and checks against all existing volunteer_id and volunteer_login_id records.
     */
    public static function generateNextVolunteerId(): string
    {
        $maxAttempts = 50;
        $attempt = 0;

        do {
            $candidateNumber = random_int(100000, 999999);
            $formatted = (string) $candidateNumber;
            $attempt++;

            $exists = DB::table('volunteers')
                ->where('volunteer_id', $formatted)
                ->orWhere('volunteer_login_id', $formatted)
                ->exists();

            if (!$exists) {
                return $formatted;
            }
        } while ($attempt < $maxAttempts);

        // Deterministic fallback if collision space is crowded
        throw new \RuntimeException("Unable to allocate a unique 6-digit numeric Volunteer ID after {$maxAttempts} attempts.");
    }

    public static function generateNextVolunteerLoginId(): string
    {
        return self::generateNextVolunteerId();
    }

    // 11. View Read-Only Volunteer Profile Dossier
    public function viewProfile($id)
    {
        $volunteer = DB::table('volunteers')
            ->leftJoin('memberships', 'volunteers.membership_id', '=', 'memberships.membership_id')
            ->select(
                'volunteers.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.aadhaar_number as member_aadhaar_number',
                'memberships.blood_group as member_blood_group',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.grama_panchayat as member_grama_panchayat',
                'memberships.state as member_state',
                'memberships.pincode as member_pincode',
                'memberships.father_or_husband_name as member_father_name'
            )
            ->where('volunteers.id', $id)
            ->first();

        if (!$volunteer) {
            abort(404, 'Volunteer record not found');
        }

        return view('admin.volunteer_profile_view', compact('volunteer'));
    }

    // 12. Permanent Purge Removal of Volunteer from System
    public function deleteVolunteer($id)
    {
        $volunteer = DB::table('volunteers')->where('id', $id)->first();
        if (!$volunteer) {
            abort(404, 'Volunteer not found');
        }

        // Remove uploaded files if exist
        if ($volunteer->document_declaration_path && Storage::disk('public')->exists($volunteer->document_declaration_path)) {
            Storage::disk('public')->delete($volunteer->document_declaration_path);
        }
        if ($volunteer->document_voter_path && Storage::disk('public')->exists($volunteer->document_voter_path)) {
            Storage::disk('public')->delete($volunteer->document_voter_path);
        }
        if ($volunteer->document_bank_path && Storage::disk('public')->exists($volunteer->document_bank_path)) {
            Storage::disk('public')->delete($volunteer->document_bank_path);
        }

        DB::table('volunteers')->where('id', $id)->delete();

        return redirect()->route('admin.volunteers.index')
            ->with('success', '🗑️ Volunteer record #' . $id . ' permanently deleted from roster.');
    }

    // Backwards-compatible aliases
    public function editForm($id)
    {
        return $this->editFull($id);
    }

    public function update(Request $request, $id)
    {
        return $this->cadreUpdate($request, $id);
    }

    public function updateVolunteerStatus(Request $request)
    {
        $id = $request->input('id');
        return $this->cadreUpdate($request, $id);
    }

    public function approveVolunteer(Request $request)
    {
        $id = $request->input('id');
        return $this->cadreUpdate($request, $id);
    }

    // 8. Render Vertical Volunteer ID Card Screen showing mapped metrics
    public function viewVolunteerCard($volunteerIdCode)
    {
        // Fetching the volunteer record using the official Volunteer ID (e.g. RS0001)
        $volunteer = DB::table('volunteers')->where('volunteer_id', $volunteerIdCode)->where('status', 'approved')->first();

        if (!$volunteer) {
            return redirect('/admin/volunteers')->with('error', 'Approved volunteer record metrics not found.');
        }

        // Fetching profile fields from matching membership parent row sequence
        $member = DB::table('memberships')->where('membership_id', $volunteer->membership_id)->first();

        // Building complete geography string details for the address wrap layer section
        $completeAddress = ($member->grama_panchayat ?? 'Grama') . ', ' . ($member->mandal ?? 'Mandal') . ', ' . ($member->assembly_segment ?? 'Badvel') . ', ' . ($member->district ?? 'District') . ', ' . ($member->state ?? 'State') . ', ' . ($member->country ?? 'India') . ' - ' . ($member->pincode ?? 'Pincode');

        $volunteerData = [
            'full_name' => $member->full_name ?? 'Volunteer',
            'volunteer_id' => $volunteer->volunteer_id,
            'clean_volunteer_id' => $volunteer->volunteer_id,
            'formatted_volunteer_id' => $volunteer->volunteer_id,
            'designation' => $volunteer->designation ?? ($volunteer->cadre ?? 'Volunteer'),
            'locality' => $volunteer->locality ?? 'HQ',
            'blood_group' => $member->blood_group ?? 'N/A',
            'phone' => $volunteer->phone,
            'membership_id' => $volunteer->membership_id,
            'complete_address' => $completeAddress,
            'photo_path' => $member->photo_path ?? null
        ];

        return view('volunteer_card_view', compact('volunteerData'));
    }
    // 9. Show Official Volunteer & Presidents Login Page Screen
    public function showLoginForm()
    {
        return view('volunteer_login');
    }
        
    // 10. Process Secure Credentials Delegated to VolunteerAuthController
    public function processLogin(Request $request)
    {
        return app(VolunteerAuthController::class)->login($request);
    }


    // 11. Clear session cache during active sign out loops
    public function logoutVolunteer()
    {
        session()->forget(['auth_volunteer_db_id', 'auth_volunteer_code', 'auth_volunteer_role', 'auth_volunteer_locality']);
        return redirect('/volunteer/login')->with('success', 'Logged out from central pipeline desk successfully.');
    }
    // 12. Show Village / Panchayat President Dashboard Layout with Live Analytics Count Cards
    public function showVillageDashboard()
    {
        $volunteer = Auth::guard('volunteer')->user();
        $isSessionAuth = session('auth_volunteer_role') === 'village_president';

        if (!$isSessionAuth && (!$volunteer || !VolunteerCadreScopeService::isVerifiedCadre($volunteer, 'panchayat_president'))) {
            abort(403, 'Unauthorized dashboard access slot.');
        }

        $volunteerLocality = session('auth_volunteer_locality') ?: ($volunteer?->resolved_grama_panchayat ?? 'Panchayat');

        $totalMembersCount = $volunteer
            ? VolunteerCadreScopeService::membersFor($volunteer)->where('payment_status', 'success')->count()
            : DB::table('memberships')->where('payment_status', 'success')->count();

        $totalBenefitsCount = DB::table('seva_orders_history')->count();
        $groupEvents = DB::table('group_events_history')->where('volunteer_id', session('auth_volunteer_code') ?: $volunteer?->volunteer_id)->get();
        $subordinateUnits = $volunteer ? VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        return view('volunteer_village_dashboard', compact('totalMembersCount', 'totalBenefitsCount', 'groupEvents', 'subordinateUnits', 'volunteer'));
    }

    // 13. Fetch Member Profile Records matching the 12-Digit Input Key ID
    public function searchMember(Request $request)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $isSessionAuth = session('auth_volunteer_role') === 'village_president';

        if (!$isSessionAuth && (!$volunteer || !VolunteerCadreScopeService::isVerifiedCadre($volunteer, 'panchayat_president'))) {
            abort(403, 'Unauthorized entry.');
        }

        $request->validate(['member_id' => 'required|digits:12']);
        $memberId = $request->input('member_id');

        $query = $volunteer ? VolunteerCadreScopeService::membersFor($volunteer) : DB::table('memberships');
        $searchedMember = $query->where('membership_id', $memberId)->first();

        if (!$searchedMember) {
            return redirect('/volunteer/dashboard/village')->with('error', 'Active Membership ID record metrics not found on server within your jurisdiction.');
        }

        $totalMembersCount = $volunteer
            ? VolunteerCadreScopeService::membersFor($volunteer)->where('payment_status', 'success')->count()
            : DB::table('memberships')->where('payment_status', 'success')->count();
        $totalBenefitsCount = DB::table('seva_orders_history')->count();
        $groupEvents = DB::table('group_events_history')->where('volunteer_id', session('auth_volunteer_code') ?: $volunteer?->volunteer_id)->get();
        $subordinateUnits = $volunteer ? VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        return view('volunteer_village_dashboard', compact('searchedMember', 'totalMembersCount', 'totalBenefitsCount', 'groupEvents', 'subordinateUnits', 'volunteer'));
    }

    // 14. Core Image 1KB-2KB Compression Engine and Seva Delivery History Record Function
    public function deliverSeva(Request $request)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $isSessionAuth = session('auth_volunteer_role') === 'village_president';

        if (!$isSessionAuth && (!$volunteer || !VolunteerCadreScopeService::isVerifiedCadre($volunteer, 'panchayat_president'))) {
            abort(403, 'Unauthorized execution rules.');
        }

        $request->validate([
            'member_id' => 'required|digits:12',
            'service_type' => 'required|string|max:255',
            'proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $memberId = $request->input('member_id');
        $serviceType = $request->input('service_type');
        $volunteerCode = session('auth_volunteer_code') ?: $volunteer?->volunteer_id;
        $volunteerRole = session('auth_volunteer_role') ?: ($volunteer?->cadre_level ?? 'panchayat_president');

        $uploadedFile = $request->file('proof_photo');
        
        $targetWidth = 100;
        $targetHeight = 100;
        $compressedImage = imagecreatetruecolor($targetWidth, $targetHeight);

        $sourceType = $uploadedFile->getClientOriginalExtension();
        if (str_contains(strtolower($sourceType), 'png')) {
            $sourceImage = imagecreatefrompng($uploadedFile->getRealPath());
        } else {
            $sourceImage = imagecreatefromjpeg($uploadedFile->getRealPath());
        }

        imagecopyresampled($compressedImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($sourceImage), imagesy($sourceImage));

        $fileName = 'seva_proof_' . time() . '_' . $memberId . '.jpg';
        $storageDir = storage_path('app/public/seva_proofs');
        
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        $finalTargetFilePath = $storageDir . '/' . $fileName;

        imagejpeg($compressedImage, $finalTargetFilePath, 20);

        imagedestroy($sourceImage);
        imagedestroy($compressedImage);

        $savedDatabasePath = 'seva_proofs/' . $fileName;

        DB::table('seva_orders_history')->insert([
            'member_id' => $memberId,
            'volunteer_id' => $volunteerCode,
            'volunteer_role' => $volunteerRole,
            'service_type' => $serviceType,
            'proof_photo_path' => $savedDatabasePath,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect('/volunteer/dashboard/village')->with('success', 'Seva delivery recorded with 1KB digital photo evidence history proof successfully!');
    }

    // 15. Show Mandal President Dashboard with Anti-Fraud Audit and Subordinate Directory
    public function showMandalDashboard(Request $request)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $isSessionAuth = session('auth_volunteer_role') === 'mandal_president';

        if (!$isSessionAuth && (!$volunteer || !VolunteerCadreScopeService::isVerifiedCadre($volunteer, 'mandal_president'))) {
            abort(403, 'Unauthorized dashboard access slot.');
        }

        $mandalLocality = session('auth_volunteer_locality') ?: ($volunteer?->resolved_mandal ?? 'Mandal');

        $totalMandalMembers = $volunteer
            ? VolunteerCadreScopeService::membersFor($volunteer)->where('payment_status', 'success')->count()
            : DB::table('memberships')->where('payment_status', 'success')->count();

        $totalPanchayatsCount = $volunteer
            ? VolunteerCadreScopeService::panchayatsFor($volunteer)->count()
            : DB::table('memberships')->where('payment_status', 'success')->distinct('grama_panchayat')->count('grama_panchayat');

        $totalMandalBenefits = DB::table('seva_orders_history')->count();
        $subordinateUnits = $volunteer ? VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        $villagePresidents = DB::table('volunteers')->where('cadre_level', 'panchayat_president')->orWhere('role', 'village_president')->get();
        $mandalMembers = $volunteer ? VolunteerCadreScopeService::membersFor($volunteer)->where('payment_status', 'success')->get() : DB::table('memberships')->where('payment_status', 'success')->get();

        $mandalGroupEvents = DB::table('group_events_history')
            ->where('mandal', $mandalLocality)
            ->orderBy('created_at', 'desc')
            ->get();

        $searchedAuditMember = null;
        $sevaHistoryRecords = collect();

        if ($request->has('audit_member_id')) {
            $request->validate(['audit_member_id' => 'required|digits:12']);
            $auditMemberId = $request->input('audit_member_id');
            $searchedAuditMember = DB::table('memberships')->where('membership_id', $auditMemberId)->first();
            if ($searchedAuditMember) {
                $sevaHistoryRecords = DB::table('seva_orders_history')
                    ->where('member_id', $auditMemberId)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        return view('volunteer_mandal_dashboard', compact(
            'totalMandalMembers',
            'totalPanchayatsCount',
            'totalMandalBenefits',
            'villagePresidents',
            'mandalMembers',
            'searchedAuditMember',
            'sevaHistoryRecords',
            'mandalGroupEvents',
            'subordinateUnits',
            'volunteer'
        ));
    }

    // 16. Show Assembly Segment President Dashboard with Subordinate Directory
    public function showAssemblyDashboard(Request $request)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $isSessionAuth = session('auth_volunteer_role') === 'assembly_president';

        if (!$isSessionAuth && (!$volunteer || !VolunteerCadreScopeService::isVerifiedCadre($volunteer, 'assembly_president'))) {
            abort(403, 'Unauthorized dashboard access slot.');
        }

        $assemblyLocality = session('auth_volunteer_locality') ?: ($volunteer?->resolved_assembly_segment ?? 'Assembly Segment');

        $totalAssemblyMembers = $volunteer
            ? VolunteerCadreScopeService::membersFor($volunteer)->where('payment_status', 'success')->count()
            : DB::table('memberships')->where('payment_status', 'success')->count();

        $totalAssemblyBenefits = DB::table('seva_orders_history')->count();
        $totalAssemblyMandals = $volunteer
            ? VolunteerCadreScopeService::mandalsFor($volunteer)->count()
            : 7;

        $mandalPresidents = DB::table('volunteers')
            ->where('cadre_level', 'mandal_president')
            ->orWhere('role', 'mandal_president')
            ->get();

        $subordinateUnits = $volunteer ? VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        $assemblyGroupEvents = DB::table('group_events_history')
            ->orderBy('created_at', 'desc')
            ->get();

        $searchedAuditMember = null;
        $sevaHistoryRecords = collect();

        if ($request->has('audit_member_id')) {
            $request->validate(['audit_member_id' => 'required|digits:12']);
            $auditMemberId = $request->input('audit_member_id');

            $searchedAuditMember = DB::table('memberships')->where('membership_id', $auditMemberId)->first();

            if ($searchedAuditMember) {
                $sevaHistoryRecords = DB::table('seva_orders_history')
                    ->where('member_id', $auditMemberId)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        return view('volunteer_assembly_dashboard', compact(
            'totalAssemblyMembers',
            'totalAssemblyBenefits',
            'totalAssemblyMandals',
            'mandalPresidents',
            'searchedAuditMember',
            'sevaHistoryRecords',
            'assemblyGroupEvents',
            'subordinateUnits',
            'volunteer'
        ));
    }

    // 17. Show District President Dashboard with Subordinate Directory
    public function showDistrictDashboard(Request $request)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $isSessionAuth = session('auth_volunteer_role') === 'district_president';

        if (!$isSessionAuth && (!$volunteer || !VolunteerCadreScopeService::isVerifiedCadre($volunteer, 'district_president'))) {
            abort(403, 'Unauthorized dashboard access slot.');
        }

        $districtLocality = session('auth_volunteer_locality') ?: ($volunteer?->resolved_district ?? 'District');

        $totalDistrictMembers = $volunteer
            ? VolunteerCadreScopeService::membersFor($volunteer)->where('payment_status', 'success')->count()
            : DB::table('memberships')->where('payment_status', 'success')->count();

        $totalDistrictBenefits = DB::table('seva_orders_history')->count();
        $totalDistrictAssemblies = $volunteer
            ? VolunteerCadreScopeService::assemblySegmentsFor($volunteer)->count()
            : 10;

        $assemblyPresidents = DB::table('volunteers')
            ->where('cadre_level', 'assembly_president')
            ->orWhere('role', 'assembly_president')
            ->get();

        $subordinateUnits = $volunteer ? VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        $searchedAuditMember = null;
        $sevaHistoryRecords = collect();

        if ($request->has('audit_member_id')) {
            $request->validate(['audit_member_id' => 'required|digits:12']);
            $auditMemberId = $request->input('audit_member_id');

            $searchedAuditMember = DB::table('memberships')->where('membership_id', $auditMemberId)->first();

            if ($searchedAuditMember) {
                $sevaHistoryRecords = DB::table('seva_orders_history')
                    ->where('member_id', $auditMemberId)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        return view('volunteer_district_dashboard', compact(
            'totalDistrictMembers',
            'totalDistrictBenefits',
            'totalDistrictAssemblies',
            'assemblyPresidents',
            'searchedAuditMember',
            'sevaHistoryRecords',
            'subordinateUnits',
            'volunteer'
        ));
    }

    // 17b. Show State President Dashboard with Subordinate Directory
    public function showStateDashboard(Request $request)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $isSessionAuth = session('auth_volunteer_role') === 'state_president';

        if (!$isSessionAuth && (!$volunteer || !VolunteerCadreScopeService::isVerifiedCadre($volunteer, 'state_president'))) {
            abort(403, 'Unauthorized dashboard access slot.');
        }

        $totalStateMembers = $volunteer
            ? VolunteerCadreScopeService::membersFor($volunteer)->where('payment_status', 'success')->count()
            : DB::table('memberships')->where('payment_status', 'success')->count();

        $totalDistrictsCount = $volunteer
            ? VolunteerCadreScopeService::districtsFor($volunteer)->count()
            : GeoDistrict::count();

        $subordinateUnits = $volunteer ? VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        return view('volunteer_global_dashboard', [
            'assignedRole' => 'state_president',
            'assignedLocality' => $volunteer?->resolved_state ?? 'State',
            'globalMembersCount' => $totalStateMembers,
            'globalBenefitsCount' => DB::table('seva_orders_history')->count(),
            'totalActiveVolunteersCount' => Volunteer::where('state_id', $volunteer?->state_id)->count(),
            'searchedAuditMember' => null,
            'sevaHistoryRecords' => collect(),
            'breakdownData' => collect(),
            'breakdownHeader' => 'District',
            'subordinateUnits' => $subordinateUnits,
            'volunteer' => $volunteer,
        ]);
    }

    // 18. Show Global / National Master Dashboard with Dynamic Pipeline Analytics
    public function showGlobalDashboard(Request $request)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $assignedRole = session('auth_volunteer_role') ?: $volunteer?->cadre_level;
        $assignedLocality = session('auth_volunteer_locality') ?: ($volunteer?->jurisdiction_summary ?? 'All India');

        $allowedRoles = ['district_president', 'state_president', 'national_president', 'international_president', 'support_team'];

        $isAuthorized = ($volunteer && VolunteerCadreScopeService::isVerifiedCadre($volunteer, 'national_president'))
            || in_array($assignedRole, $allowedRoles, true);

        if (!$isAuthorized) {
            abort(403, 'Unauthorized global dashboard access slot.');
        }

        $globalMembersCount = DB::table('memberships')->where('payment_status', 'success')->count();
        $globalBenefitsCount = DB::table('seva_orders_history')->count();
        $totalActiveVolunteersCount = DB::table('volunteers')->count();
        $subordinateUnits = $volunteer ? VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        $breakdownData = collect();
        $breakdownHeader = 'State';

        if ($assignedRole === 'district_president' || $assignedRole === 'support_team') {
            $breakdownHeader = 'Assembly Segment';
            $breakdownData = DB::table('memberships')
                ->select('assembly_segment as zone_name', DB::raw('count(*) as members_count'))
                ->where('payment_status', 'success')
                ->groupBy('assembly_segment')
                ->get();
        } elseif ($assignedRole === 'state_president') {
            $breakdownHeader = 'District';
            $breakdownData = DB::table('memberships')
                ->select('district as zone_name', DB::raw('count(*) as members_count'))
                ->where('payment_status', 'success')
                ->groupBy('district')
                ->get();
        } elseif ($assignedRole === 'national_president') {
            $breakdownHeader = 'State';
            $breakdownData = DB::table('memberships')
                ->select('state as zone_name', DB::raw('count(*) as members_count'))
                ->where('payment_status', 'success')
                ->groupBy('state')
                ->get();
        } elseif ($assignedRole === 'international_president') {
            $breakdownHeader = 'Country';
            $breakdownData = DB::table('memberships')
                ->select('country as zone_name', DB::raw('count(*) as members_count'))
                ->where('payment_status', 'success')
                ->groupBy('country')
                ->get();
        }

        $searchedAuditMember = null;
        $sevaHistoryRecords = collect();

        if ($request->has('audit_member_id')) {
            $request->validate(['audit_member_id' => 'required|digits:12']);
            $auditMemberId = $request->input('audit_member_id');
            $searchedAuditMember = DB::table('memberships')->where('membership_id', $auditMemberId)->first();
            if ($searchedAuditMember) {
                $sevaHistoryRecords = DB::table('seva_orders_history')
                    ->where('member_id', $auditMemberId)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        return view('volunteer_global_dashboard', compact(
            'assignedRole', 'assignedLocality', 'globalMembersCount', 
            'globalBenefitsCount', 'totalActiveVolunteersCount', 
            'searchedAuditMember', 'sevaHistoryRecords',
            'breakdownData', 'breakdownHeader', 'subordinateUnits', 'volunteer'
        ));
    }
    // 19. Village Dashboard Base Loader handling Group Events Display metrics
    public function showVillageDashboardWithGallery()
    {
        if (session('auth_volunteer_role') !== 'village_president') {
            return redirect('/volunteer/login')->with('error', 'Unauthorized access.');
        }

        $totalMembersCount = DB::table('memberships')->where('payment_status', 'success')->count();
        $totalBenefitsCount = DB::table('seva_orders_history')->count();
        
        // Fetching entire published community mass event records to display inside table gallery
        $groupEvents = DB::table('group_events_history')
            ->where('volunteer_id', session('auth_volunteer_code'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('volunteer_village_dashboard', compact('totalMembersCount', 'totalBenefitsCount', 'groupEvents'));
    }

    // 20. Core Group Image 30KB-50KB Matrix Compression Engine and Database Publishing
    public function uploadGroupEvent(Request $request)
    {
        if (session('auth_volunteer_role') !== 'village_president') {
            return redirect('/volunteer/login')->with('error', 'Unauthorized execution.');
        }

        $request->validate([
            'event_title' => 'required|string|max:255',
            'group_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $eventTitle = $request->input('event_title');
        $volunteerCode = session('auth_volunteer_code');
        $volunteerRole = session('auth_volunteer_role');
        $volunteerLocality = session('auth_volunteer_locality');

        $uploadedFile = $request->file('group_photo');
        
        // --- NATIVE GROUP IMAGE COMPRESSION LOGIC TO FORCE 30KB-50KB SIZE ---
        // Setting crisp landscape resolution layout dimensions to keep faces visible clearly
        $targetWidth = 600;
        $targetHeight = 400;
        $compressedImage = imagecreatetruecolor($targetWidth, $targetHeight);

        $sourceType = $uploadedFile->getClientOriginalExtension();
        if (str_contains(strtolower($sourceType), 'png')) {
            $sourceImage = imagecreatefrompng($uploadedFile->getRealPath());
        } else {
            $sourceImage = imagecreatefromjpeg($uploadedFile->getRealPath());
        }

        // Resizing raw pixels into standard 600x400 landscape album layout block
        imagecopyresampled($compressedImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($sourceImage), imagesy($sourceImage));

        $fileName = 'group_event_' . time() . '_' . rand(10,99) . '.jpg';
        $storageDir = storage_path('app/public/group_events');
        
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        $finalTargetFilePath = $storageDir . '/' . $fileName;

        // Saving file data down with compressed quality ratio 50% to achieve pure 30KB-50KB targets
        imagejpeg($compressedImage, $finalTargetFilePath, 50);

        imagedestroy($sourceImage);
        imagedestroy($compressedImage);

        $savedDatabasePath = 'group_events/' . $fileName;

        // Inserting the data rows into group events table safely inside database row logs
        DB::table('group_events_history')->insert([
            'volunteer_id' => $volunteerCode,
            'volunteer_role' => $volunteerRole,
            'mandal' => 'PORUMAMILLA', // Current session mandal scope layout
            'grama_panchayat' => $volunteerLocality,
            'event_title' => $eventTitle,
            'group_photo_path' => $savedDatabasePath,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect('/volunteer/dashboard/village')->with('success', 'Mass group service event published with 30KB optimized photo evidence successfully!');
    }


}
