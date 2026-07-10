<?php

namespace Tests\Unit\Shop\Queries\Product;

use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Queries\Product\FindProductByIdQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindProductByIdQueryTest extends TestCase
{
    use RefreshDatabase;

    private FindProductByIdQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = app(FindProductByIdQuery::class);
    }

    public function test_get_product_by_id_returns_visible_product_with_brand_loaded(): void
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        $result = $this->query->handle($product->id);

        $this->assertSame($product->id, $result->id);
        $this->assertTrue($result->relationLoaded('brand'));
        $this->assertSame($brand->id, $result->brand->id);
    }

    public function test_get_product_by_id_throws_for_invisible_product(): void
    {
        $product = Product::factory()->invisible()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->query->handle($product->id);
    }

    public function test_get_product_by_id_throws_for_nonexistent_id(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->query->handle(999999);
    }
}
