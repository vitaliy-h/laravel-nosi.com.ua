<?php

namespace Tests\Unit\Auth;

use App\Modules\Auth\Actions\GenerateImpersonationTokenAction;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateImpersonationTokenActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_url_containing_user_id(): void
    {
        $user = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($user);

        $this->assertStringContainsString((string) $user->id, $url);
    }

    public function test_generated_url_contains_signature(): void
    {
        $user = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($user);

        $this->assertStringContainsString('signature=', $url);
    }

    public function test_generated_url_contains_expiry(): void
    {
        $user = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($user);

        $this->assertStringContainsString('expires=', $url);
    }

    public function test_generated_url_points_to_impersonate_route(): void
    {
        $user = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($user);

        $this->assertStringContainsString('/auth/impersonate/', $url);
    }
}
