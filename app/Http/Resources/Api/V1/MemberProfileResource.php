<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Strict allowlist: Never return raw Aadhaar numbers, PAN, full document numbers, or internal payment secrets.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'membership_id'          => $this->membership_id,
            'phone'                  => $this->phone,
            'full_name'              => $this->full_name,
            'gender'                 => $this->gender,
            'dob'                    => $this->dob,
            'father_or_husband_name' => $this->father_or_husband_name,
            'occupation'             => $this->occupation,
            'blood_group'            => $this->blood_group,
            'email'                  => $this->email,
            'present_address'        => $this->present_address,
            'pincode'                => $this->pincode,
            'photo_url'              => $this->photo_path ? url('storage/' . $this->photo_path) : null,
            'country'                => $this->country ?: 'India',
            'state'                  => $this->stateRelation?->name ?? $this->state,
            'district'               => $this->districtRelation?->name ?? $this->district,
            'assembly_segment'       => $this->assemblySegmentRelation?->name ?? $this->assembly_segment,
            'mandal'                 => $this->mandalRelation?->name ?? $this->mandal,
            'grama_panchayat'        => $this->panchayatRelation?->name ?? $this->grama_panchayat,
            'is_completed'           => (bool) $this->is_completed,
            'is_identity_verified'   => $this->hasVerifiedIdentity(),
            'identity_badge'         => $this->getIdentityBadgeLabel(),
            'identity_method'        => $this->getIdentityMethodLabel(),
            'identity_document_masked' => $this->getIdentityDocumentMaskedLabel(),
            'identity_verified_at'   => $this->getIdentityVerifiedAtFormatted(),
            'payment_status'         => $this->payment_status,
            'payment_verified_at'    => $this->payment_verified_at?->format('d-M-Y H:i:s'),
            'created_at'             => $this->created_at?->format('d-M-Y'),
        ];
    }
}
