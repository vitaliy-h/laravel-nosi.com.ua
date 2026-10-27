<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\AttributeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A product parameter (size, season, material…) with a fixed list of values;
 * a product can have several values of one parameter.
 */
class Attribute extends Model
{
    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    use HasVisibility;

    protected $table = 'shop_attributes';

    protected $fillable = [
        'display',
        'is_filterable',
        'rank',
        'title',
        'code',
    ];

    protected $casts = [
        'display'       => 'boolean',
        'is_filterable' => 'boolean',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)->orderBy('rank')->orderBy('id');
    }

    protected static function newFactory(): AttributeFactory
    {
        return AttributeFactory::new();
    }
}
