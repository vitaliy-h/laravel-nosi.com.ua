<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Support\ImageResizer;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageResizerTest extends TestCase
{
    private ImageResizer $resizer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        $this->resizer = app(ImageResizer::class);
    }

    public function test_crop_mode_resizes_to_exact_dimensions(): void
    {
        Storage::put('test/img/original.jpg', $this->createJpeg(600, 400));

        $this->resizer->resize(
            'test/img/original.jpg',
            'thumb/test/img/original.jpg',
            ['width' => 300, 'height' => 300, 'mode' => 'crop'],
        );

        $this->assertTrue(Storage::exists('thumb/test/img/original.jpg'));
        $this->assertImageDimensions('thumb/test/img/original.jpg', 300, 300);
    }

    public function test_contain_mode_scales_proportionally_by_width_only(): void
    {
        Storage::put('test/img/original.jpg', $this->createJpeg(600, 400));

        $this->resizer->resize(
            'test/img/original.jpg',
            'thumb/test/img/original.jpg',
            ['width' => 300, 'mode' => 'contain'],
        );

        $this->assertImageDimensions('thumb/test/img/original.jpg', 300, 200);
    }

    public function test_contain_mode_with_width_and_height_does_not_upscale_beyond_original(): void
    {
        Storage::put('test/img/small.jpg', $this->createJpeg(100, 100));

        $this->resizer->resize(
            'test/img/small.jpg',
            'thumb/test/img/small.jpg',
            ['width' => 300, 'height' => 300, 'mode' => 'contain'],
        );

        $this->assertImageDimensions('thumb/test/img/small.jpg', 100, 100);
    }

    public function test_creates_parent_directories_for_preview(): void
    {
        Storage::put('test/img/file.jpg', $this->createJpeg(600, 400));

        $destPath = 'thumb/deep/nested/path/img/file.jpg';

        $this->resizer->resize(
            'test/img/file.jpg',
            $destPath,
            ['width' => 300, 'height' => 300, 'mode' => 'crop'],
        );

        $this->assertTrue(Storage::exists($destPath));
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

    private function assertImageDimensions(string $path, int $expectedWidth, int $expectedHeight): void
    {
        $size = getimagesizefromstring(Storage::get($path));

        $this->assertSame($expectedWidth, $size[0]);
        $this->assertSame($expectedHeight, $size[1]);
    }
}
