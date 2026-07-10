<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Models\User;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Facades\LetterTemplateFacade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_register_with_valid_fields_redirects_to_profile_and_authenticates_user(): void
    {
        $response = $this->post('/auth/register', [
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $response->assertRedirect(save_route('profile.edit'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'name' => 'Test User',
        ]);
    }

    public function test_login_with_valid_credentials_redirects_and_authenticates(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->post('/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(save_route('profile.edit'));
        $this->assertAuthenticated();
    }

    public function test_login_with_wrong_password_redirects_back_with_validation_errors(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $authUrl = save_route('auth.index');

        $response = $this->from($authUrl)
            ->post('/auth/login', [
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect($authUrl);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_redirects_and_clears_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/auth/logout');

        $response->assertRedirect(save_route('index.index'));
        $this->assertGuest();
    }

    public function test_password_recovery_with_valid_email_sends_letter_and_redirects_back_with_success(): void
    {
        $this->seedLetterTemplates();

        $user = User::factory()->create(['email' => 'user@example.com']);

        LetterTemplateFacade::shouldReceive('send')
            ->once()
            ->withArgs(function ($data) use ($user) {
                return $data->key === LetterEnum::PASSWORD_RESET
                    && $data->to === $user->email
                    && isset($data->replace['reset_url']);
            });

        $authUrl = save_route('auth.index');

        $response = $this->from($authUrl)
            ->post('/auth/password-recovery', [
                'email' => $user->email,
            ]);

        $response->assertRedirect($authUrl);
        $response->assertSessionHas('success');
    }

    public function test_password_recovery_with_unknown_email_redirects_back_with_validation_errors(): void
    {
        $this->seedLetterTemplates();

        $authUrl = save_route('auth.index');

        $response = $this->from($authUrl)
            ->post('/auth/password-recovery', [
                'email' => 'unknown@example.com',
            ]);

        $response->assertRedirect($authUrl);
        $response->assertSessionHasErrors('email');
    }

    public function test_password_reset_with_valid_token_updates_password_and_redirects_to_auth(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/auth/password-reset', [
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => $token,
        ]);

        $response->assertRedirect(save_route('auth.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_reset_with_invalid_token_redirects_back_with_validation_errors(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $authUrl = save_route('auth.index');

        $response = $this->from($authUrl)
            ->post('/auth/password-reset', [
                'email' => $user->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
                'token' => 'invalid-token',
            ]);

        $response->assertRedirect($authUrl);
        $response->assertSessionHasErrors('email');
    }
}
