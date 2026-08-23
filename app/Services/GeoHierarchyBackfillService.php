<?php

namespace App\Services;

use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\GeoState;
use App\Models\GeoDistrict;
use App\Models\GeoAssemblySegment;
use App\Models\GeoMandal;
use App\Models\GeoPanchayat;
use App\Models\GeoAlias;
use Illuminate\Support\Facades\DB;

class GeoHierarchyBackfillService
{
    /**
     * Normalize a geographic name for canonical deterministic matching.
     */
    public static function normalize(string|null $value): string
    {
        if ($value === null) {
            return '';
        }
        $cleaned = preg_replace('/\s+/', ' ', trim($value));
        return strtolower($cleaned);
    }

    /**
     * Run backfill analysis or execution.
     *
     * @param bool $dryRun When true, strictly zero database writes are performed.
     * @param bool $onlyVolunteers
     * @param bool $onlyMemberships
     * @return array Summary metrics and classifications.
     */
    public function run(bool $dryRun = true, bool $onlyVolunteers = false, bool $onlyMemberships = false): array
    {
        $metrics = [
            'dry_run' => $dryRun,
            'volunteers_scanned' => 0,
            'memberships_scanned' => 0,
            'would_match_full' => 0,
            'would_match_partial' => 0,
            'already_mapped' => 0,
            'cadre_conflicts' => 0,
            'geographic_conflicts' => 0,
            'would_remain_unmapped' => 0,
            'matched_states' => 0,
            'matched_districts' => 0,
            'matched_assembly_segments' => 0,
            'matched_mandals' => 0,
            'matched_panchayats' => 0,
            'persisted_updates' => 0,
            'details' => [],
        ];

        // 1. Process Volunteers
        if (!$onlyMemberships) {
            $volunteers = Volunteer::all();
            $metrics['volunteers_scanned'] = $volunteers->count();

            foreach ($volunteers as $vol) {
                $res = $this->evaluateRecord($vol, 'volunteer', $dryRun);
                $this->accumulateMetrics($metrics, $res);
            }
        }

        // 2. Process Memberships
        if (!$onlyVolunteers) {
            $memberships = Membership::all();
            $metrics['memberships_scanned'] = $memberships->count();

            foreach ($memberships as $memb) {
                $res = $this->evaluateRecord($memb, 'membership', $dryRun);
                $this->accumulateMetrics($metrics, $res);
            }
        }

        return $metrics;
    }

