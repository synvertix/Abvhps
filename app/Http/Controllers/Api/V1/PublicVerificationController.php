<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiMediaHelper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PublicVerificationController extends Controller
{
    /**
     * Public QR / Master ID live verification endpoint.
     * Supports: membership, volunteer, rudrasena, exam, organic-farmers, kala-brundham, grama-seva-dal.
     */
    public function verify(string $type, string $id): JsonResponse
    {
        $cleanId = trim($id);

        return match ($type) {
            'membership'      => $this->verifyMembership($cleanId),
            'volunteer'       => $this->verifyVolunteer($cleanId),
            'rudrasena'       => $this->verifyRudrasena($cleanId),
            'exam'            => $this->verifyExam($cleanId),
            'organic-farmers' => $this->verifyOrganicFarmers($cleanId),
            'kala-brundham', 'kala-brundam' => $this->verifyKalaBrundam($cleanId),
            'grama-seva-dal'  => $this->verifyGramaSevaDal($cleanId),
            default           => response()->json([
                'success'  => false,
                'is_valid' => false,
                'message'  => 'Invalid verification entity type requested.',
            ], 400),
        };
    }

    private function verifyMembership(string $cleanId): JsonResponse
    {
        $member = DB::table('memberships')->where('membership_id', $cleanId)->first();

        if (!$member) {
            return response()->json([
                'success'     => false,
                'is_valid'    => false,
                'entity_type' => 'Membership',
                'searched_id' => $cleanId,
                'message'     => 'No active membership record was found with this ID.',
            ], 404);
        }

        $locationParts = array_filter([
            $member->grama_panchayat,
            $member->mandal,
            $member->district,
            $member->state,
            $member->country ?? 'India',
        ]);

        $isApproved = ($member->payment_status === 'success' || $member->is_completed);

        return response()->json([
            'success'          => true,
            'is_valid'         => true,
            'entity_type'      => 'ABVHPS Life Member',
            'official_id_label'=> 'Membership ID',
            'official_id'      => $member->membership_id,
            'name'             => $member->full_name,
            'photo_url'        => ApiMediaHelper::resolveUrl($member->photo_path),
            'status'           => $isApproved ? 'ACTIVE & VERIFIED' : 'PENDING VERIFICATION',
            'is_approved'      => $isApproved,
            'cadre'            => 'Life Member',
            'location'         => implode(', ', $locationParts) ?: 'Headquarters Matrix',
            'blood_group'      => $member->blood_group ?? null,
            'verified_since'   => $member->created_at ? Carbon::parse($member->created_at)->format('d M Y') : 'Official Record',
        ]);
    }

    private function verifyVolunteer(string $cleanId): JsonResponse
    {
        $volunteer = DB::table('volunteers')
            ->leftJoin('memberships', 'volunteers.membership_id', '=', 'memberships.membership_id')
            ->select(
                'volunteers.*',
                'memberships.full_name as member_full_name',
                'memberships.photo_path as member_photo_path',
                'memberships.blood_group as member_blood_group',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.grama_panchayat as member_grama_panchayat',
                'memberships.state as member_state'
            )
            ->where('volunteers.volunteer_id', $cleanId)
            ->orWhere('volunteers.volunteer_login_id', $cleanId)
            ->first();

        if (!$volunteer || $volunteer->status !== 'approved') {
            return response()->json([
                'success'     => false,
                'is_valid'    => false,
                'entity_type' => 'Volunteer',
                'searched_id' => $cleanId,
                'message'     => 'No active, approved volunteer record was found with this ID.',
            ], 404);
        }

        $locationParts = array_filter([
            $volunteer->grama_panchayat ?: $volunteer->member_grama_panchayat,
            $volunteer->mandal ?: $volunteer->member_mandal,
            $volunteer->district ?: $volunteer->member_district,
            $volunteer->state ?: $volunteer->member_state,
        ]);

        return response()->json([
            'success'          => true,
            'is_valid'         => true,
            'entity_type'      => 'Authorized Volunteer',
            'official_id_label'=> 'Volunteer ID',
            'official_id'      => $volunteer->volunteer_id ?? $volunteer->volunteer_login_id,
            'name'             => $volunteer->member_full_name ?? 'Registered Volunteer',
            'photo_url'        => ApiMediaHelper::resolveUrl($volunteer->member_photo_path),
            'status'           => 'ACTIVE & APPROVED',
            'is_approved'      => true,
            'cadre'            => $volunteer->cadre ?: ($volunteer->designation ?: 'Field Volunteer'),
            'location'         => implode(', ', $locationParts) ?: ($volunteer->locality ?: 'Regional Jurisdiction'),
            'blood_group'      => $volunteer->member_blood_group ?? null,
            'verified_since'   => $volunteer->created_at ? Carbon::parse($volunteer->created_at)->format('d M Y') : 'Official Record',
        ]);
    }

    private function verifyRudrasena(string $cleanId): JsonResponse
    {
        $member = DB::table('rudrasena_members')
            ->leftJoin('memberships', 'rudrasena_members.membership_id', '=', 'memberships.membership_id')
            ->select(
                'rudrasena_members.*',
                'memberships.photo_path as member_photo_path',
                'memberships.district as member_district',
                'memberships.mandal as member_mandal',
                'memberships.state as member_state'
            )
            ->where('rudrasena_members.rudrasena_id', $cleanId)
            ->first();

        if (!$member || !in_array($member->status, ['verified', 'approved'], true)) {
            return response()->json([
                'success'     => false,
                'is_valid'    => false,
                'entity_type' => 'Rudra Sena Member',
                'searched_id' => $cleanId,
                'message'     => 'No active, verified Rudra Sena member was found with this ID.',
            ], 404);
        }

        $locationParts = array_filter([
            $member->assigned_locality,
            $member->member_district,
            $member->member_state,
        ]);

        return response()->json([
            'success'          => true,
            'is_valid'         => true,
            'entity_type'      => 'Rudra Sena Member',
            'official_id_label'=> 'Rudra Sena ID',
            'official_id'      => $member->rudrasena_id,
            'name'             => $member->full_name,
            'photo_url'        => ApiMediaHelper::resolveUrl($member->member_photo_path),
            'status'           => 'VERIFIED & ACTIVE',
            'is_approved'      => true,
            'cadre'            => $member->assigned_cadder ?: 'Rudra Sena Commando',
            'location'         => implode(', ', $locationParts) ?: ($member->assigned_locality ?: 'Dharma Defense Wing'),
            'blood_group'      => $member->blood_group ?? null,
            'verified_since'   => $member->created_at ? Carbon::parse($member->created_at)->format('d M Y') : 'Official Record',
        ]);
    }

    private function verifyExam(string $cleanId): JsonResponse
    {
        $app = DB::table('exam_applications')
            ->leftJoin('exam_settings', 'exam_applications.exam_setting_id', '=', 'exam_settings.id')
            ->select(
                'exam_applications.*',
                'exam_settings.exam_title',
                'exam_settings.exam_date_time as exam_date',
                'exam_settings.exam_center_location as exam_center_address',
                'exam_settings.exam_type'
            )
            ->where('exam_applications.hall_ticket_number', $cleanId)
            ->first();

        if (!$app) {
            return response()->json([
                'success'     => false,
                'is_valid'    => false,
                'entity_type' => 'Exam Hall Ticket',
                'searched_id' => $cleanId,
                'message'     => 'No exam applicant hall ticket was found with this number.',
            ], 404);
        }

        $isPaid = ($app->payment_status === 'success' || $app->payment_status === 'completed');

        return response()->json([
            'success'          => true,
            'is_valid'         => true,
            'entity_type'      => 'Exam Applicant Hall Ticket',
            'official_id_label'=> 'Hall Ticket No.',
            'official_id'      => $app->hall_ticket_number,
            'name'             => $app->full_name,
            'photo_url'        => ApiMediaHelper::resolveUrl($app->photo_path),
            'status'           => $isPaid ? 'VALID HALL TICKET' : 'PENDING CLEARANCE',
            'is_approved'      => $isPaid,
            'cadre'            => $app->exam_title ?: 'Sanatana Dharma Examination',
            'location'         => $app->exam_center_address ?: ($app->address ?: 'Authorized Examination Center'),
            'exam_date'        => $app->exam_date ? Carbon::parse($app->exam_date)->format('d M Y, h:i A') : 'Scheduled Exam',
            'school_college'   => $app->school_college_name ?? null,
            'verified_since'   => $app->created_at ? Carbon::parse($app->created_at)->format('d M Y') : 'Official Entry',
        ]);
    }

    private function verifyOrganicFarmers(string $cleanId): JsonResponse
    {
        $group = DB::table('organic_farmers')
            ->where('farmer_registration_id', $cleanId)
            ->first();

        if (!$group) {
            return response()->json([
                'success'     => false,
                'is_valid'    => false,
                'entity_type' => 'Organic Farmers Group',
                'searched_id' => $cleanId,
                'message'     => 'No organic farmers village group was found with this Group ID.',
            ], 404);
        }

        $crops = DB::table('farmer_crops')->where('organic_farmer_id', $group->id)->pluck('crop_name')->toArray();
        $isApproved = ($group->status === 'approved');

        return response()->json([
            'success'          => true,
            'is_valid'         => true,
            'entity_type'      => 'Organic Farmers Group',
            'official_id_label'=> 'Group ID',
            'official_id'      => $group->farmer_registration_id,
            'name'             => $group->farmer_name . ' (Village Lead Farmer)',
            'status'           => $isApproved ? 'VERIFIED ORGANIC GROUP' : 'PENDING VERIFICATION',
            'is_approved'      => $isApproved,
            'cadre'            => 'Desi Agriculture Wing',
            'location'         => ($group->land_size_acres ? $group->land_size_acres . ' Acres (' . ($group->water_source ?? 'Rainfed') . ')' : 'Organic Farmland'),
            'extra_detail'     => 'Desi Cows: ' . ($group->indigenous_cows_count ?? 0) . (!empty($crops) ? ' | Crops: ' . implode(', ', $crops) : ''),
            'verified_since'   => $group->created_at ? Carbon::parse($group->created_at)->format('d M Y') : 'Official Entry',
        ]);
    }

    private function verifyKalaBrundam(string $cleanId): JsonResponse
    {
        $group = DB::table('kala_brundams')
            ->where('team_registration_id', $cleanId)
            ->first();

        if (!$group) {
            return response()->json([
                'success'     => false,
                'is_valid'    => false,
                'entity_type' => 'Kala Brundam Cultural Wing',
                'searched_id' => $cleanId,
                'message'     => 'No Kala Brundam cultural group was found with this Group ID.',
            ], 404);
        }

        $membersCount = DB::table('kala_brundam_members')->where('kala_brundam_id', $group->id)->count();
        $isApproved = ($group->status === 'approved');

        return response()->json([
            'success'          => true,
            'is_valid'         => true,
            'entity_type'      => 'Kala Brundam Cultural Wing',
            'official_id_label'=> 'Group ID',
            'official_id'      => $group->team_registration_id,
            'name'             => $group->team_name,
            'status'           => $isApproved ? 'VERIFIED CULTURAL TEAM' : 'PENDING VERIFICATION',
            'is_approved'      => $isApproved,
            'cadre'            => $group->team_type . ($group->custom_type_spec ? ' (' . $group->custom_type_spec . ')' : ''),
            'location'         => $group->location,
            'extra_detail'     => 'Certified Team Strength: ' . ($membersCount ?: 1) . ' Artists / Performers',
            'verified_since'   => $group->created_at ? Carbon::parse($group->created_at)->format('d M Y') : 'Official Entry',
        ]);
    }

    private function verifyGramaSevaDal(string $cleanId): JsonResponse
    {
        $group = DB::table('grama_seva_dals')
            ->where('gong_registration_id', $cleanId)
            ->first();

        if (!$group) {
            return response()->json([
                'success'     => false,
                'is_valid'    => false,
                'entity_type' => 'Grama Seva Dal Village Wing',
                'searched_id' => $cleanId,
                'message'     => 'No Grama Seva Dal village service group was found with this Group ID.',
            ], 404);
        }

        $membersCount = DB::table('grama_seva_dal_members')->where('grama_seva_dal_id', $group->id)->count();
        $locationParts = array_filter([
            $group->village_or_gp,
            $group->mandal,
            $group->district,
            $group->state,
        ]);
        $isApproved = ($group->status === 'approved');

        return response()->json([
            'success'          => true,
            'is_valid'         => true,
            'entity_type'      => 'Grama Seva Dal Village Wing',
            'official_id_label'=> 'Group ID',
            'official_id'      => $group->gong_registration_id,
            'name'             => $group->leader_name . ' (Dal Lead)',
            'status'           => $isApproved ? 'VERIFIED SERVICE DAL' : 'PENDING VERIFICATION',
            'is_approved'      => $isApproved,
            'cadre'            => 'Village Youth Service Dal',
            'location'         => implode(', ', $locationParts) ?: 'Grama Panchayat Jurisdiction',
            'extra_detail'     => 'Seva Force Strength: ' . ($membersCount ?: 1) . ' Active Volunteers',
            'verified_since'   => $group->created_at ? Carbon::parse($group->created_at)->format('d M Y') : 'Official Entry',
        ]);
    }
}
