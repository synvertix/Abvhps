<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Card representation for mobile digital ID rendering.
     */
    public function toArray(Request $request): array
    {
        return [
            'membership_id'          => $this->membership_id,
            'full_name'              => $this->full_name,
            'father_or_husband_name' => $this->father_or_husband_name,
            'dob'                    => $this->dob,
            'blood_group'            => $this->blood_group,
            'phone'                  => $this->phone,
            'photo_url'              => $this->photo_path ? url('storage/' . $this->photo_path) : null,
            'state'                  => $this->stateRelation?->name ?? $this->state,
            'district'               => $this->districtRelation?->name ?? $this->district,
            'assembly_segment'       => $this->assemblySegmentRelation?->name ?? $this->assembly_segment,
            'mandal'                 => $this->mandalRelation?->name ?? $this->mandal,
            'grama_panchayat'        => $this->panchayatRelation?->name ?? $this->grama_panchayat,
            'pincode'                => $this->pincode,
            'is_completed'           => (bool) $this->is_completed,
            'is_identity_verified'   => $this->hasVerifiedIdentity(),
            'identity_badge'         => $this->getIdentityBadgeLabel(),
            'identity_document_masked' => $this->getIdentityDocumentMaskedLabel(),
            'issued_date'            => $this->payment_verified_at?->format('d-M-Y') ?? $this->created_at?->format('d-M-Y'),
        ];
    }
}
