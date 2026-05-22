<?php

namespace App\Modules\Shop\Http\Models;

use App\Modules\Core\Traits\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class ShopParameter extends Model
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
