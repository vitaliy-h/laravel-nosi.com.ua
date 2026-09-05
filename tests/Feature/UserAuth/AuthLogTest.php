<?php

namespace Tests\Feature\UserAuth;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogResultEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\Core\Models\AuthLog;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
        $this->seedLetterTemplates();

        config(['auth_log.cooldown_seconds' => 5]);
    }

    public function test_successful_web_login_is_logged_as_client_source(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->post('/user_auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $log = AuthLog::query()->firstOrFail();

        $this->assertSame($user->id, $log->user_id);
        $this->assertSame(AuthLogActionEnum::PASSWORD, $log->action);
        $this->assertSame(AuthLogResultEnum::SUCCESS, $log->result);
        $this->assertSame(AuthLogSourceEnum::WEBSITE, $log->source);
    }

    public function test_failed_web_login_is_logged_as_failure(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->post('/user_auth/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $log = AuthLog::query()->firstOrFail();

        $this->assertSame(AuthLogResultEnum::FAILURE, $log->result);
        $this->assertSame('user@example.com', $log->login);
    }

    public function test_login_of_an_unknown_user_is_logged_with_the_submitted_login(): void
    {
        $this->post('/user_auth/login', [
            'email'    => 'ghost@example.com',
            'password' => 'whatever',
        ]);

        $log = AuthLog::query()->firstOrFail();

        $this->assertNull($log->user_id);
        $this->assertSame('ghost@example.com', $log->login);
        $this->assertSame(AuthLogResultEnum::FAILURE, $log->result);
    }

    public function test_api_login_is_logged_as_api_source(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $this->assertSame(
            AuthLogSourceEnum::API,
            AuthLog::query()->firstOrFail()->source,
        );
    }

    public function test_second_attempt_within_the_cooldown_is_blocked_and_logged(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->post('/user_auth/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        // correct password, but the cooldown has not expired yet
        $response = $this->post('/user_auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->assertSame(
            1,
            AuthLog::query()->where('result', AuthLogResultEnum::BLOCKED)->count(),
        );
    }

    public function test_cooldown_expires_and_login_succeeds_again(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->post('/user_auth/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $this->travel(6)->seconds();

        $this->post('/user_auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertSame(
            0,
            AuthLog::query()->where('result', AuthLogResultEnum::BLOCKED)->count(),
        );
    }

    public function test_successful_login_clears_the_cooldown(): void
    {
        User::factory()->create(['email' => 'user@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        // failure for another login must not block this one
        $this->post('/user_auth/login', [
            'email'    => 'other@example.com',
            'password' => 'wrong-password',
        ]);

        $this->post('/user_auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    public function test_password_reset_request_is_logged(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->post('/user_auth/password-recovery', [
            'email' => $user->email,
        ]);

        $log = AuthLog::query()->firstOrFail();

        $this->assertSame(AuthLogActionEnum::PASSWORD_RESET_REQUEST, $log->action);
        $this->assertSame(AuthLogResultEnum::SUCCESS, $log->result);
        $this->assertSame($user->id, $log->user_id);
    }

    public function test_password_reset_request_for_an_unknown_email_is_logged_as_failure(): void
    {
        $this->post('/user_auth/password-recovery', [
            'email' => 'ghost@example.com',
        ]);

        $log = AuthLog::query()->firstOrFail();

        $this->assertSame(AuthLogActionEnum::PASSWORD_RESET_REQUEST, $log->action);
        $this->assertSame(AuthLogResultEnum::FAILURE, $log->result);
        $this->assertNull($log->user_id);
    }

    public function test_password_change_through_reset_is_logged(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $token = Password::broker()->createToken($user);

        $this->post('/user_auth/password-reset', [
            'email'                 => $user->email,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token'                 => $token,
        ]);

        $log = AuthLog::query()
            ->where('action', AuthLogActionEnum::PASSWORD_CHANGE)
            ->firstOrFail();

        $this->assertSame(AuthLogResultEnum::SUCCESS, $log->result);
        $this->assertSame($user->id, $log->user_id);
    }

    public function test_password_change_in_the_profile_is_logged(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->actingAs($user)->post('/profile/update-user-password', [
            'current_password'      => 'password',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $log = AuthLog::query()
            ->where('action', AuthLogActionEnum::PASSWORD_CHANGE)
            ->firstOrFail();

        $this->assertSame(AuthLogResultEnum::SUCCESS, $log->result);
        $this->assertSame($user->id, $log->user_id);
    }
}
