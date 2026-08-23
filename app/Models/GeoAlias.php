<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeoAlias extends Model
{
    protected $table = 'geo_aliases';

    protected $fillable = [
        'entity_type',
        'alias_name',
        'canonical_id',
        'state_id',
        'district_id',
        'approved_by_admin_id',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(GeoState::class, 'state_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(GeoDistrict::class, 'district_id');
    }
}
