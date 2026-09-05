<?php

namespace Tests\Feature\Errors;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendNotFoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_frontend_page_returns_custom_404_template(): void
    {
        $this->seedPagesForRouting();

        $response = $this->get('/definitely-missing-page-xyz');

        $response->assertStatus(404);
        $response->assertSee('404', false);
        $response->assertSee('href="' . url('/') . '"', false);
    }
}
