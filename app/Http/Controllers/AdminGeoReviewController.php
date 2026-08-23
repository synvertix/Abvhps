<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\GeoState;
use App\Models\GeoDistrict;
use App\Models\GeoAssemblySegment;
use App\Models\GeoMandal;
use App\Models\GeoPanchayat;
use App\Models\GeoAlias;
use App\Services\VolunteerCadreScopeService;
use App\Services\AuditLogger;

class AdminGeoReviewController extends Controller
{
    /**
     * Display the Geographic Mapping Review Desk with dynamic conflict discovery.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $typeFilter = $request->query('type', 'all');

        $states = GeoState::where('is_active', true)->orderBy('name')->get();
        $districts = GeoDistrict::where('is_active', true)->orderBy('name')->get();
        $aliases = GeoAlias::with(['state', 'district'])->latest()->get();

        // 1. Fetch Volunteers needing review or matching status
        $volunteersQuery = Volunteer::with(['membership', 'stateRelation', 'districtRelation', 'assemblySegmentRelation', 'mandalRelation', 'panchayatRelation']);
        if ($statusFilter !== 'all') {
            $volunteersQuery->where('geo_mapping_status', $statusFilter);
        }
        $volunteers = $volunteersQuery->get()->map(function ($v) {
            return [
                'id' => $v->id,
                'type' => 'Volunteer',
                'record_id' => $v->volunteer_id ?? "VOL-{$v->id}",
                'full_name' => $v->full_name,
                'legacy_state' => $v->state ?: ($v->membership?->state ?? '—'),
                'legacy_district' => $v->district ?: ($v->membership?->district ?? '—'),
                'legacy_assembly' => $v->assembly_segment ?: ($v->membership?->assembly_segment ?? '—'),
                'legacy_mandal' => $v->mandal ?: ($v->membership?->mandal ?? '—'),
                'legacy_panchayat' => $v->grama_panchayat ?: ($v->membership?->grama_panchayat ?? '—'),
                'legacy_cadre' => $v->cadre ?: ($v->designation ?: 'Volunteer'),
                'legacy_role' => $v->role ?? 'volunteer',
                'cadre_level' => $v->cadre_level,
                'state_id' => $v->state_id,
                'district_id' => $v->district_id,
                'assembly_segment_id' => $v->assembly_segment_id,
                'mandal_id' => $v->mandal_id,
                'panchayat_id' => $v->panchayat_id,
                'mapped_summary' => $v->jurisdiction_summary,
                'geo_mapping_status' => $v->geo_mapping_status ?? 'unmapped',
                'notes' => $v->geo_mapping_notes ?? '—',
                'is_verified' => $v->geo_mapping_status === 'verified',
            ];
        });

        // 2. Fetch Memberships needing review or matching status
        $membershipsQuery = Membership::with(['stateRelation', 'districtRelation', 'assemblySegmentRelation', 'mandalRelation', 'panchayatRelation']);
        if ($statusFilter !== 'all') {
            $membershipsQuery->where('geo_mapping_status', $statusFilter);
        }
        $memberships = $membershipsQuery->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'type' => 'Membership',
                'record_id' => $m->membership_id ?? "MEM-{$m->id}",
                'full_name' => $m->full_name ?? 'Member',
                'legacy_state' => $m->state ?? '—',
                'legacy_district' => $m->district ?? '—',
                'legacy_assembly' => $m->assembly_segment ?? '—',
                'legacy_mandal' => $m->mandal ?? '—',
                'legacy_panchayat' => $m->grama_panchayat ?? '—',
                'legacy_cadre' => '—',
                'legacy_role' => '—',
                'cadre_level' => null,
                'state_id' => $m->state_id,
                'district_id' => $m->district_id,
                'assembly_segment_id' => $m->assembly_segment_id,
                'mandal_id' => $m->mandal_id,
                'panchayat_id' => $m->panchayat_id,
                'mapped_summary' => ($m->mandalRelation?->name ?? $m->mandal) . ', ' . ($m->districtRelation?->name ?? $m->district),
                'geo_mapping_status' => $m->geo_mapping_status ?? 'unmapped',
                'notes' => $m->geo_mapping_notes ?? '—',
                'is_verified' => $m->geo_mapping_status === 'verified',
            ];
        });

        $records = collect();
        if ($typeFilter === 'all' || $typeFilter === 'volunteer') {
            $records = $records->concat($volunteers);
        }
        if ($typeFilter === 'all' || $typeFilter === 'membership') {
            $records = $records->concat($memberships);
        }

        $counts = [
            'total' => $volunteers->count() + $memberships->count(),
            'needs_review' => $volunteers->where('geo_mapping_status', 'needs_review')->count() + $memberships->where('geo_mapping_status', 'needs_review')->count(),
            'matched' => $volunteers->whereIn('geo_mapping_status', ['matched', 'partial_matched'])->count() + $memberships->whereIn('geo_mapping_status', ['matched', 'partial_matched'])->count(),
            'verified' => $volunteers->where('geo_mapping_status', 'verified')->count() + $memberships->where('geo_mapping_status', 'verified')->count(),
            'unmapped' => $volunteers->where('geo_mapping_status', 'unmapped')->count() + $memberships->where('geo_mapping_status', 'unmapped')->count(),
        ];

        return view('admin.geo_mapping_review', compact('records', 'states', 'districts', 'aliases', 'counts', 'statusFilter', 'typeFilter'));
    }

    /**
     * Explicit Admin Action: Update mapping and confirm canonical status for a record.
     */
    public function updateMapping(Request $request, string $type, int $id)
    {
        $request->validate([
            'state_id' => 'nullable|integer|exists:geo_states,id',
            'district_id' => 'nullable|integer|exists:geo_districts,id',
            'assembly_segment_id' => 'nullable|integer|exists:geo_assembly_segments,id',
            'mandal_id' => 'nullable|integer|exists:geo_mandals,id',
            'panchayat_id' => 'nullable|integer|exists:geo_panchayats,id',
            'cadre_level' => 'nullable|string|in:national_president,state_president,district_president,assembly_president,mandal_president,panchayat_president,volunteer',
            'confirm_verified' => 'nullable|boolean',
        ]);

        $stateId = $request->input('state_id') ? (int)$request->input('state_id') : null;
        $districtId = $request->input('district_id') ? (int)$request->input('district_id') : null;
        $assemblyId = $request->input('assembly_segment_id') ? (int)$request->input('assembly_segment_id') : null;
        $mandalId = $request->input('mandal_id') ? (int)$request->input('mandal_id') : null;
        $panchayatId = $request->input('panchayat_id') ? (int)$request->input('panchayat_id') : null;
        $cadreLevel = $request->input('cadre_level');
        $isVerified = (bool)$request->input('confirm_verified', true);

        // Validate parent-child hierarchy integrity
        $hierarchyError = VolunteerCadreScopeService::validateParentChildGeography($stateId, $districtId, $assemblyId, $mandalId, $panchayatId);
        if ($hierarchyError) {
            return back()->withErrors(['hierarchy' => $hierarchyError]);
        }

        if (strtolower($type) === 'volunteer') {
            $volunteer = Volunteer::findOrFail($id);

            // Duplicate active president check
            if ($isVerified && $cadreLevel && $cadreLevel !== 'volunteer') {
                $dupError = VolunteerCadreScopeService::checkDuplicateActivePresident($cadreLevel, $stateId, $districtId, $assemblyId, $mandalId, $panchayatId, $volunteer->id);
                if ($dupError) {
                    return back()->withErrors(['duplicate' => $dupError]);
                }
            }

            $volunteer->update([
                'state_id' => $stateId,
                'district_id' => $districtId,
                'assembly_segment_id' => $assemblyId,
                'mandal_id' => $mandalId,
                'panchayat_id' => $panchayatId,
                'cadre_level' => $cadreLevel ?: $volunteer->cadre_level,
                'geo_mapping_status' => $isVerified ? 'verified' : 'matched',
                'geo_mapping_notes' => 'Confirmed and verified by Admin on ' . now()->format('Y-m-d H:i:s'),
            ]);

            AuditLogger::log('GEO_MAPPING_VERIFIED', 'Volunteer', (string)$volunteer->volunteer_id, [
                'cadre_level' => $volunteer->cadre_level,
                'state_id' => $stateId,
                'district_id' => $districtId,
                'mandal_id' => $mandalId,
                'panchayat_id' => $panchayatId,
            ]);

            return back()->with('success', "Volunteer #{$volunteer->volunteer_id} mapping successfully verified and updated.");
        }

        if (strtolower($type) === 'membership') {
            $membership = Membership::findOrFail($id);
            $membership->update([
                'state_id' => $stateId,
                'district_id' => $districtId,
                'assembly_segment_id' => $assemblyId,
                'mandal_id' => $mandalId,
                'panchayat_id' => $panchayatId,
                'geo_mapping_status' => $isVerified ? 'verified' : 'matched',
                'geo_mapping_notes' => 'Confirmed and verified by Admin on ' . now()->format('Y-m-d H:i:s'),
            ]);

            AuditLogger::log('GEO_MAPPING_VERIFIED', 'Membership', (string)$membership->membership_id, [
                'state_id' => $stateId,
                'district_id' => $districtId,
                'mandal_id' => $mandalId,
                'panchayat_id' => $panchayatId,
            ]);

            return back()->with('success', "Membership #{$membership->membership_id} mapping successfully verified and updated.");
        }

        return back()->withErrors(['type' => 'Invalid entity type.']);
    }

