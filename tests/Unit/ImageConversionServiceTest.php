<?php

namespace Tests\Unit;

use App\Modules\Core\Services\ImageConversionService;
use App\Modules\Core\Services\ImageResizer;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ImageConversionServiceTest extends TestCase
{
    private ImageConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        $this->service = app(ImageConversionService::class);
    }

    public function test_resolve_size_config_returns_config_for_known_entity(): void
    {
        $config = $this->service->resolveSizeConfig(
            'thumb',
            'shop_products/1/img/product.jpg',
        );

        $this->assertSame([
            'width' => 500,
            'height' => 500,
            'mode' => 'contain',
        ], $config);
    }

    public function test_resolve_size_config_returns_null_for_unknown_entity(): void
    {
        $config = $this->service->resolveSizeConfig(
            'large',
            'shop_categories/1/img/category.jpg',
        );

        $this->assertNull($config);
    }

    public function test_preview_path_builds_preset_prefixed_path(): void
    {
        $path = $this->service->previewPath(
            'thumb',
            'shop_products/1/img/product.jpg',
        );

        $this->assertSame('thumb/shop_products/1/img/product.jpg', $path);
    }

    public function test_generate_creates_preview_when_original_exists(): void
    {
        Storage::put('shop_products/1/img/product.jpg', $this->createJpeg(600, 400));

        $this->service->generate('shop_products/1/img/product.jpg', 'thumb');

        $this->assertTrue(Storage::exists('thumb/shop_products/1/img/product.jpg'));
    }

    public function test_generate_is_no_op_when_original_missing(): void
    {
        $resizer = Mockery::mock(ImageResizer::class);
        $resizer->shouldNotReceive('resize');
        $service = new ImageConversionService($resizer);

        $service->generate('shop_products/1/img/missing.jpg', 'thumb');

        $this->assertFalse(Storage::exists('thumb/shop_products/1/img/missing.jpg'));
    }

    public function test_generate_is_no_op_when_size_config_missing(): void
    {
        Storage::put('shop_categories/1/img/category.jpg', $this->createJpeg(600, 400));

        $resizer = Mockery::mock(ImageResizer::class);
        $resizer->shouldNotReceive('resize');
        $service = new ImageConversionService($resizer);

        $service->generate('shop_categories/1/img/category.jpg', 'large');

        $this->assertFalse(Storage::exists('large/shop_categories/1/img/category.jpg'));
    }

    public function test_generate_is_no_op_when_preview_already_exists(): void
    {
        Storage::put('shop_products/1/img/product.jpg', $this->createJpeg(600, 400));
        Storage::put('thumb/shop_products/1/img/product.jpg', 'existing-preview');

        $resizer = Mockery::mock(ImageResizer::class);
        $resizer->shouldNotReceive('resize');
        $service = new ImageConversionService($resizer);

        $service->generate('shop_products/1/img/product.jpg', 'thumb');

        $this->assertSame(
            'existing-preview',
            Storage::get('thumb/shop_products/1/img/product.jpg'),
        );
    }

    public function test_generate_is_idempotent(): void
    {
        Storage::put('shop_products/1/img/product.jpg', $this->createJpeg(600, 400));

        $this->service->generate('shop_products/1/img/product.jpg', 'thumb');
        $firstPreview = Storage::get('thumb/shop_products/1/img/product.jpg');

        $this->service->generate('shop_products/1/img/product.jpg', 'thumb');

        $this->assertSame(
            $firstPreview,
            Storage::get('thumb/shop_products/1/img/product.jpg'),
        );
    }

    public function test_generate_eager_creates_all_configured_presets(): void
    {
        Storage::put('shop_products/1/img/product.jpg', $this->createJpeg(600, 400));

        $this->service->generateEager('shop_products/1/img/product.jpg');

        $this->assertTrue(Storage::exists('thumb/shop_products/1/img/product.jpg'));
        $this->assertTrue(Storage::exists('large/shop_products/1/img/product.jpg'));
    }

    public function test_generate_eager_skips_presets_without_entity_config(): void
    {
        Storage::put('shop_categories/1/img/category.jpg', $this->createJpeg(600, 400));

        $this->service->generateEager('shop_categories/1/img/category.jpg');

        $this->assertTrue(Storage::exists('thumb/shop_categories/1/img/category.jpg'));
        $this->assertFalse(Storage::exists('large/shop_categories/1/img/category.jpg'));
    }

    public function test_generate_eager_skips_empty_path(): void
    {
        $resizer = Mockery::mock(ImageResizer::class);
        $resizer->shouldNotReceive('resize');
        $service = new ImageConversionService($resizer);

        $service->generateEager('');

        $this->assertTrue(true);
    }

    public function test_generate_eager_reports_errors_without_throwing(): void
    {
        Storage::put('shop_products/1/img/product.jpg', $this->createJpeg(600, 400));

        $resizer = Mockery::mock(ImageResizer::class);
        $resizer->shouldReceive('resize')
            ->twice()
            ->andThrow(new \RuntimeException('Resize failed'));
        $service = new ImageConversionService($resizer);

        $service->generateEager('shop_products/1/img/product.jpg');

        $this->assertFalse(Storage::exists('thumb/shop_products/1/img/product.jpg'));
        $this->assertFalse(Storage::exists('large/shop_products/1/img/product.jpg'));
    }

    public function test_delete_for_field_removes_preset_field_directories(): void
    {
        Storage::put('thumb/shop_products/42/img/old.jpg', 'thumb-old');
        Storage::put('large/shop_products/42/img/old.jpg', 'large-old');
        Storage::put('thumb/shop_products/42/gallery/other.jpg', 'thumb-gallery');

        $this->service->deleteForField('shop_products', 42, 'img');

        $this->assertFalse(Storage::exists('thumb/shop_products/42/img'));
        $this->assertFalse(Storage::exists('large/shop_products/42/img'));
        $this->assertTrue(Storage::exists('thumb/shop_products/42/gallery/other.jpg'));
    }

    public function test_delete_for_path_removes_all_preset_previews(): void
    {
        $originalPath = 'shop_products/1/img/product.jpg';

        Storage::put('thumb/'.$originalPath, 'thumb-preview');
        Storage::put('large/'.$originalPath, 'large-preview');

        $this->service->deleteForPath($originalPath);

        $this->assertFalse(Storage::exists('thumb/'.$originalPath));
        $this->assertFalse(Storage::exists('large/'.$originalPath));
    }

    public function test_delete_for_record_removes_preset_directories(): void
    {
        Storage::makeDirectory('thumb/shop_products/42/img');
        Storage::makeDirectory('large/shop_products/42/img');
        Storage::put('thumb/shop_products/42/img/a.jpg', 'thumb-a');
        Storage::put('large/shop_products/42/img/a.jpg', 'large-a');

        $this->service->deleteForRecord('shop_products', 42);

        $this->assertFalse(Storage::exists('thumb/shop_products/42'));
        $this->assertFalse(Storage::exists('large/shop_products/42'));
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
