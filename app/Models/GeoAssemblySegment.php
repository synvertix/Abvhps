<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoAssemblySegment extends Model
{
    protected $table = 'geo_assembly_segments';

    protected $fillable = [
        'district_id',
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

    public function mandals(): HasMany
    {
        return $this->hasMany(GeoMandal::class, 'assembly_segment_id');
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class, 'assembly_segment_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'assembly_segment_id');
    }
}
