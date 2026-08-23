<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'role',
        'designation',
        'locality',
        'country',
        'state',
        'district',
        'assembly_segment',
        'mandal',
        'grama_panchayat',
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
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function membership()
    {
        return $this->belongsTo(Membership::class, 'membership_id', 'membership_id');
    }

    public function events()
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
        return ($this->attributes['state'] ?? null) ?: $this->membership?->state;
    }

    public function getResolvedDistrictAttribute(): ?string
    {
        return ($this->attributes['district'] ?? null) ?: $this->membership?->district;
    }

    public function getResolvedAssemblySegmentAttribute(): ?string
    {
        return ($this->attributes['assembly_segment'] ?? null) ?: $this->membership?->assembly_segment;
    }

    public function getResolvedMandalAttribute(): ?string
    {
        return ($this->attributes['mandal'] ?? null) ?: $this->membership?->mandal;
    }

    public function getResolvedGramaPanchayatAttribute(): ?string
    {
        return ($this->attributes['grama_panchayat'] ?? null) ?: $this->membership?->grama_panchayat;
    }

    public function getCadreLabelAttribute(): string
    {
        return $this->cadre ?: ($this->designation ?: 'Volunteer');
    }
}
