<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLetterTemplates();
    }

    public function test_login_with_valid_credentials_succeeds(): void
    {
        User::factory()->create(['email' => 'a@b.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'a@b.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
    }

    public function test_login_with_wrong_password_fails_validation(): void
    {
        User::factory()->create(['email' => 'a@b.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'a@b.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('email');
    }

    public function test_login_validates_empty_request(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_register_succeeds(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'X',
            'email' => 'new@b.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);

        $this->assertDatabaseHas('users', ['email' => 'new@b.com']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dup@b.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'X',
            'email' => 'dup@b.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('email');
    }

    public function test_register_rejects_unconfirmed_short_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'X',
            'email' => 'new@b.com',
            'password' => 'short',
            'terms' => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('password');
    }

    public function test_register_rejects_unaccepted_terms(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'X',
            'email' => 'new@b.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => false,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('terms');
    }

    public function test_logout_without_token_is_unauthorized(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }

    public function test_logout_with_token_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}
