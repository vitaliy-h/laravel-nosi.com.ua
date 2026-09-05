<?php

namespace Tests\Unit\UserAuth;

use App\Modules\User\Models\User;
use App\Modules\UserAuth\Actions\GenerateImpersonationTokenAction;
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

        $this->assertStringContainsString('/user_auth/impersonate/', $url);
    }
}
