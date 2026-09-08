<?php

namespace Tests\Feature\Core;

use App\Modules\Core\Support\ApiEnvelope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The envelope is a contract with every API client: four keys, always the same
 * four, whether the request succeeded or not.
 */
class ApiEnvelopeTest extends TestCase
{
    use RefreshDatabase;

    private const KEYS = ['success', 'message', 'data', 'errors'];

    public function test_a_success_carries_all_four_keys(): void
    {
        $body = ApiEnvelope::success(['id' => 1], 'Done')->getData(true);

        $this->assertSame(self::KEYS, array_keys($body));
        $this->assertTrue($body['success']);
        $this->assertSame(['id' => 1], $body['data']);
        $this->assertNull($body['errors']);
    }

    public function test_a_failure_carries_the_same_four_keys(): void
    {
        $body = ApiEnvelope::error('Nope', ['email' => ['taken']], 422)->getData(true);

        $this->assertSame(self::KEYS, array_keys($body));
        $this->assertFalse($body['success']);
        $this->assertNull($body['data']);
        $this->assertSame(['email' => ['taken']], $body['errors']);
    }

    public function test_a_validation_failure_from_the_renderer_uses_the_envelope(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422);
        $this->assertSame(self::KEYS, array_keys($response->json()));
        $response->assertJsonPath('data', null);
        $response->assertJsonPath('success', false);
    }

    public function test_an_unauthenticated_request_uses_the_envelope(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
        $this->assertSame(self::KEYS, array_keys($response->json()));
        $response->assertJsonPath('data', null);
        $response->assertJsonPath('errors', null);
    }

    public function test_a_missing_endpoint_uses_the_envelope(): void
    {
        $response = $this->getJson('/api/this/endpoint/does/not/exist');

        $response->assertStatus(404);
        $this->assertSame(self::KEYS, array_keys($response->json()));
    }

    public function test_a_successful_endpoint_uses_the_envelope(): void
    {
        $response = $this->getJson('/api/translations');

        $response->assertStatus(200);
        $this->assertSame(self::KEYS, array_keys($response->json()));
        $response->assertJsonPath('errors', null);
    }
}
