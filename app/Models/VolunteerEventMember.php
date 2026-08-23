<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\TinyProofImageService;

class VolunteerEventMember extends Model
{
    protected $table = 'volunteer_event_members';

    protected $fillable = [
        'volunteer_event_id',
        'membership_record_id',
        'membership_id',
        'participation_type',
        'participation_status',
        'benefit_details',
        'notes',
        'proof_image_path',
        'proof_image_size_bytes',
        'proof_image_mime',
        'proof_image_width',
        'proof_image_height',
        'added_by_volunteer_id',
    ];

    protected $casts = [
        'proof_image_size_bytes' => 'integer',
        'proof_image_width'      => 'integer',
        'proof_image_height'     => 'integer',
    ];

    public const PARTICIPATION_TYPES = [
        'participant'       => 'Participant',
        'beneficiary'       => 'Beneficiary',
        'volunteer_support' => 'Volunteer Support',
        'other'             => 'Other',
    ];

    public const PARTICIPATION_STATUSES = [
        'registered'   => 'Registered',
        'participated' => 'Participated',
        'benefited'    => 'Benefited',
        'absent'       => 'Absent',
    ];

    /**
     * Model-level Invariant Guard for Ultra-Tiny <= 2048 Bytes Proof Images
     */
    protected static function booted(): void
    {
        static::saving(function (VolunteerEventMember $model) {
            if ($model->proof_image_size_bytes !== null && $model->proof_image_size_bytes > TinyProofImageService::MAX_BYTES) {
                throw new \InvalidArgumentException("proof_image_size_bytes ({$model->proof_image_size_bytes}) cannot exceed " . TinyProofImageService::MAX_BYTES . " bytes.");
            }
            if ($model->proof_image_path !== null && ($model->proof_image_size_bytes === null || $model->proof_image_size_bytes <= 0)) {
                throw new \InvalidArgumentException("proof_image_path requires valid positive proof_image_size_bytes <= 2048.");
            }
        });
    }

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function event(): BelongsTo
    {
        return $this->belongsTo(VolunteerEvent::class, 'volunteer_event_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membership_record_id');
    }

    public function addedByVolunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class, 'added_by_volunteer_id');
    }

    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------

    public function getMemberNameAttribute(): string
    {
        return $this->membership?->full_name ?? 'Member';
    }

    public function getFormattedProofSizeAttribute(): string
    {
        if (!$this->proof_image_size_bytes) {
            return '';
        }

        if ($this->proof_image_size_bytes < 1024) {
            return $this->proof_image_size_bytes . ' B';
        }

        return round($this->proof_image_size_bytes / 1024, 1) . ' KB';
    }
}
