<?php

namespace App\Modules\Geo\Models;

use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\GeoRegionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeoRegion extends Model
{
    /** @use HasFactory<GeoRegionFactory> */
    use HasFactory;
    use HasVisibility;

    protected $fillable = [
        'geo_country_id',
        'display',
        'rank',
        'title',
        'code',
    ];

    protected $casts = [
        'display' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(GeoCountry::class, 'geo_country_id');
    }
}
