<?php

namespace Tests\Feature\Errors;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotFoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_admin_page_returns_404_for_guest(): void
    {
        $response = $this->get('/admin/nonexistent-resource-xyz');

        $response->assertStatus(404);
    }

    public function test_missing_admin_page_returns_404_for_authenticated_admin(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->actingAs($admin)->get('/admin/nonexistent-resource-xyz');

        $response->assertStatus(404);
    }
}
