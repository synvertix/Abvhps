<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoMandal extends Model
{
    protected $table = 'geo_mandals';

    protected $fillable = [
        'district_id',
        'assembly_segment_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(GeoDistrict::class, 'district_id');
    }

    public function assemblySegment(): BelongsTo
    {
        return $this->belongsTo(GeoAssemblySegment::class, 'assembly_segment_id');
    }

    public function panchayats(): HasMany
    {
        return $this->hasMany(GeoPanchayat::class, 'mandal_id');
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class, 'mandal_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'mandal_id');
    }
}
