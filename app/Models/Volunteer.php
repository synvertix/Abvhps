<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Volunteer extends Authenticatable
{
    use Notifiable;

    protected $table = 'volunteers';

    protected $fillable = [
        'membership_id',
        'phone',
        'qualification',
        'voter_id_number',
        'email',
        'password',
        'must_change_password',
        'remember_token',
        'credentials_created_at',
        'welcome_email_sent_at',
        'bank_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'branch_name',
        'nominee_name',
        'nominee_relation',
        'nominee_phone',
        'document_declaration_path',
        'document_voter_path',
        'document_bank_path',
        'status',
        'is_active',
        'cadre',
        'cadre_level',
        'role',
        'designation',
        'locality',
        'country',
        'state',
        'district',
        'assembly_segment',
        'mandal',
        'grama_panchayat',
        'state_id',
        'district_id',
        'assembly_segment_id',
        'mandal_id',
        'panchayat_id',
        'geo_mapping_status',
        'geo_mapping_notes',
        'volunteer_id',
        'volunteer_login_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'credentials_created_at' => 'datetime',
        'welcome_email_sent_at' => 'datetime',
        'state_id' => 'integer',
        'district_id' => 'integer',
        'assembly_segment_id' => 'integer',
        'mandal_id' => 'integer',
        'panchayat_id' => 'integer',
    ];

    // -------------------------------------------------------
    // Canonical Hierarchy Relationships
    // -------------------------------------------------------

    public function stateRelation(): BelongsTo
    {
        return $this->belongsTo(GeoState::class, 'state_id');
    }

    public function districtRelation(): BelongsTo
    {
        return $this->belongsTo(GeoDistrict::class, 'district_id');
    }

    public function assemblySegmentRelation(): BelongsTo
    {
        return $this->belongsTo(GeoAssemblySegment::class, 'assembly_segment_id');
    }

    public function mandalRelation(): BelongsTo
    {
        return $this->belongsTo(GeoMandal::class, 'mandal_id');
    }

    public function panchayatRelation(): BelongsTo
    {
        return $this->belongsTo(GeoPanchayat::class, 'panchayat_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membership_id', 'membership_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(VolunteerEvent::class, 'volunteer_id');
    }

    // -------------------------------------------------------
    // Event Statistics Helpers
    // -------------------------------------------------------

    public function conductedEventsCount(): int
    {
        return $this->events()->where('status', 'completed')->count();
    }

    public function upcomingEventsCount(): int
    {
        return $this->events()->where('status', 'upcoming')->count();
    }

    public function totalEventsCount(): int
    {
        return $this->events()->count();
    }

    public function totalParticipantsCount(): int
    {
        return VolunteerEventMember::whereIn('volunteer_event_id', $this->events()->pluck('id'))
            ->whereIn('participation_status', ['registered', 'participated', 'benefited'])
            ->count();
    }

    public function totalBeneficiariesCount(): int
    {
        return VolunteerEventMember::whereIn('volunteer_event_id', $this->events()->pluck('id'))
            ->where(function ($q) {
                $q->where('participation_type', 'beneficiary')
                  ->orWhere('participation_status', 'benefited');
            })
            ->count();
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    /**
     * Scope query to only APPROVED and ACTIVE volunteers for public display and login.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved')
                     ->where(function ($q) {
                         $q->whereNull('is_active')
                           ->orWhere('is_active', true);
                     });
    }

    // -------------------------------------------------------
    // Accessors (with fallback to linked Membership record)
    // -------------------------------------------------------

    public function getFullNameAttribute(): string
    {
        return $this->membership?->full_name ?? 'Volunteer';
    }

    public function getPhotoPathAttribute(): ?string
    {
        return $this->membership?->photo_path;
    }

    public function getResolvedCountryAttribute(): string
    {
        return ($this->attributes['country'] ?? null) ?: ($this->membership?->country ?: 'India');
    }

    public function getResolvedStateAttribute(): ?string
    {
        return $this->stateRelation?->name ?: (($this->attributes['state'] ?? null) ?: $this->membership?->state);
    }

    public function getResolvedDistrictAttribute(): ?string
    {
        return $this->districtRelation?->name ?: (($this->attributes['district'] ?? null) ?: $this->membership?->district);
    }

    public function getResolvedAssemblySegmentAttribute(): ?string
    {
        return $this->assemblySegmentRelation?->name ?: (($this->attributes['assembly_segment'] ?? null) ?: $this->membership?->assembly_segment);
    }

    public function getResolvedMandalAttribute(): ?string
    {
        return $this->mandalRelation?->name ?: (($this->attributes['mandal'] ?? null) ?: $this->membership?->mandal);
    }

    public function getResolvedGramaPanchayatAttribute(): ?string
    {
        return $this->panchayatRelation?->name ?: (($this->attributes['grama_panchayat'] ?? null) ?: $this->membership?->grama_panchayat);
    }

    public function getCadreLabelAttribute(): string
    {
        if (!empty($this->cadre_level)) {
            return self::cadreLevelToPublicTitle($this->cadre_level);
        }
        return $this->cadre ?: ($this->designation ?: 'Volunteer');
    }

    public static function cadreLevelToPublicTitle(?string $level): string
    {
        return match ($level) {
            'national_president' => 'National President',
            'state_president'    => 'State President',
            'district_president' => 'District President',
            'assembly_president' => 'Taluka President / Assembly Segment President',
            'mandal_president'   => 'Mandal President',
            'panchayat_president'=> 'Panchayat President',
            'volunteer'          => 'Volunteer',
            default              => ucfirst(str_replace('_', ' ', (string)$level ?: 'Volunteer')),
        };
    }

    public function isVerifiedPresident(): bool
    {
        return $this->status === 'approved'
            && ($this->is_active ?? true)
            && !empty($this->cadre_level)
            && in_array($this->cadre_level, [
                'national_president',
                'state_president',
                'district_president',
                'assembly_president',
                'mandal_president',
                'panchayat_president'
            ], true)
            && ($this->geo_mapping_status === 'verified');
    }

    public function getJurisdictionSummaryAttribute(): string
    {
        return match ($this->cadre_level) {
            'national_president' => 'National / All India Scope',
            'state_president'    => 'State: ' . ($this->resolved_state ?? 'Unassigned'),
            'district_president' => 'District: ' . ($this->resolved_district ?? 'Unassigned'),
            'assembly_president' => 'Assembly Segment: ' . ($this->resolved_assembly_segment ?? 'Unassigned'),
            'mandal_president'   => 'Mandal: ' . ($this->resolved_mandal ?? 'Unassigned'),
            'panchayat_president'=> 'Panchayat: ' . ($this->resolved_grama_panchayat ?? 'Unassigned'),
            default              => $this->locality ?? 'General Volunteer',
        };
    }
}
