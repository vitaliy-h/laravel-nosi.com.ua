<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FrontendPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    #[DataProvider('frontendPagesProvider')]
    public function test_frontend_page_returns_expected_status(string $url, int $expectedStatus): void
    {
        $response = $this->get($url);

        $this->assertSmokeResponse($response, $expectedStatus);
    }

    public static function frontendPagesProvider(): array
    {
        return [
            'home' => ['/', 200],
            'news' => ['/news', 200],
            'shop' => ['/shop', 200],
            'feedback' => ['/feedback', 200],
            'auth' => ['/auth', 200],
            'text page' => ['/text-page-1', 200],
            'profile guest redirect' => ['/profile', 302],
        ];
    }

    private function assertSmokeResponse(TestResponse $response, int $expectedStatus): void
    {
        $response->assertStatus($expectedStatus);
        $this->assertNotEquals(500, $response->status());
    }
}
