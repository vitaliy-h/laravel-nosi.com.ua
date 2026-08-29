<?php

namespace Tests\Unit\Profile\ViewModels;

use App\Modules\Profile\ViewModels\ProfileUserViewModel;
use App\Modules\User\Models\User;
use App\Modules\UserAuth\Models\SocialProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUserViewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_accounts_are_keyed_by_provider(): void
    {
        $user = User::factory()->create();
        SocialProfile::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'identifier' => 'ada@example.com',
        ]);

        $vm = ProfileUserViewModel::from($user->load('socialProfiles'));

        $this->assertSame('ada@example.com', $vm->social_profiles['google']->identifier);
        $this->assertArrayNotHasKey('facebook', $vm->social_profiles);
    }

    /**
     * A connected account with no identifier still has to read as connected, so
     * the entry stays an object rather than collapsing to an empty string.
     */
    public function test_a_connected_account_without_an_identifier_is_still_present(): void
    {
        $user = User::factory()->create();
        SocialProfile::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'identifier' => null,
        ]);

        $vm = ProfileUserViewModel::from($user->load('socialProfiles'));

        $this->assertNotNull($vm->social_profiles['google'] ?? null);
        $this->assertNull($vm->social_profiles['google']->identifier);
    }

    public function test_it_carries_no_credentials(): void
    {
        $user = User::factory()->create();
        SocialProfile::factory()->create(['user_id' => $user->id, 'token' => 'secret-token']);

        $payload = json_encode(ProfileUserViewModel::from($user->load('socialProfiles'))->toArray());

        $this->assertStringNotContainsString('secret-token', $payload);
        $this->assertStringNotContainsString($user->password, $payload);
        foreach (['password', 'remember_token', 'token'] as $forbidden) {
            $this->assertStringNotContainsString('"'.$forbidden.'"', $payload);
        }
    }
}
