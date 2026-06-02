<?php

namespace App\Modules\Geo\Models;

use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class GeoCountry extends Model
{
    use HasVisibility;

    protected $fillable = [
        'display',
        'rank',
        'title',
        'iso2',
        'iso3',
        'phone_code',
        'currency_code',
        'timezone',
    ];

    protected $casts = [
        'display' => 'boolean',
    ];
}
