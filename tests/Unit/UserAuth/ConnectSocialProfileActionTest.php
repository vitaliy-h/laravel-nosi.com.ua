<?php

namespace Tests\Unit\UserAuth;

use App\Modules\User\Models\User;
use App\Modules\UserAuth\Actions\ConnectSocialProfileAction;
use App\Modules\UserAuth\Models\SocialProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class ConnectSocialProfileActionTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSocialiteUser(string $id): SocialiteUser
    {
        $socialiteUser = new SocialiteUser;
        $socialiteUser->map(['id' => $id, 'name' => 'Test', 'email' => null]);
        $socialiteUser->token = 'tok';
        $socialiteUser->refreshToken = null;
        $socialiteUser->expiresIn = null;

        return $socialiteUser;
    }

    public function test_connects_new_social_profile_to_user(): void
    {
        $user = User::factory()->create();

        $profile = app(ConnectSocialProfileAction::class)
            ->handle($user, 'google', $this->fakeSocialiteUser('400'));

        $this->assertSame($user->id, $profile->user_id);
        $this->assertDatabaseHas('social_profiles', [
            'user_id'     => $user->id,
            'provider'    => 'google',
            'provider_id' => '400',
        ]);
    }

    public function test_reconnecting_own_social_profile_updates_it(): void
    {
        $user = User::factory()->create();
        SocialProfile::factory()->forUser($user)->forProvider('google')->create(['provider_id' => '401']);

        app(ConnectSocialProfileAction::class)
            ->handle($user, 'google', $this->fakeSocialiteUser('401'));

        $this->assertDatabaseCount('social_profiles', 1);
    }

    public function test_throws_when_social_profile_belongs_to_another_user(): void
    {
        $owner = User::factory()->create();
        SocialProfile::factory()->forUser($owner)->forProvider('google')->create(['provider_id' => '402']);

        $intruder = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(ConnectSocialProfileAction::class)
            ->handle($intruder, 'google', $this->fakeSocialiteUser('402'));
    }

    public function test_does_not_reassign_social_profile_when_owned_by_another_user(): void
    {
        $owner = User::factory()->create();
        SocialProfile::factory()->forUser($owner)->forProvider('google')->create(['provider_id' => '403']);

        $intruder = User::factory()->create();

        try {
            app(ConnectSocialProfileAction::class)
                ->handle($intruder, 'google', $this->fakeSocialiteUser('403'));
        } catch (ValidationException) {
            // expected
        }

        $this->assertDatabaseHas('social_profiles', [
            'user_id'     => $owner->id,
            'provider'    => 'google',
            'provider_id' => '403',
        ]);
    }
}
