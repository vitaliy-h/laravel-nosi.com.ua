<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_admin_login_page(): void
    {
        $response = $this->get('/admin/login');

        $this->assertSmokeResponse($response, 200);
    }

    public function test_guest_is_redirected_from_admin_to_login(): void
    {
        $response = $this->get('/admin');

        $this->assertSmokeResponse($response, 302);
    }

    private function assertSmokeResponse(TestResponse $response, int $expectedStatus): void
    {
        $response->assertStatus($expectedStatus);
        $this->assertNotEquals(500, $response->status());
    }
}
