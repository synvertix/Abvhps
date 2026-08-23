<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoDistrict extends Model
{
    protected $table = 'geo_districts';

    protected $fillable = [
        'state_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(GeoState::class, 'state_id');
    }

    public function assemblySegments(): HasMany
    {
        return $this->hasMany(GeoAssemblySegment::class, 'district_id');
    }

    public function mandals(): HasMany
    {
        return $this->hasMany(GeoMandal::class, 'district_id');
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class, 'district_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'district_id');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(GeoAlias::class, 'district_id');
    }
}
