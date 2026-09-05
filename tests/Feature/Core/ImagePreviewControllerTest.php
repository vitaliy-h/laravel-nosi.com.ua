<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagePreviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        Config::set('image_sizes', [
            'thumb' => [
                'shop_products' => ['width' => 100, 'height' => 100, 'mode' => 'crop'],
            ],
        ]);

        Storage::put('shop_products/1/img/product.jpg', $this->createJpeg(600, 400));
    }

    public function test_returns_404_for_unknown_preset(): void
    {
        $this->get('/storage/nonexistent/shop_products/1/img/product.jpg')
            ->assertStatus(404);
    }

    public function test_returns_404_when_original_image_does_not_exist(): void
    {
        $this->get('/storage/thumb/shop_products/999/img/missing.jpg')
            ->assertStatus(404);
    }

    public function test_returns_404_when_entity_not_configured_for_preset(): void
    {
        Config::set('image_sizes', ['thumb' => []]);

        $this->get('/storage/thumb/shop_products/1/img/product.jpg')
            ->assertStatus(404);
    }

    public function test_generates_preview_on_first_request(): void
    {
        $previewPath = 'thumb/shop_products/1/img/product.jpg';

        $this->assertFalse(Storage::exists($previewPath));

        $response = $this->get('/storage/thumb/shop_products/1/img/product.jpg');

        $response->assertOk();
        $this->assertStringContainsString(
            'image/',
            strtolower($response->headers->get('content-type') ?? ''),
        );
        $this->assertTrue(Storage::exists($previewPath));
    }

    public function test_serves_cached_preview_on_second_request_without_re_generating(): void
    {
        $previewPath = 'thumb/shop_products/1/img/product.jpg';

        $this->get('/storage/thumb/shop_products/1/img/product.jpg')->assertOk();

        $mtime = filemtime(Storage::path($previewPath));

        $this->get('/storage/thumb/shop_products/1/img/product.jpg')->assertOk();

        $this->assertSame($mtime, filemtime(Storage::path($previewPath)));
    }

    public function test_serves_pre_generated_thumb_without_regenerating(): void
    {
        $previewPath = 'thumb/shop_products/1/img/product.jpg';

        Storage::put($previewPath, $this->createJpeg(100, 100));

        $mtime = filemtime(Storage::path($previewPath));

        $this->get('/storage/thumb/shop_products/1/img/product.jpg')->assertOk();

        $this->assertSame($mtime, filemtime(Storage::path($previewPath)));
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
