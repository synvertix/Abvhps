<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VolunteerEvent extends Model
{
    protected $table = 'volunteer_events';

    protected $fillable = [
        'volunteer_id',
        'title',
        'event_type',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'venue',
        'village',
        'mandal',
        'district',
        'state',
        'status',
        'outcome',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    // Canonical service types
    public const SERVICE_TYPES = [
        'Temple seva'                 => 'Temple seva',
        'Annadanam'                   => 'Annadanam',
        'Goshala seva'                => 'Goshala seva',
        'Medical/service camp'        => 'Medical / Health Camp',
        'Awareness program'           => 'Awareness Program',
        'Community support'           => 'Community Support',
        'Relief activity'             => 'Relief Activity',
        'Religious/cultural activity' => 'Religious / Cultural Activity',
        'Other authorized service'    => 'Other Authorized Service',
    ];

    // Allowed status values
    public const STATUSES = [
        'upcoming'  => 'Upcoming',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class, 'volunteer_id');
    }

    public function eventMembers(): HasMany
    {
        return $this->hasMany(VolunteerEventMember::class, 'volunteer_event_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            Membership::class,
            'volunteer_event_members',
            'volunteer_event_id',
            'membership_record_id'
        )->withPivot([
            'participation_type',
            'participation_status',
            'benefit_details',
            'notes',
            'proof_image_path',
            'proof_image_size_bytes',
        ])->withTimestamps();
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForVolunteer($query, $volunteerId)
    {
        return $query->where('volunteer_id', $volunteerId);
    }

    // -------------------------------------------------------
    // Accessors & Helpers
    // -------------------------------------------------------

    public function getParticipantsCountAttribute(): int
    {
        return $this->eventMembers()
            ->whereIn('participation_status', ['registered', 'participated', 'benefited'])
            ->count();
    }

    public function getBeneficiariesCountAttribute(): int
    {
        return $this->eventMembers()
            ->where(function ($q) {
                $q->where('participation_type', 'beneficiary')
                  ->orWhere('participation_status', 'benefited');
            })
            ->count();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'upcoming'  => 'bg-blue-100 text-blue-800 border-blue-200',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
            default     => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function isEditableBy(Volunteer $volunteer): bool
    {
        return (int)$this->volunteer_id === (int)$volunteer->id;
    }
}
