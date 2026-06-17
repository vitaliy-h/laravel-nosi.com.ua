<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagesClearCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        Config::set('image_sizes', [
            'thumb' => [
                'shop_products' => ['width' => 100, 'height' => 100, 'mode' => 'crop'],
                'news' => ['width' => 100, 'mode' => 'contain'],
            ],
            'large' => [
                'shop_products' => ['width' => 1200, 'height' => 1200, 'mode' => 'contain'],
            ],
        ]);

        Storage::makeDirectory('thumb/shop_products');
        Storage::makeDirectory('thumb/news');
        Storage::makeDirectory('large/shop_products');
    }

    public function test_clears_all_presets_when_called_without_arguments(): void
    {
        $this->artisan('images:clear')
            ->assertSuccessful();

        $this->assertFalse(Storage::exists('thumb'));
        $this->assertFalse(Storage::exists('large'));
    }

    public function test_clears_single_preset(): void
    {
        $this->artisan('images:clear thumb')
            ->assertSuccessful();

        $this->assertFalse(Storage::exists('thumb'));
        $this->assertTrue(Storage::exists('large'));
    }

    public function test_clears_specific_entity_within_preset(): void
    {
        $this->artisan('images:clear thumb shop_products')
            ->assertSuccessful();

        $this->assertFalse(Storage::exists('thumb/shop_products'));
        $this->assertTrue(Storage::exists('thumb/news'));
    }

    public function test_fails_with_error_for_unknown_preset(): void
    {
        $this->artisan('images:clear unknownpreset')
            ->assertExitCode(Command::FAILURE)
            ->expectsOutputToContain('not defined');
    }

    public function test_does_not_throw_when_directory_does_not_exist(): void
    {
        Storage::fake();

        Config::set('image_sizes', [
            'thumb' => [
                'shop_products' => ['width' => 100, 'height' => 100, 'mode' => 'crop'],
            ],
        ]);

        $this->artisan('images:clear thumb')
            ->assertSuccessful();
    }
}
