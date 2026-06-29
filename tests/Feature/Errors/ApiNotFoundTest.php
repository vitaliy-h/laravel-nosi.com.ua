<?php

namespace Tests\Feature\Errors;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiNotFoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_api_endpoint_returns_json_404(): void
    {
        $response = $this->getJson('/api/this/endpoint/does/not/exist');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }
}
