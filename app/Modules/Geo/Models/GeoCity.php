<?php

namespace App\Modules\Geo\Models;

use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\GeoCityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeoCity extends Model
{
    /** @use HasFactory<GeoCityFactory> */
    use HasFactory;

    use HasVisibility;

    protected $fillable = [
        'geo_country_id',
        'geo_region_id',
        'display',
        'rank',
        'title',
        'latitude',
        'longitude',
        'external_id',
    ];

    protected $casts = [
        'display'   => 'boolean',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(GeoCountry::class, 'geo_country_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(GeoRegion::class, 'geo_region_id');
    }
}
