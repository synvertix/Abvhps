<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoState extends Model
{
    protected $table = 'geo_states';

    protected $fillable = [
        'country',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(GeoDistrict::class, 'state_id');
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class, 'state_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'state_id');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(GeoAlias::class, 'state_id');
    }
}
