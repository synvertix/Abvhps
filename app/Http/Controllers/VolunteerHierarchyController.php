<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\GeoState;
use App\Models\GeoDistrict;
use App\Models\GeoAssemblySegment;
use App\Models\GeoMandal;
use App\Models\GeoPanchayat;
use App\Models\VolunteerEvent;
use App\Services\VolunteerCadreScopeService;

class VolunteerHierarchyController extends Controller
{
    /**
     * Display State Detail View & Subordinate Districts Table
     */
    public function showState($id)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $stateId = (int)$id;

        if (!VolunteerCadreScopeService::canViewState($volunteer, $stateId)) {
            abort(403, 'Unauthorized access to State outside your authorized geographic scope.');
        }

        $state = GeoState::findOrFail($stateId);
        $president = VolunteerCadreScopeService::getPresidentForUnit('state', $stateId);
        $stats = VolunteerCadreScopeService::getUnitStatistics('state', $stateId);
        $subordinates = VolunteerCadreScopeService::getSubordinateDistricts($stateId);

        $unitType = 'State';
        $unitName = $state->name;
        $jurisdictionSummary = 'National Scope &mdash; India';
        $childUnitLabel = 'Districts Under ' . $state->name;

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('volunteer.dashboard')],
            ['title' => $state->name, 'url' => null, 'active' => true],
        ];
        $backUrl = route('volunteer.dashboard');

        return view('volunteer.hierarchy.unit_detail', compact(
            'volunteer',
            'state',
            'president',
            'stats',
            'subordinates',
            'unitType',
            'unitName',
            'jurisdictionSummary',
            'childUnitLabel',
            'breadcrumbs',
            'backUrl'
        ));
    }

    /**
     * Display District Detail View & Subordinate Assembly Segments Table
     */
    public function showDistrict($id)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $districtId = (int)$id;

        if (!VolunteerCadreScopeService::canViewDistrict($volunteer, $districtId)) {
            abort(403, 'Unauthorized access to District outside your authorized geographic scope.');
        }

        $district = GeoDistrict::with('state')->findOrFail($districtId);
        $president = VolunteerCadreScopeService::getPresidentForUnit('district', $districtId);
        $stats = VolunteerCadreScopeService::getUnitStatistics('district', $districtId);
        $subordinates = VolunteerCadreScopeService::getSubordinateAssemblySegments($districtId);

        $unitType = 'District';
        $unitName = $district->name;
        $jurisdictionSummary = $district->state?->name ?? 'Andhra Pradesh';
        $childUnitLabel = 'Assembly Segments Under ' . $district->name;

        $canViewState = VolunteerCadreScopeService::canViewState($volunteer, (int)$district->state_id);

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('volunteer.dashboard')],
            ['title' => $district->state?->name ?? 'State', 'url' => $canViewState ? route('volunteer.hierarchy.state', $district->state_id) : null],
            ['title' => $district->name, 'url' => null, 'active' => true],
        ];
        $backUrl = $canViewState ? route('volunteer.hierarchy.state', $district->state_id) : route('volunteer.dashboard');

        return view('volunteer.hierarchy.unit_detail', compact(
            'volunteer',
            'district',
            'president',
            'stats',
            'subordinates',
            'unitType',
            'unitName',
            'jurisdictionSummary',
            'childUnitLabel',
            'breadcrumbs',
            'backUrl'
        ));
    }

    /**
     * Display Assembly Segment Detail View & Subordinate Mandals Table
     */
    public function showAssembly($id)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $assemblyId = (int)$id;

        if (!VolunteerCadreScopeService::canViewAssemblySegment($volunteer, $assemblyId)) {
            abort(403, 'Unauthorized access to Assembly Segment outside your authorized geographic scope.');
        }

        $assembly = GeoAssemblySegment::with(['district.state'])->findOrFail($assemblyId);
        $president = VolunteerCadreScopeService::getPresidentForUnit('assembly', $assemblyId);
        $stats = VolunteerCadreScopeService::getUnitStatistics('assembly', $assemblyId);
        $subordinates = VolunteerCadreScopeService::getSubordinateMandals($assemblyId);

        $unitType = 'Taluk / Assembly Segment';
        $unitName = $assembly->name;
        $jurisdictionSummary = ($assembly->district?->name ?? 'District') . ', ' . ($assembly->district?->state?->name ?? 'State');
        $childUnitLabel = 'Mandals Under ' . $assembly->name;

        $canViewState = $assembly->district ? VolunteerCadreScopeService::canViewState($volunteer, (int)$assembly->district->state_id) : false;
        $canViewDistrict = VolunteerCadreScopeService::canViewDistrict($volunteer, (int)$assembly->district_id);

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('volunteer.dashboard')],
            ['title' => $assembly->district?->state?->name ?? 'State', 'url' => $canViewState ? route('volunteer.hierarchy.state', $assembly->district->state_id) : null],
            ['title' => $assembly->district?->name ?? 'District', 'url' => $canViewDistrict ? route('volunteer.hierarchy.district', $assembly->district_id) : null],
            ['title' => $assembly->name, 'url' => null, 'active' => true],
        ];
        $backUrl = $canViewDistrict ? route('volunteer.hierarchy.district', $assembly->district_id) : route('volunteer.dashboard');

        return view('volunteer.hierarchy.unit_detail', compact(
            'volunteer',
            'assembly',
            'president',
            'stats',
            'subordinates',
            'unitType',
            'unitName',
            'jurisdictionSummary',
            'childUnitLabel',
            'breadcrumbs',
            'backUrl'
        ));
    }

    /**
     * Display Mandal Detail View & Subordinate Panchayats Table
     */
    public function showMandal($id)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $mandalId = (int)$id;

        if (!VolunteerCadreScopeService::canViewMandal($volunteer, $mandalId)) {
            abort(403, 'Unauthorized access to Mandal outside your authorized geographic scope.');
        }

        $mandal = GeoMandal::with(['assemblySegment', 'district.state'])->findOrFail($mandalId);
        $president = VolunteerCadreScopeService::getPresidentForUnit('mandal', $mandalId);
        $stats = VolunteerCadreScopeService::getUnitStatistics('mandal', $mandalId);
        $subordinates = VolunteerCadreScopeService::getSubordinatePanchayats($mandalId);

        $unitType = 'Mandal';
        $unitName = $mandal->name;
        $jurisdictionSummary = ($mandal->assemblySegment?->name ?? 'Assembly') . ', ' . ($mandal->district?->name ?? 'District') . ', ' . ($mandal->district?->state?->name ?? 'State');
        $childUnitLabel = 'Panchayats Under ' . $mandal->name;

        $canViewState = $mandal->district ? VolunteerCadreScopeService::canViewState($volunteer, (int)$mandal->district->state_id) : false;
        $canViewDistrict = VolunteerCadreScopeService::canViewDistrict($volunteer, (int)$mandal->district_id);
        $canViewAssembly = $mandal->assembly_segment_id ? VolunteerCadreScopeService::canViewAssemblySegment($volunteer, (int)$mandal->assembly_segment_id) : false;

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('volunteer.dashboard')],
            ['title' => $mandal->district?->state?->name ?? 'State', 'url' => $canViewState ? route('volunteer.hierarchy.state', $mandal->district->state_id) : null],
            ['title' => $mandal->district?->name ?? 'District', 'url' => $canViewDistrict ? route('volunteer.hierarchy.district', $mandal->district_id) : null],
            ['title' => $mandal->assemblySegment?->name ?? 'Assembly', 'url' => $canViewAssembly ? route('volunteer.hierarchy.assembly', $mandal->assembly_segment_id) : null],
            ['title' => $mandal->name, 'url' => null, 'active' => true],
        ];
        $backUrl = $canViewAssembly ? route('volunteer.hierarchy.assembly', $mandal->assembly_segment_id) : ($canViewDistrict ? route('volunteer.hierarchy.district', $mandal->district_id) : route('volunteer.dashboard'));

        return view('volunteer.hierarchy.unit_detail', compact(
            'volunteer',
            'mandal',
            'president',
            'stats',
            'subordinates',
            'unitType',
            'unitName',
            'jurisdictionSummary',
            'childUnitLabel',
            'breadcrumbs',
            'backUrl'
        ));
    }

    /**
     * Display Panchayat Detail View (Lowest Operational Tier)
     */
    public function showPanchayat($id)
    {
        $volunteer = Auth::guard('volunteer')->user();
        $panchayatId = (int)$id;

        if (!VolunteerCadreScopeService::canViewPanchayat($volunteer, $panchayatId)) {
            abort(403, 'Unauthorized access to Panchayat outside your authorized geographic scope.');
        }

        $panchayat = GeoPanchayat::with(['mandal.assemblySegment', 'mandal.district.state'])->findOrFail($panchayatId);
        $president = VolunteerCadreScopeService::getPresidentForUnit('panchayat', $panchayatId);
        $stats = VolunteerCadreScopeService::getUnitStatistics('panchayat', $panchayatId);

        // Safe Registered Members list for this canonical Panchayat (Paginated)
        $members = Membership::where('panchayat_id', $panchayatId)
            ->select(['id', 'membership_id', 'full_name', 'grama_panchayat', 'mandal', 'payment_status', 'created_at'])
            ->orderBy('id', 'desc')
            ->paginate(50, ['id', 'membership_id', 'full_name', 'grama_panchayat', 'mandal', 'payment_status', 'created_at'], 'members_page')
            ->withQueryString();

        // Approved & Active Volunteers for this canonical Panchayat (Paginated)
        $volunteers = Volunteer::where('panchayat_id', $panchayatId)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where('geo_mapping_status', 'verified')
            ->orderBy('id', 'desc')
            ->paginate(50, ['*'], 'volunteers_page')
            ->withQueryString();

        // Events canonically mapped to this Panchayat (Paginated)
        $events = VolunteerEvent::where('panchayat_id', $panchayatId)
            ->withCount('eventMembers')
            ->orderBy('event_date', 'desc')
            ->paginate(25, ['*'], 'events_page')
            ->withQueryString();

        $mandal = $panchayat->mandal;
        $canViewState = $mandal?->district ? VolunteerCadreScopeService::canViewState($volunteer, (int)$mandal->district->state_id) : false;
        $canViewDistrict = $mandal ? VolunteerCadreScopeService::canViewDistrict($volunteer, (int)$mandal->district_id) : false;
        $canViewAssembly = $mandal?->assembly_segment_id ? VolunteerCadreScopeService::canViewAssemblySegment($volunteer, (int)$mandal->assembly_segment_id) : false;
        $canViewMandal = $mandal ? VolunteerCadreScopeService::canViewMandal($volunteer, (int)$mandal->id) : false;

        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('volunteer.dashboard')],
            ['title' => $mandal?->district?->state?->name ?? 'State', 'url' => $canViewState ? route('volunteer.hierarchy.state', $mandal->district->state_id) : null],
            ['title' => $mandal?->district?->name ?? 'District', 'url' => $canViewDistrict ? route('volunteer.hierarchy.district', $mandal->district_id) : null],
            ['title' => $mandal?->assemblySegment?->name ?? 'Assembly', 'url' => $canViewAssembly ? route('volunteer.hierarchy.assembly', $mandal->assembly_segment_id) : null],
            ['title' => $mandal?->name ?? 'Mandal', 'url' => $canViewMandal ? route('volunteer.hierarchy.mandal', $mandal->id) : null],
            ['title' => $panchayat->name, 'url' => null, 'active' => true],
        ];
        $backUrl = $canViewMandal ? route('volunteer.hierarchy.mandal', $panchayat->mandal_id) : route('volunteer.dashboard');

        return view('volunteer.hierarchy.panchayat_detail', compact(
            'volunteer',
            'panchayat',
            'president',
            'stats',
            'members',
            'volunteers',
            'events',
            'breadcrumbs',
            'backUrl'
        ));
    }
}
