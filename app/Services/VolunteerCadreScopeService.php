<?php

namespace App\Services;

use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\GeoState;
use App\Models\GeoDistrict;
use App\Models\GeoAssemblySegment;
use App\Models\GeoMandal;
use App\Models\GeoPanchayat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VolunteerCadreScopeService
{
    /**
     * Determine whether the volunteer is a verified, active President for the given cadre level.
     * Enforces strict is_active === true (null or false is denied), status === 'approved',
     * geo_mapping_status === 'verified', and presence of active canonical jurisdiction ID.
     */
    public static function isVerifiedCadre(Volunteer $volunteer, ?string $requiredLevel = null): bool
    {
        if ($volunteer->status !== 'approved') {
            return false;
        }

        // Strict is_active boolean check: null, false, 0 are strictly denied
        if ($volunteer->is_active !== true && $volunteer->is_active !== 1 && $volunteer->is_active !== '1') {
            return false;
        }

        if (empty($volunteer->cadre_level) || $volunteer->geo_mapping_status !== 'verified') {
            return false;
        }

        if ($requiredLevel !== null && $volunteer->cadre_level !== $requiredLevel) {
            return false;
        }

        // Validate presence and existence of required canonical jurisdiction IDs
        return match ($volunteer->cadre_level) {
            'national_president'  => true,
            'state_president'     => !empty($volunteer->state_id) && GeoState::where('id', $volunteer->state_id)->where('is_active', true)->exists(),
            'district_president'  => !empty($volunteer->district_id) && GeoDistrict::where('id', $volunteer->district_id)->where('is_active', true)->exists(),
            'assembly_president'  => !empty($volunteer->assembly_segment_id) && GeoAssemblySegment::where('id', $volunteer->assembly_segment_id)->where('is_active', true)->exists(),
            'mandal_president'    => !empty($volunteer->mandal_id) && GeoMandal::where('id', $volunteer->mandal_id)->where('is_active', true)->exists(),
            'panchayat_president' => !empty($volunteer->panchayat_id) && GeoPanchayat::where('id', $volunteer->panchayat_id)->where('is_active', true)->exists(),
            'volunteer'           => true,
            default               => false,
        };
    }

    // =========================================================================
    // Scope Access Checks (Strict Canonical ID Match)
    // =========================================================================

    public static function canViewState(Volunteer $volunteer, int $stateId): bool
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return false;
        }

        if ($volunteer->cadre_level === 'national_president') {
            return true;
        }

        return (int)$volunteer->state_id === $stateId;
    }

    public static function canViewDistrict(Volunteer $volunteer, int $districtId): bool
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return false;
        }

        if ($volunteer->cadre_level === 'national_president') {
            return true;
        }

        $district = GeoDistrict::find($districtId);
        if (!$district) {
            return false;
        }

        if ($volunteer->cadre_level === 'state_president') {
            return (int)$district->state_id === (int)$volunteer->state_id;
        }

        return (int)$volunteer->district_id === $districtId;
    }

    public static function canViewAssemblySegment(Volunteer $volunteer, int $segmentId): bool
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return false;
        }

        if ($volunteer->cadre_level === 'national_president') {
            return true;
        }

        $segment = GeoAssemblySegment::find($segmentId);
        if (!$segment) {
            return false;
        }

        if ($volunteer->cadre_level === 'state_president') {
            return (int)($segment->district?->state_id) === (int)$volunteer->state_id;
        }

        if ($volunteer->cadre_level === 'district_president') {
            return (int)$segment->district_id === (int)$volunteer->district_id;
        }

        return (int)$volunteer->assembly_segment_id === $segmentId;
    }

    public static function canViewMandal(Volunteer $volunteer, int $mandalId): bool
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return false;
        }

        if ($volunteer->cadre_level === 'national_president') {
            return true;
        }

        $mandal = GeoMandal::find($mandalId);
        if (!$mandal) {
            return false;
        }

        if ($volunteer->cadre_level === 'state_president') {
            return (int)($mandal->district?->state_id) === (int)$volunteer->state_id;
        }

        if ($volunteer->cadre_level === 'district_president') {
            return (int)$mandal->district_id === (int)$volunteer->district_id;
        }

        if ($volunteer->cadre_level === 'assembly_president') {
            return (int)$mandal->assembly_segment_id === (int)$volunteer->assembly_segment_id;
        }

        return (int)$volunteer->mandal_id === $mandalId;
    }

    public static function canViewPanchayat(Volunteer $volunteer, int $panchayatId): bool
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return false;
        }

        if ($volunteer->cadre_level === 'national_president') {
            return true;
        }

        $panchayat = GeoPanchayat::find($panchayatId);
        if (!$panchayat) {
            return false;
        }

        $mandal = $panchayat->mandal;
        if (!$mandal) {
            return false;
        }

        if ($volunteer->cadre_level === 'state_president') {
            return (int)($mandal->district?->state_id) === (int)$volunteer->state_id;
        }

        if ($volunteer->cadre_level === 'district_president') {
            return (int)$mandal->district_id === (int)$volunteer->district_id;
        }

        if ($volunteer->cadre_level === 'assembly_president') {
            return (int)$mandal->assembly_segment_id === (int)$volunteer->assembly_segment_id;
        }

        if ($volunteer->cadre_level === 'mandal_president') {
            return (int)$volunteer->mandal_id === (int)$panchayat->mandal_id;
        }

        if ($volunteer->cadre_level === 'panchayat_president') {
            return (int)$volunteer->panchayat_id === $panchayatId;
        }

        return false;
    }

    // =========================================================================
    // Scoped Query Builders (Fail-Closed if Not Verified)
    // =========================================================================

    public static function statesFor(Volunteer $volunteer): Builder
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return GeoState::whereRaw('1 = 0');
        }

        $query = GeoState::where('is_active', true);
        if ($volunteer->cadre_level === 'national_president') {
            return $query->orderBy('name');
        }
        if ($volunteer->state_id) {
            return $query->where('id', $volunteer->state_id);
        }
        return $query->whereRaw('1 = 0');
    }

    public static function districtsFor(Volunteer $volunteer, ?int $stateId = null): Builder
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return GeoDistrict::whereRaw('1 = 0');
        }

        $query = GeoDistrict::where('is_active', true);

        if ($stateId) {
            $query->where('state_id', $stateId);
        }

        if ($volunteer->cadre_level === 'national_president') {
            return $query->orderBy('name');
        }

        if ($volunteer->cadre_level === 'state_president') {
            return $query->where('state_id', $volunteer->state_id)->orderBy('name');
        }

        if ($volunteer->district_id) {
            return $query->where('id', $volunteer->district_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function assemblySegmentsFor(Volunteer $volunteer, ?int $districtId = null): Builder
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return GeoAssemblySegment::whereRaw('1 = 0');
        }

        $query = GeoAssemblySegment::where('is_active', true);

        if ($districtId) {
            $query->where('district_id', $districtId);
        }

        if ($volunteer->cadre_level === 'national_president') {
            return $query->orderBy('name');
        }

        if ($volunteer->cadre_level === 'state_president') {
            return $query->whereHas('district', fn($q) => $q->where('state_id', $volunteer->state_id))->orderBy('name');
        }

        if ($volunteer->cadre_level === 'district_president') {
            return $query->where('district_id', $volunteer->district_id)->orderBy('name');
        }

        if ($volunteer->assembly_segment_id) {
            return $query->where('id', $volunteer->assembly_segment_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function mandalsFor(Volunteer $volunteer, ?int $segmentOrDistrictId = null): Builder
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return GeoMandal::whereRaw('1 = 0');
        }

        $query = GeoMandal::where('is_active', true);

        if ($segmentOrDistrictId) {
            $query->where(function ($q) use ($segmentOrDistrictId) {
                $q->where('assembly_segment_id', $segmentOrDistrictId)
                  ->orWhere('district_id', $segmentOrDistrictId);
            });
        }

        if ($volunteer->cadre_level === 'national_president') {
            return $query->orderBy('name');
        }

        if ($volunteer->cadre_level === 'state_president') {
            return $query->whereHas('district', fn($q) => $q->where('state_id', $volunteer->state_id))->orderBy('name');
        }

        if ($volunteer->cadre_level === 'district_president') {
            return $query->where('district_id', $volunteer->district_id)->orderBy('name');
        }

        if ($volunteer->cadre_level === 'assembly_president') {
            return $query->where('assembly_segment_id', $volunteer->assembly_segment_id)->orderBy('name');
        }

        if ($volunteer->mandal_id) {
            return $query->where('id', $volunteer->mandal_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function panchayatsFor(Volunteer $volunteer, ?int $mandalId = null): Builder
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return GeoPanchayat::whereRaw('1 = 0');
        }

        $query = GeoPanchayat::where('is_active', true);

        if ($mandalId) {
            $query->where('mandal_id', $mandalId);
        }

        if ($volunteer->cadre_level === 'national_president') {
            return $query->orderBy('name');
        }

        if ($volunteer->cadre_level === 'state_president') {
            return $query->whereHas('mandal.district', fn($q) => $q->where('state_id', $volunteer->state_id))->orderBy('name');
        }

        if ($volunteer->cadre_level === 'district_president') {
            return $query->whereHas('mandal', fn($q) => $q->where('district_id', $volunteer->district_id))->orderBy('name');
        }

        if ($volunteer->cadre_level === 'assembly_president') {
            return $query->whereHas('mandal', fn($q) => $q->where('assembly_segment_id', $volunteer->assembly_segment_id))->orderBy('name');
        }

        if ($volunteer->cadre_level === 'mandal_president') {
            return $query->where('mandal_id', $volunteer->mandal_id)->orderBy('name');
        }

        if ($volunteer->panchayat_id) {
            return $query->where('id', $volunteer->panchayat_id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Authorized Member Query: Strict Canonical Foreign Keys ONLY.
     * Legacy free-text strings are NEVER used to expand President authorization.
     */
    public static function membersFor(Volunteer $volunteer): Builder
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return Membership::whereRaw('1 = 0');
        }

        $query = Membership::query();

        if ($volunteer->cadre_level === 'national_president') {
            return $query;
        }

        if ($volunteer->cadre_level === 'state_president') {
            return $query->where('state_id', $volunteer->state_id);
        }

        if ($volunteer->cadre_level === 'district_president') {
            return $query->where('district_id', $volunteer->district_id);
        }

        if ($volunteer->cadre_level === 'assembly_president') {
            return $query->where('assembly_segment_id', $volunteer->assembly_segment_id);
        }

        if ($volunteer->cadre_level === 'mandal_president') {
            return $query->where('mandal_id', $volunteer->mandal_id);
        }

        if ($volunteer->cadre_level === 'panchayat_president') {
            return $query->where('panchayat_id', $volunteer->panchayat_id);
        }

        return $query->whereRaw('1 = 0');
    }

    // =========================================================================
    // Subordinate Unit Directory Helpers
    // =========================================================================

    /**
     * Retrieve subordinate geographic units with assigned President details for directory rendering.
     */
    public static function subordinateUnitsFor(Volunteer $volunteer): Collection
    {
        if (!self::isVerifiedCadre($volunteer)) {
            return collect();
        }

        return match ($volunteer->cadre_level) {
            'national_president' => self::getSubordinateStates(),
            'state_president'    => self::getSubordinateDistricts($volunteer->state_id),
            'district_president' => self::getSubordinateAssemblySegments($volunteer->district_id),
            'assembly_president' => self::getSubordinateMandals($volunteer->assembly_segment_id),
            'mandal_president'   => self::getSubordinatePanchayats($volunteer->mandal_id),
            'panchayat_president'=> self::getPanchayatSelfDetails($volunteer),
            default              => collect(),
        };
    }

    protected static function getSubordinateStates(): Collection
    {
        return GeoState::where('is_active', true)->orderBy('name')->get()->map(function ($st) {
            $pres = Volunteer::where('cadre_level', 'state_president')
                ->where('state_id', $st->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where('geo_mapping_status', 'verified')
                ->first();

            return [
                'unit_id' => $st->id,
                'unit_name' => $st->name,
                'unit_type' => 'State',
                'president_name' => $pres?->full_name ?? 'Not Assigned',
                'contact_phone' => $pres?->phone ?? '—',
                'is_assigned' => $pres !== null,
                'volunteer_id' => $pres?->volunteer_id,
            ];
        });
    }

    protected static function getSubordinateDistricts(?int $stateId): Collection
    {
        if (!$stateId) return collect();

        return GeoDistrict::where('state_id', $stateId)->where('is_active', true)->orderBy('name')->get()->map(function ($dst) {
            $pres = Volunteer::where('cadre_level', 'district_president')
                ->where('district_id', $dst->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where('geo_mapping_status', 'verified')
                ->first();

            return [
                'unit_id' => $dst->id,
                'unit_name' => $dst->name,
                'unit_type' => 'District',
                'president_name' => $pres?->full_name ?? 'Not Assigned',
                'contact_phone' => $pres?->phone ?? '—',
                'is_assigned' => $pres !== null,
                'volunteer_id' => $pres?->volunteer_id,
            ];
        });
    }

    protected static function getSubordinateAssemblySegments(?int $districtId): Collection
    {
        if (!$districtId) return collect();

        return GeoAssemblySegment::where('district_id', $districtId)->where('is_active', true)->orderBy('name')->get()->map(function ($seg) {
            $pres = Volunteer::where('cadre_level', 'assembly_president')
                ->where('assembly_segment_id', $seg->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where('geo_mapping_status', 'verified')
                ->first();

            return [
                'unit_id' => $seg->id,
                'unit_name' => $seg->name,
                'unit_type' => 'Assembly Segment',
                'president_name' => $pres?->full_name ?? 'Not Assigned',
                'contact_phone' => $pres?->phone ?? '—',
                'is_assigned' => $pres !== null,
                'volunteer_id' => $pres?->volunteer_id,
            ];
        });
    }

    protected static function getSubordinateMandals(?int $assemblySegmentId): Collection
    {
        if (!$assemblySegmentId) return collect();

        return GeoMandal::where('assembly_segment_id', $assemblySegmentId)->where('is_active', true)->orderBy('name')->get()->map(function ($mdl) {
            $pres = Volunteer::where('cadre_level', 'mandal_president')
                ->where('mandal_id', $mdl->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where('geo_mapping_status', 'verified')
                ->first();

            return [
                'unit_id' => $mdl->id,
                'unit_name' => $mdl->name,
                'unit_type' => 'Mandal',
                'president_name' => $pres?->full_name ?? 'Not Assigned',
                'contact_phone' => $pres?->phone ?? '—',
                'is_assigned' => $pres !== null,
                'volunteer_id' => $pres?->volunteer_id,
            ];
        });
    }

    protected static function getSubordinatePanchayats(?int $mandalId): Collection
    {
        if (!$mandalId) return collect();

        return GeoPanchayat::where('mandal_id', $mandalId)->where('is_active', true)->orderBy('name')->get()->map(function ($pan) {
            $pres = Volunteer::where('cadre_level', 'panchayat_president')
                ->where('panchayat_id', $pan->id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where('geo_mapping_status', 'verified')
                ->first();

            return [
                'unit_id' => $pan->id,
                'unit_name' => $pan->name,
                'unit_type' => 'Grama Panchayat',
                'president_name' => $pres?->full_name ?? 'Not Assigned',
                'contact_phone' => $pres?->phone ?? '—',
                'is_assigned' => $pres !== null,
                'volunteer_id' => $pres?->volunteer_id,
            ];
        });
    }

    protected static function getPanchayatSelfDetails(Volunteer $volunteer): Collection
    {
        return collect([[
            'unit_id' => $volunteer->panchayat_id ?? 0,
            'unit_name' => $volunteer->panchayatRelation?->name ?? 'Panchayat',
            'unit_type' => 'Grama Panchayat',
            'president_name' => $volunteer->full_name,
            'contact_phone' => $volunteer->phone,
            'is_assigned' => true,
            'volunteer_id' => $volunteer->volunteer_id,
        ]]);
    }

    // =========================================================================
    // Duplicate Active President & Parent-Child Hierarchy Guards
    // =========================================================================

    /**
     * Check if another active verified President already exists for this level & jurisdiction.
     * Uses strict is_active = true and status = 'approved'.
     * Returns error message string if duplicate found, null if clean.
     */
    public static function checkDuplicateActivePresident(
        ?string $cadreLevel,
        ?int $stateId,
        ?int $districtId,
        ?int $assemblyId,
        ?int $mandalId,
        ?int $panchayatId,
        ?int $excludeVolunteerId = null
    ): ?string {
        if (!in_array($cadreLevel, [
            'national_president',
            'state_president',
            'district_president',
            'assembly_president',
            'mandal_president',
            'panchayat_president'
        ], true)) {
            return null;
        }

        $query = Volunteer::where('cadre_level', $cadreLevel)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where('geo_mapping_status', 'verified');

        if ($excludeVolunteerId) {
            $query->where('id', '!=', $excludeVolunteerId);
        }

        if ($cadreLevel === 'national_president') {
            $existing = $query->first();
            if ($existing) {
                return "An active verified National President is already assigned (#{$existing->volunteer_id} {$existing->full_name}). Deactivate or reassign first.";
            }
        } elseif ($cadreLevel === 'state_president' && $stateId) {
            $query->where('state_id', $stateId);
            $existing = $query->first();
            if ($existing) {
                return "An active verified State President is already assigned to this State (#{$existing->volunteer_id} {$existing->full_name}).";
            }
        } elseif ($cadreLevel === 'district_president' && $districtId) {
            $query->where('district_id', $districtId);
            $existing = $query->first();
            if ($existing) {
                return "An active verified District President is already assigned to this District (#{$existing->volunteer_id} {$existing->full_name}).";
            }
        } elseif ($cadreLevel === 'assembly_president' && $assemblyId) {
            $query->where('assembly_segment_id', $assemblyId);
            $existing = $query->first();
            if ($existing) {
                return "An active verified Assembly President is already assigned to this Assembly Segment (#{$existing->volunteer_id} {$existing->full_name}).";
            }
        } elseif ($cadreLevel === 'mandal_president' && $mandalId) {
            $query->where('mandal_id', $mandalId);
            $existing = $query->first();
            if ($existing) {
                return "An active verified Mandal President is already assigned to this Mandal (#{$existing->volunteer_id} {$existing->full_name}).";
            }
        } elseif ($cadreLevel === 'panchayat_president' && $panchayatId) {
            $query->where('panchayat_id', $panchayatId);
            $existing = $query->first();
            if ($existing) {
                return "An active verified Panchayat President is already assigned to this Panchayat (#{$existing->volunteer_id} {$existing->full_name}).";
            }
        }

        return null;
    }

    /**
     * Validate parent-child relationship integrity across complete supplied chain:
     * State -> District -> Assembly -> Mandal -> Panchayat.
     * When Assembly Segment is supplied, Mandal MUST belong to that exact Assembly Segment (NULL fails).
     * Returns error message string if invalid hierarchy detected, null if valid.
     */
    public static function validateParentChildGeography(
        ?int $stateId,
        ?int $districtId,
        ?int $assemblyId,
        ?int $mandalId,
        ?int $panchayatId
    ): ?string {
        if ($districtId) {
            $district = GeoDistrict::find($districtId);
            if (!$district) {
                return "Selected District does not exist.";
            }
            if ($stateId && (int)$district->state_id !== $stateId) {
                return "The selected District does not belong to the selected State.";
            }
        }

        if ($assemblyId) {
            $segment = GeoAssemblySegment::find($assemblyId);
            if (!$segment) {
                return "Selected Assembly Segment does not exist.";
            }
            if ($districtId && (int)$segment->district_id !== $districtId) {
                return "The selected Assembly Segment does not belong to the selected District.";
            }
        }

        if ($mandalId) {
            $mandal = GeoMandal::find($mandalId);
            if (!$mandal) {
                return "Selected Mandal does not exist.";
            }
            if ($districtId && (int)$mandal->district_id !== $districtId) {
                return "The selected Mandal does not belong to the selected District.";
            }
            // Strict assembly check: When assemblyId is supplied, mandal.assembly_segment_id MUST match assemblyId exactly
            if ($assemblyId) {
                if (empty($mandal->assembly_segment_id) || (int)$mandal->assembly_segment_id !== $assemblyId) {
                    return "The selected Mandal does not belong to the selected Assembly Segment.";
                }
            }
        }

        if ($panchayatId) {
            $panchayat = GeoPanchayat::find($panchayatId);
            if (!$panchayat) {
                return "Selected Grama Panchayat does not exist.";
            }
            if ($mandalId && (int)$panchayat->mandal_id !== $mandalId) {
                return "The selected Grama Panchayat does not belong to the selected Mandal.";
            }
        }

        return null;
    }
}
