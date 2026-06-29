<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ApiEndpointsSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_categories_index_returns_success(): void
    {
        $response = $this->getJson('/api/shop/categories');

        $this->assertSmokeResponse($response, 200);
        $response->assertJsonPath('success', true);
    }

    public function test_translations_index_returns_success(): void
    {
        $response = $this->getJson('/api/translations');

        $this->assertSmokeResponse($response, 200);
    }

    private function assertSmokeResponse(TestResponse $response, int $expectedStatus): void
    {
        $response->assertStatus($expectedStatus);
        $this->assertNotEquals(500, $response->status());
    }
}