    /**
     * Explicit Admin Action: Approve and create a geographic alias mapping (e.g. Cuddapah -> YSR Kadapa).
     */
    public function approveAlias(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:district,assembly_segment,mandal,panchayat',
            'alias_name' => 'required|string|max:150',
            'canonical_id' => 'required|integer',
            'state_id' => 'nullable|integer|exists:geo_states,id',
            'district_id' => 'nullable|integer|exists:geo_districts,id',
        ]);

        $alias = GeoAlias::updateOrCreate(
            [
                'entity_type' => $request->input('entity_type'),
                'alias_name' => trim($request->input('alias_name')),
                'state_id' => $request->input('state_id') ?: null,
                'district_id' => $request->input('district_id') ?: null,
            ],
            [
                'canonical_id' => (int)$request->input('canonical_id'),
                'approved_by_admin_id' => auth()->id() ?? 1,
            ]
        );

        AuditLogger::log('GEO_ALIAS_APPROVED', 'GeoAlias', (string)$alias->id, [
            'entity_type' => $alias->entity_type,
            'alias_name' => $alias->alias_name,
            'canonical_id' => $alias->canonical_id,
        ]);

        return back()->with('success', "Geographic alias '{$alias->alias_name}' successfully approved and linked to canonical ID #{$alias->canonical_id}.");
    }
}
