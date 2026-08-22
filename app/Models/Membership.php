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
     * Maps perfectly with payment tokens, Aadhaar, and address configurations.
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
        'is_aadhaar_verified' => 'boolean',
        'is_completed' => 'boolean',
        'aadhaar_verified_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'payment_amount' => 'decimal:2',
    ];
}
