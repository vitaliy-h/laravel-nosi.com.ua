<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasSeoFields;
use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasImage;
    use HasSeoFields;
    use HasVisibility;

    /** How many sets («1 шт.», «5 шт.», «10 шт.») a product can be sold in. */
    public const int MAX_PACKS = 3;

    protected array $imgArrayFields = ['imgs'];

    protected $table = 'shop_products';

    protected $fillable = [
        'display',
        'is_hit',
        'rank',
        'category_id',
        'brand_id',
        'price',
        'packs',
        'is_pack',
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
        'display' => 'boolean',
        'is_hit'  => 'boolean',
        'is_pack' => 'boolean',
        'price'   => 'float',
        'packs'   => 'array',
        'imgs'    => 'array',
    ];

    /**
     * `packs` is what the customer picks from; `price` mirrors the price of the
     * smallest set, so the catalogue filter and sorting keep working on a plain
     * column. Either side may be the one written: the admin form and the seeder
     * fill `packs`, the inline price column and older code fill `price`.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $packs = self::normalizePacks($product->packs);

            if ($product->isDirty('price') && !$product->isDirty('packs') && $product->price !== null) {
                $packs[0] = ['quantity' => $packs[0]['quantity'] ?? 1, 'price' => (float) $product->price];
            } elseif ($packs === [] && $product->price !== null) {
                $packs = [['quantity' => 1, 'price' => (float) $product->price]];
            }

            $product->packs = $packs ?: null;
            $product->price = $packs[0]['price'] ?? null;
        });
    }

    /**
     * Sets with a price only, one per quantity, smallest first.
     *
     * @return array<int, array{quantity: int, price: float}>
     */
    public static function normalizePacks(?array $packs): array
    {
        return collect($packs ?? [])
            ->filter(fn ($pack): bool => is_array($pack)
                && (int) ($pack['quantity'] ?? 0) > 0
                && is_numeric($pack['price'] ?? null))
            ->map(fn (array $pack): array => [
                'quantity' => (int) $pack['quantity'],
                'price'    => (float) $pack['price'],
            ])
            ->unique('quantity')
            ->sortBy('quantity')
            ->take(self::MAX_PACKS)
            ->values()
            ->all();
    }

    /**
     * The set of the given size, or null when the product is not sold so.
     *
     * @return array{quantity: int, price: float}|null
     */
    public function pack(int $quantity): ?array
    {
        return collect(self::normalizePacks($this->packs))->firstWhere('quantity', $quantity);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * The chosen values of all parameters (size, season, material…) at once;
     * group by `attribute_id` to tell them apart.
     */
    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'shop_attribute_option_product', 'product_id', 'option_id');
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
