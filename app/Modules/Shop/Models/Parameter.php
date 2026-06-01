<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use HasVisibility;

    protected $table = 'shop_parameters';

    protected $fillable = [
        'name',
        'type',
        'rank',
        'display',
    ];

    protected $casts = [
        'display' => 'boolean',
    ];
}
