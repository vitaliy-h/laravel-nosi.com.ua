<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasSeoFields;
use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasImage;
    use HasSeoFields;
    use HasVisibility;

    protected array $imgArrayFields = ['imgs'];

    protected $table = 'shop_products';

    protected $fillable = [
        'display',
        'is_hit',
        'rank',
        'category_id',
        'brand_id',
        'price',
        'price2',
        'article',
        'title',
        'slug',
        'img',
        'imgs',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'display'       => 'boolean',
        'is_hit'        => 'boolean',
        'price'         => 'float',
        'price2'        => 'float',
        'imgs'          => 'array',
        'rating'        => 'float',
        'reviews_count' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function visibleReviews()
    {
        return $this->hasMany(ProductReview::class)->visible();
    }

    /**
     * The product's public URL segment — the slug alone is not unique.
     */
    protected function slugWithId(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->slug . '-' . $this->id,
        );
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
