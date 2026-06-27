<?php

namespace Tests\Feature;

use App\Modules\News\Models\News;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HasImageConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    public function test_creates_thumb_on_model_create(): void
    {
        Storage::put('shop_products/temp/img/product.jpg', $this->createJpeg(600, 400));

        $product = Product::factory()->create([
            'img' => 'shop_products/temp/img/product.jpg',
        ]);

        $expectedOriginal = "shop_products/{$product->id}/img/product.jpg";

        $this->assertSame($expectedOriginal, $product->fresh()->img);
        $this->assertTrue(Storage::exists("thumb/{$expectedOriginal}"));
    }

    public function test_does_not_create_large_on_create(): void
    {
        Storage::put('shop_products/temp/img/product.jpg', $this->createJpeg(600, 400));

        $product = Product::factory()->create([
            'img' => 'shop_products/temp/img/product.jpg',
        ]);

        $expectedOriginal = "shop_products/{$product->id}/img/product.jpg";

        $this->assertFalse(Storage::exists("large/{$expectedOriginal}"));
    }

    public function test_replaces_thumb_on_img_update(): void
    {
        $product = Product::factory()->create(['img' => null]);

        $originalPath = "shop_products/{$product->id}/img/old.jpg";
        $newPath = "shop_products/{$product->id}/img/new.jpg";

        Storage::put($originalPath, $this->createJpeg(600, 400));
        Storage::put($newPath, $this->createJpeg(800, 600));

        $product->update(['img' => $originalPath]);
        $this->assertTrue(Storage::exists("thumb/{$originalPath}"));

        $product->update(['img' => $newPath]);

        $this->assertFalse(Storage::exists("thumb/{$originalPath}"));
        $this->assertFalse(Storage::exists($originalPath));
        $this->assertTrue(Storage::exists("thumb/{$newPath}"));
        $this->assertFalse(Storage::exists("large/{$newPath}"));
    }

    public function test_deletes_conversions_on_model_delete(): void
    {
        $product = Product::factory()->create(['img' => null]);

        $originalPath = "shop_products/{$product->id}/img/product.jpg";
        Storage::put($originalPath, $this->createJpeg(600, 400));

        $product->update(['img' => $originalPath]);

        Storage::put("large/{$originalPath}", 'large-preview');

        $productId = $product->id;
        $product->delete();

        $this->assertFalse(Storage::exists("shop_products/{$productId}"));
        $this->assertFalse(Storage::exists("thumb/shop_products/{$productId}"));
        $this->assertFalse(Storage::exists("large/shop_products/{$productId}"));
    }

    public function test_deletes_conversions_when_removed_from_imgs_array(): void
    {
        $news = News::factory()->create(['img' => null, 'imgs' => null]);

        $path1 = "news/{$news->id}/imgs/photo1.jpg";
        $path2 = "news/{$news->id}/imgs/photo2.jpg";

        Storage::put($path1, $this->createJpeg(600, 400));
        Storage::put($path2, $this->createJpeg(600, 400));

        $news->update(['imgs' => [$path1, $path2]]);

        $this->assertTrue(Storage::exists("thumb/{$path1}"));
        $this->assertTrue(Storage::exists("thumb/{$path2}"));

        Storage::put("large/{$path1}", 'large-preview');

        $news->update(['imgs' => [$path2]]);

        $this->assertFalse(Storage::exists($path1));
        $this->assertFalse(Storage::exists("thumb/{$path1}"));
        $this->assertFalse(Storage::exists("large/{$path1}"));
        $this->assertTrue(Storage::exists($path2));
        $this->assertTrue(Storage::exists("thumb/{$path2}"));
    }

    public function test_product_gallery_temp_move_and_thumb(): void
    {
        Storage::put('shop_products/temp/gallery/photo.jpg', $this->createJpeg(600, 400));

        $product = Product::factory()->create([
            'imgs' => ['shop_products/temp/gallery/photo.jpg'],
        ]);

        $expectedPath = "shop_products/{$product->id}/gallery/photo.jpg";

        $this->assertSame([$expectedPath], $product->fresh()->imgs);
        $this->assertTrue(Storage::exists($expectedPath));
        $this->assertTrue(Storage::exists("thumb/{$expectedPath}"));
        $this->assertFalse(Storage::exists('shop_products/temp/gallery/photo.jpg'));
    }

    private function createJpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        ob_start();
        imagejpeg($image);
        $content = ob_get_clean();

        imagedestroy($image);

        return $content;
    }
}
