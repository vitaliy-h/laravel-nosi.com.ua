<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * One value of a parameter («Зима» of «Сезон»).
 */
class AttributeOption extends Model
{
    protected $table = 'shop_attribute_options';

    protected $fillable = [
        'attribute_id',
        'rank',
        'title',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shop_attribute_option_product', 'option_id', 'product_id');
    }
}
