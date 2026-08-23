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
}
