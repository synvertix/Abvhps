<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoPanchayat extends Model
{
    protected $table = 'geo_panchayats';

    protected $fillable = [
        'mandal_id',
        'name',
        'pincode',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mandal(): BelongsTo
    {
        return $this->belongsTo(GeoMandal::class, 'mandal_id');
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class, 'panchayat_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'panchayat_id');
    }
}
