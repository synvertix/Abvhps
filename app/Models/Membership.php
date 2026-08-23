<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    /**
     * The official database table associated with the model.
     *
     * @var string
     */
    protected $table = 'memberships';

    /**
     * The attributes that are mass assignable inside core systems.
     *
     * SECURITY RULE: Security-critical identity verification fields (identity_verified,
     * identity_verified_name, identity_verification_method, identity_verification_provider,
     * identity_verification_reference_id, identity_verification_id, identity_document_last4,
     * identity_verified_at, welcome_email_sent_at) are intentionally EXCLUDED from $fillable
     * to prevent browser mass-assignment. They are assigned explicitly from trusted server-side results.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'membership_id',
        'phone',
        'payment_status',
        'payment_id',
        'payment_gateway',
        'payment_order_id',
        'payment_amount',
        'payment_verified_at',
        'aadhaar_number',
        'full_name',
        'gender',
        'dob',
        'father_or_husband_name',
        'photo_path',
        'gotram',
        'occupation',
        'blood_group',
        'email',
        'permanent_address',
        'present_address',
        'pincode',
        'grama_panchayat',
        'mandal',
        'assembly_segment',
        'district',
        'state',
        'country',
        'is_completed',
        'is_aadhaar_verified',
        'aadhaar_verification_ref',
        'aadhaar_verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_aadhaar_verified'        => 'boolean',
        'identity_verified'          => 'boolean',
        'is_completed'               => 'boolean',
        'aadhaar_verified_at'        => 'datetime',
        'identity_verified_at'       => 'datetime',
        'welcome_email_sent_at'      => 'datetime',
        'payment_verified_at'        => 'datetime',
        'payment_amount'             => 'decimal:2',
    ];

    /**
     * Canonical single source of truth for identity verification.
     *
     * Rules:
     * 1. Generic verification requires: identity_verified=true, non-empty method, non-empty verified name, non-null verified_at.
     * 2. Legacy Aadhaar verification requires: is_aadhaar_verified=true AND non-empty full_name.
     */
    public function hasVerifiedIdentity(): bool
    {
        $genericVerified = ($this->identity_verified === true)
            && !empty($this->identity_verification_method)
            && !empty($this->identity_verified_name)
            && !is_null($this->identity_verified_at);

        $legacyAadhaarVerified = ($this->is_aadhaar_verified === true) && !empty($this->full_name);

        return $genericVerified || $legacyAadhaarVerified;
    }

    /**
     * Get human-readable label for identity verification method.
     */
    public function getIdentityMethodLabel(): string
    {
        if (!$this->hasVerifiedIdentity()) {
            return 'Pending';
        }

        $method = $this->identity_verification_method;
        if (empty($method) && $this->is_aadhaar_verified) {
            $method = 'aadhaar';
        }

        return match (strtolower((string)$method)) {
            'aadhaar'         => 'Aadhaar',
            'pan'             => 'PAN',
            'voter_id'        => 'Voter ID',
            'driving_licence' => 'Driving Licence',
            'passport'        => 'Passport',
            default           => 'Government ID',
        };
    }

    /**
     * Get safe masked document label (never exposes raw document number).
     */
    public function getIdentityDocumentMaskedLabel(): string
    {
        if (!$this->hasVerifiedIdentity()) {
            return 'Identity Pending';
        }

        $method = $this->identity_verification_method;
        if (empty($method) && $this->is_aadhaar_verified) {
            $method = 'aadhaar';
        }

        $last4 = $this->identity_document_last4;
        if (empty($last4) && !empty($this->aadhaar_number) && strlen((string)$this->aadhaar_number) >= 4) {
            $last4 = substr((string)$this->aadhaar_number, -4);
        }

        if (empty($last4)) {
            return 'Verified document details unavailable';
        }

        return match (strtolower((string)$method)) {
            'aadhaar'         => 'Aadhaar ending in ' . $last4,
            'pan'             => 'PAN ending in ' . $last4,
            'voter_id'        => 'Voter ID ending in ' . $last4,
            'driving_licence' => 'Driving Licence ending in ' . $last4,
            'passport'        => 'Passport File Number ending in ' . $last4,
            default           => 'Document ending in ' . $last4,
        };
    }

    /**
     * Get masked Aadhaar representation (XXXX-XXXX-1234) safely without raw number exposure.
     */
    public function getMaskedAadhaar(): ?string
    {
        if (!$this->hasVerifiedIdentity()) {
            return null;
        }

        $last4 = $this->identity_document_last4;
        if (empty($last4) && !empty($this->aadhaar_number) && strlen((string)$this->aadhaar_number) >= 4) {
            $last4 = substr((string)$this->aadhaar_number, -4);
        }

        if (!empty($last4)) {
            return 'XXXX-XXXX-' . $last4;
        }

        return null;
    }

    /**
     * Get canonical identity verification badge label.
     */
    public function getIdentityBadgeLabel(): string
    {
        if (!$this->hasVerifiedIdentity()) {
            return 'Identity Pending';
        }

        return '✓ ' . $this->getIdentityMethodLabel() . ' Verified';
    }

    /**
     * Get formatted verification timestamp in Asia/Kolkata timezone.
     */
    public function getIdentityVerifiedAtFormatted(): ?string
    {
        if (!$this->hasVerifiedIdentity()) {
            return null;
        }

        $dt = $this->identity_verified_at ?? $this->aadhaar_verified_at;
        if (!$dt) {
            return null;
        }

        return \Carbon\Carbon::parse($dt)->timezone('Asia/Kolkata')->format('d-M-Y h:i A') . ' IST';
    }

    /**
     * Get identity verification provider display label.
     */
    public function getIdentityVerificationProviderLabel(): string
    {
        if (!$this->hasVerifiedIdentity()) {
            return 'N/A';
        }

        if (!empty($this->identity_verification_provider)) {
            return ucfirst((string)$this->identity_verification_provider);
        }

        if ($this->is_aadhaar_verified) {
            return 'Legacy verification';
        }

        return 'Not recorded';
    }
}
