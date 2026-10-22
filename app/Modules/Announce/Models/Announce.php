<?php

namespace App\Modules\Announce\Models;

use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

/**
 * One phrase of the running line above the site header.
 */
class Announce extends Model
{
    use HasVisibility;

    protected $fillable = [
        'display',
        'rank',
        'title',
        'url',
    ];

    protected $casts = [
        'display' => 'boolean',
    ];
}
