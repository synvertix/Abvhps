<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VolunteerProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Strict allowlist: Never return password hashes, raw identity documents, bank details, or internal tokens.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'volunteer_id'        => $this->volunteer_id,
            'volunteer_login_id'  => $this->volunteer_login_id,
            'full_name'           => $this->full_name,
            'phone'               => $this->phone,
            'email'               => $this->email,
            'photo_url'           => $this->photo_path ? url('storage/' . $this->photo_path) : null,
            'status'              => $this->status,
            'is_active'           => (bool) $this->is_active,
            'cadre'               => $this->cadre,
            'cadre_level'         => $this->cadre_level,
            'cadre_label'         => $this->cadre_label,
            'designation'         => $this->designation,
            'jurisdiction_summary'=> $this->jurisdiction_summary,
            'country'             => $this->resolved_country,
            'state'               => $this->resolved_state,
            'district'            => $this->resolved_district,
            'assembly_segment'    => $this->resolved_assembly_segment,
            'mandal'              => $this->resolved_mandal,
            'grama_panchayat'     => $this->resolved_grama_panchayat,
            'state_id'            => $this->state_id,
            'district_id'         => $this->district_id,
            'assembly_segment_id' => $this->assembly_segment_id,
            'mandal_id'           => $this->mandal_id,
            'panchayat_id'        => $this->panchayat_id,
            'geo_mapping_status'  => $this->geo_mapping_status,
            'must_change_password'=> (bool) $this->must_change_password,
        ];
    }
}
