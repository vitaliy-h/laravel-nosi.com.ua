<?php

namespace Tests\Unit\Core;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IsActiveUrlTest extends TestCase
{
    public static function cases(): array
    {
        return [
            'same page'                  => ['/shop/kolgotky', '/shop/kolgotky', true],
            'same page, trailing slash'  => ['/shop/kolgotky', '/shop/kolgotky/', true],
            'absolute url of this host'  => ['/shop/kolgotky', 'http://localhost/shop/kolgotky', true],
            'product inside the section' => ['/shop/kolgotky/some-product', '/shop/kolgotky', true],
            'similar prefix'             => ['/shop/kolgotky-dytiachi', '/shop/kolgotky', false],
            'other section'              => ['/shop/bilyzna', '/shop/kolgotky', false],
            'home on home'               => ['/', '/', true],
            'home elsewhere'             => ['/shop', '/', false],
            'anchor link'                => ['/', '/#sale', false],
            'placeholder'                => ['/', '#', false],
            'empty'                      => ['/', null, false],
            'other host'                 => ['/shop', 'https://example.com/shop', false],
        ];
    }

    #[DataProvider('cases')]
    public function test_matches_the_current_page(string $current, ?string $url, bool $expected): void
    {
        $this->app->instance('request', Request::create('http://localhost' . $current));

        $this->assertSame($expected, is_active_url($url));
    }
}
