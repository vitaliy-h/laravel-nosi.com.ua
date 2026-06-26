<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Models\SocialProfile;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class OAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPagesForRouting();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function fakeSocialiteUser(
        string $id = '111',
        string $name = 'OAuth User',
        ?string $email = 'oauth@example.com',
        ?string $token = 'tok',
    ): SocialiteUser {
        $user = new SocialiteUser;
        $user->map([
            'id'    => $id,
            'name'  => $name,
            'email' => $email,
        ]);
        $user->token        = $token;
        $user->refreshToken = null;
        $user->expiresIn    = null;

        return $user;
    }

    private function mockSocialiteCallback(string $provider, SocialiteUser $socialiteUser): void
    {
        $driver = \Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturn($driver);
    }

    // -----------------------------------------------------------------------
    // Redirect
    // -----------------------------------------------------------------------

    public function test_redirect_returns_redirect_for_valid_provider(): void
    {
        $driver = \Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $driver->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $response = $this->get(route('social.redirect', 'google'));

        $response->assertRedirect();
    }

    public function test_redirect_returns_404_for_invalid_provider(): void
    {
        $this->get(route('social.redirect', 'twitter'))->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // Callback — login / register as guest
    // -----------------------------------------------------------------------

    public function test_callback_creates_new_user_and_social_profile_when_no_match(): void
    {
        $fakeUser = $this->fakeSocialiteUser(id: '999', email: 'new-oauth@example.com');
        $this->mockSocialiteCallback('google', $fakeUser);

        $response = $this->get(route('social.callback', 'google'));

        $response->assertRedirect();
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new-oauth@example.com']);
        $this->assertDatabaseHas('social_profiles', [
            'provider'    => 'google',
            'provider_id' => '999',
        ]);
    }

    public function test_callback_creates_user_without_email_when_provider_returns_none(): void
    {
        $fakeUser = $this->fakeSocialiteUser(id: '777', email: null);
        $this->mockSocialiteCallback('telegram', $fakeUser);

        $response = $this->get(route('social.callback', 'telegram'));

        $response->assertRedirect();
        $this->assertAuthenticated();
        $this->assertDatabaseHas('social_profiles', [
            'provider'    => 'telegram',
            'provider_id' => '777',
        ]);
    }

    public function test_callback_logs_in_existing_user_by_social_profile(): void
    {
        $user = User::factory()->create();
        SocialProfile::factory()->forUser($user)->forProvider('google')->create(['provider_id' => '555']);

        $fakeUser = $this->fakeSocialiteUser(id: '555');
        $this->mockSocialiteCallback('google', $fakeUser);

        $response = $this->get(route('social.callback', 'google'));

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_callback_merges_with_existing_user_when_email_matches(): void
    {
        $existing = User::factory()->create(['email' => 'merge@example.com']);

        $fakeUser = $this->fakeSocialiteUser(id: '321', email: 'merge@example.com');
        $this->mockSocialiteCallback('google', $fakeUser);

        $this->get(route('social.callback', 'google'));

        $this->assertAuthenticatedAs($existing);
        $this->assertDatabaseHas('social_profiles', [
            'user_id'     => $existing->id,
            'provider'    => 'google',
            'provider_id' => '321',
        ]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_callback_updates_token_on_returning_social_user(): void
    {
        $user = User::factory()->create();
        SocialProfile::factory()->forUser($user)->forProvider('google')->create([
            'provider_id' => '100',
            'token'       => 'old-token',
        ]);

        $fakeUser = $this->fakeSocialiteUser(id: '100', token: 'new-token');
        $this->mockSocialiteCallback('google', $fakeUser);

        $this->get(route('social.callback', 'google'));

        $this->assertDatabaseHas('social_profiles', [
            'provider_id' => '100',
            'token'       => 'new-token',
        ]);
    }

    public function test_callback_returns_404_for_invalid_provider(): void
    {
        $this->get(route('social.callback', 'twitter'))->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // Callback — connect mode (authenticated user)
    // -----------------------------------------------------------------------

    public function test_callback_connects_social_profile_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fakeUser = $this->fakeSocialiteUser(id: '200', email: 'other@example.com');
        $this->mockSocialiteCallback('facebook', $fakeUser);

        $response = $this->get(route('social.callback', 'facebook'));

        $response->assertRedirect();
        $this->assertDatabaseHas('social_profiles', [
            'user_id'     => $user->id,
            'provider'    => 'facebook',
            'provider_id' => '200',
        ]);
    }

    public function test_callback_does_not_create_second_user_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fakeUser = $this->fakeSocialiteUser(id: '201', email: 'nobody@example.com');
        $this->mockSocialiteCallback('google', $fakeUser);

        $this->get(route('social.callback', 'google'));

        $this->assertDatabaseCount('users', 1);
    }

    // -----------------------------------------------------------------------
    // Disconnect
    // -----------------------------------------------------------------------

    public function test_disconnect_removes_social_profile(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        SocialProfile::factory()->forUser($user)->forProvider('google')->create();

        $this->actingAs($user)
            ->post(route('profile.social.disconnect', 'google'))
            ->assertRedirect();

        $this->assertDatabaseMissing('social_profiles', [
            'user_id'  => $user->id,
            'provider' => 'google',
        ]);
    }

    public function test_disconnect_blocked_when_no_password_no_email_and_last_provider(): void
    {
        $user = User::factory()->create(['email' => null, 'password' => null]);
        SocialProfile::factory()->forUser($user)->forProvider('google')->create();

        $response = $this->actingAs($user)
            ->post(route('profile.social.disconnect', 'google'));

        $response->assertSessionHasErrors('provider');
        $this->assertDatabaseHas('social_profiles', [
            'user_id'  => $user->id,
            'provider' => 'google',
        ]);
    }

    public function test_disconnect_allowed_when_user_has_password_even_with_no_email(): void
    {
        $user = User::factory()->create(['email' => null, 'password' => bcrypt('secret')]);
        SocialProfile::factory()->forUser($user)->forProvider('google')->create();

        $this->actingAs($user)
            ->post(route('profile.social.disconnect', 'google'))
            ->assertRedirect();

        $this->assertDatabaseMissing('social_profiles', [
            'user_id'  => $user->id,
            'provider' => 'google',
        ]);
    }

    public function test_disconnect_allowed_when_another_social_provider_exists(): void
    {
        $user = User::factory()->create(['email' => null, 'password' => null]);
        SocialProfile::factory()->forUser($user)->forProvider('google')->create();
        SocialProfile::factory()->forUser($user)->forProvider('facebook')->create();

        $this->actingAs($user)
            ->post(route('profile.social.disconnect', 'google'))
            ->assertRedirect();

        $this->assertDatabaseMissing('social_profiles', [
            'user_id'  => $user->id,
            'provider' => 'google',
        ]);
        $this->assertDatabaseHas('social_profiles', [
            'user_id'  => $user->id,
            'provider' => 'facebook',
        ]);
    }

    public function test_disconnect_requires_authentication(): void
    {
        $this->post(route('profile.social.disconnect', 'google'))
            ->assertRedirect();

        $this->assertGuest();
    }
}