    /**
     * Evaluate an individual record for deterministic matching and conflict detection.
     */
    public function evaluateRecord($record, string $type, bool $dryRun): array
    {
        $id = $record->id;
        $identifier = $type === 'volunteer' ? ($record->volunteer_id ?? "Vol#{$id}") : ($record->membership_id ?? "Memb#{$id}");

        // If already verified by admin, preserve untouched
        if ($record->geo_mapping_status === 'verified') {
            return [
                'type' => $type,
                'identifier' => $identifier,
                'classification' => 'ALREADY_MAPPED',
                'reason' => 'Record is already verified by Admin.',
                'matched_levels' => ['state' => (bool)$record->state_id, 'district' => (bool)$record->district_id, 'assembly' => (bool)$record->assembly_segment_id, 'mandal' => (bool)$record->mandal_id, 'panchayat' => (bool)$record->panchayat_id],
                'proposed_cadre' => $record->cadre_level,
                'persisted' => false,
            ];
        }

        // Extract raw legacy text
        $rawState = $record->state ?: ($type === 'volunteer' ? $record->membership?->state : null);
        $rawDistrict = $record->district ?: ($type === 'volunteer' ? $record->membership?->district : null);
        $rawAssembly = $record->assembly_segment ?: ($type === 'volunteer' ? $record->membership?->assembly_segment : null);
        $rawMandal = $record->mandal ?: ($type === 'volunteer' ? $record->membership?->mandal : null);
        $rawPanchayat = $record->grama_panchayat ?: ($type === 'volunteer' ? $record->membership?->grama_panchayat : null);

        $normState = self::normalize($rawState);
        $normDistrict = self::normalize($rawDistrict);
        $normAssembly = self::normalize($rawAssembly);
        $normMandal = self::normalize($rawMandal);
        $normPanchayat = self::normalize($rawPanchayat);

        $geoConflict = null;
        $matchedStateId = null;
        $matchedDistrictId = null;
        $matchedAssemblyId = null;
        $matchedMandalId = null;
        $matchedPanchayatId = null;

        // 1. Match State
        if ($normState !== '') {
            $state = GeoState::where('is_active', true)
                ->whereRaw('LOWER(name) = ?', [$normState])
                ->first();
            if ($state) {
                $matchedStateId = $state->id;
            }
        }

        // 2. Match District (Exact canonical or approved alias)
        if ($normDistrict !== '' && $matchedStateId) {
            $district = GeoDistrict::where('state_id', $matchedStateId)
                ->where('is_active', true)
                ->whereRaw('LOWER(name) = ?', [$normDistrict])
                ->first();

            if (!$district) {
                // Check approved alias
                $alias = GeoAlias::where('entity_type', 'district')
                    ->where('state_id', $matchedStateId)
                    ->whereRaw('LOWER(alias_name) = ?', [$normDistrict])
                    ->first();

                if ($alias) {
                    $district = GeoDistrict::find($alias->canonical_id);
                }
            }

            if ($district) {
                $matchedDistrictId = $district->id;
            } else {
                $geoConflict = "District '{$rawDistrict}' not recognized in State #{$matchedStateId} (requires Admin alias approval).";
            }
        }

        // 3. Match Assembly Segment
        if ($normAssembly !== '' && $matchedDistrictId) {
            $assembly = GeoAssemblySegment::where('district_id', $matchedDistrictId)
                ->where('is_active', true)
                ->whereRaw('LOWER(name) = ?', [$normAssembly])
                ->first();

            if ($assembly) {
                $matchedAssemblyId = $assembly->id;
            } else {
                $geoConflict = $geoConflict ?: "Assembly Segment '{$rawAssembly}' not recognized under District #{$matchedDistrictId}.";
            }
        }

        // 4. Match Mandal (Strict Assembly Parent Enforcement - No Cross-Assembly Fallback)
        if ($normMandal !== '' && $matchedDistrictId && !$geoConflict) {
            if ($matchedAssemblyId) {
                // When assembly segment is matched, mandal MUST belong to that exact assembly segment
                $mandal = GeoMandal::where('district_id', $matchedDistrictId)
                    ->where('assembly_segment_id', $matchedAssemblyId)
                    ->where('is_active', true)
                    ->whereRaw('LOWER(name) = ?', [$normMandal])
                    ->first();

                if ($mandal) {
                    $matchedMandalId = $mandal->id;
                } else {
                    // Check if mandal exists under a different assembly in this district
                    $crossAssemblyMandal = GeoMandal::where('district_id', $matchedDistrictId)
                        ->where('is_active', true)
                        ->whereRaw('LOWER(name) = ?', [$normMandal])
                        ->first();

                    if ($crossAssemblyMandal) {
                        $geoConflict = "Mandal '{$rawMandal}' belongs to a different Assembly Segment (#{$crossAssemblyMandal->assembly_segment_id}) than matched Assembly #{$matchedAssemblyId}.";
                    } else {
                        $geoConflict = "Mandal '{$rawMandal}' not found under Assembly Segment #{$matchedAssemblyId}.";
                    }
                }
            } else {
                // No assembly segment supplied; match mandal directly under district
                $mandal = GeoMandal::where('district_id', $matchedDistrictId)
                    ->where('is_active', true)
                    ->whereRaw('LOWER(name) = ?', [$normMandal])
                    ->first();

                if ($mandal) {
                    $matchedMandalId = $mandal->id;
                    if ($mandal->assembly_segment_id) {
                        $matchedAssemblyId = $mandal->assembly_segment_id;
                    }
                }
            }
        }

        // 5. Match Panchayat
        if ($normPanchayat !== '' && $matchedMandalId && !$geoConflict) {
            $panchayat = GeoPanchayat::where('mandal_id', $matchedMandalId)
                ->where('is_active', true)
                ->whereRaw('LOWER(name) = ?', [$normPanchayat])
                ->first();

            if ($panchayat) {
                $matchedPanchayatId = $panchayat->id;
            }
        }

        // 6. Cadre Evaluation for Volunteers
        $cadreConflict = null;
        $proposedCadre = null;

        if ($type === 'volunteer') {
            $cadreText = self::normalize($record->cadre);
            $roleText = self::normalize($record->role);

            $isMandalCadre = in_array($cadreText, ['mandal president', 'mandal presedient', 'mandal_president'], true);
            $isVillageCadre = in_array($cadreText, ['village president', 'panchayat president', 'village_president', 'panchayat_president'], true);
            $isAssemblyCadre = in_array($cadreText, ['assembly president', 'assembly segment president', 'assembly_president'], true);
            $isDistrictCadre = in_array($cadreText, ['district president', 'district_president'], true);
            $isStateCadre = in_array($cadreText, ['state president', 'state_president'], true);
            $isNationalCadre = in_array($cadreText, ['national president', 'national_president'], true);

            if ($isMandalCadre && ($roleText === 'village_president' || $roleText === 'panchayat_president')) {
                // Verified production conflict: Cadre says Mandal Presedient, role says village_president
                $cadreConflict = "Cadre conflict: cadre='{$record->cadre}' vs role='{$record->role}'. Requires Admin confirmation.";
                $proposedCadre = null;
            } elseif ($isMandalCadre) {
                $proposedCadre = 'mandal_president';
            } elseif ($isVillageCadre) {
                $proposedCadre = 'panchayat_president';
            } elseif ($isAssemblyCadre) {
                $proposedCadre = 'assembly_president';
            } elseif ($isDistrictCadre) {
                $proposedCadre = 'district_president';
            } elseif ($isStateCadre) {
                $proposedCadre = 'state_president';
            } elseif ($isNationalCadre) {
                $proposedCadre = 'national_president';
            } else {
                $proposedCadre = 'volunteer';
            }
        }

        // 7. Determine Classification
        if ($cadreConflict) {
            $classification = 'CADRE_CONFLICT';
            $mappingStatus = 'needs_review';
            $notes = $cadreConflict;
        } elseif ($geoConflict) {
            $classification = 'GEOGRAPHIC_CONFLICT';
            $mappingStatus = 'needs_review';
            $notes = $geoConflict;
        } elseif ($matchedPanchayatId && $matchedMandalId && $matchedAssemblyId && $matchedDistrictId && $matchedStateId) {
            // Full 5-tier match strictly requires State, District, Assembly Segment, Mandal, AND Panchayat
            $classification = 'WOULD_MATCH';
            $mappingStatus = 'matched';
            $notes = 'Deterministic match across all 5 canonical tiers.';
        } elseif ($matchedDistrictId && $matchedStateId) {
            $classification = 'WOULD_PARTIAL_MATCH';
            $mappingStatus = 'partial_matched';
            $notes = 'Matched upper geographic tiers (State/District); lower level missing or pending.';
        } else {
            $classification = 'WOULD_REMAIN_UNMAPPED';
            $mappingStatus = 'unmapped';
            $notes = 'Insufficient geographic master data to resolve legacy text.';
        }

        $persisted = false;

        // 8. Strict Non-Conflicting Write Policy in Non-Dry-Run Mode:
        // CADRE_CONFLICT, GEOGRAPHIC_CONFLICT, and WOULD_REMAIN_UNMAPPED must NOT be written by backfill.
        // Writes are ONLY permitted for WOULD_MATCH and WOULD_PARTIAL_MATCH.
        if (!$dryRun && in_array($classification, ['WOULD_MATCH', 'WOULD_PARTIAL_MATCH'], true)) {
            $updatePayload = [
                'state_id' => $matchedStateId,
                'district_id' => $matchedDistrictId,
                'assembly_segment_id' => $matchedAssemblyId,
                'mandal_id' => $matchedMandalId,
                'panchayat_id' => $matchedPanchayatId,
                'geo_mapping_status' => $mappingStatus,
                'geo_mapping_notes' => $notes,
            ];

            if ($type === 'volunteer' && $proposedCadre && !$cadreConflict) {
                $updatePayload['cadre_level'] = $proposedCadre;
            }

            DB::table($record->getTable())->where('id', $id)->update($updatePayload);
            $persisted = true;
        }

        return [
            'type' => $type,
            'identifier' => $identifier,
            'classification' => $classification,
            'reason' => $notes,
            'matched_levels' => [
                'state' => (bool)$matchedStateId,
                'district' => (bool)$matchedDistrictId,
                'assembly' => (bool)$matchedAssemblyId,
                'mandal' => (bool)$matchedMandalId,
                'panchayat' => (bool)$matchedPanchayatId,
            ],
            'proposed_cadre' => $proposedCadre,
            'persisted' => $persisted,
        ];
    }

    protected function accumulateMetrics(array &$metrics, array $res): void
    {
        match ($res['classification']) {
            'WOULD_MATCH' => $metrics['would_match_full']++,
            'WOULD_PARTIAL_MATCH' => $metrics['would_match_partial']++,
            'ALREADY_MAPPED' => $metrics['already_mapped']++,
            'CADRE_CONFLICT' => $metrics['cadre_conflicts']++,
            'GEOGRAPHIC_CONFLICT' => $metrics['geographic_conflicts']++,
            'WOULD_REMAIN_UNMAPPED' => $metrics['would_remain_unmapped']++,
            default => null,
        };

        if ($res['matched_levels']['state']) $metrics['matched_states']++;
        if ($res['matched_levels']['district']) $metrics['matched_districts']++;
        if ($res['matched_levels']['assembly']) $metrics['matched_assembly_segments']++;
        if ($res['matched_levels']['mandal']) $metrics['matched_mandals']++;
        if ($res['matched_levels']['panchayat']) $metrics['matched_panchayats']++;

        if ($res['persisted']) {
            $metrics['persisted_updates']++;
        }

        $metrics['details'][] = $res;
    }
}
