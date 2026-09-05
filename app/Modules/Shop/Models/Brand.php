<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasSeoFields;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    use HasImage;
    use HasSeoFields;

    protected $table = 'shop_brands';

    protected $fillable = [
        'display',
        'rank',
        'title',
        'slug',
        'img',
        'text',
        'meta_title',
        'meta_description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
