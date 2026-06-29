<?php

namespace Tests\Feature\Profile;

use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_user_data_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/update-user-data', [
                'name' => 'New',
                'email' => 'new@b.com',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@b.com',
            'name' => 'New',
        ]);
    }

    public function test_update_user_data_rejects_email_of_another_user(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'other@b.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/update-user-data', [
                'name' => 'New',
                'email' => 'other@b.com',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('email');
    }

    public function test_update_user_data_allows_own_email(): void
    {
        $user = User::factory()->create(['email' => 'mine@b.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/update-user-data', [
                'name' => 'Mine',
                'email' => 'mine@b.com',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'mine@b.com',
            'name' => 'Mine',
        ]);
    }

    public function test_update_password_succeeds_with_correct_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/update-user-password', [
                'current_password' => 'password',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_update_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/update-user-password', [
                'current_password' => 'wrong-password',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('current_password');
    }

    public function test_update_password_rejects_unconfirmed_short_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/update-user-password', [
                'current_password' => 'password',
                'password' => 'short',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('password');
    }

    public function test_delete_account_rejects_mismatched_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/delete-user-account', [
                'email' => 'wrong@b.com',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('email');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_delete_account_succeeds_with_matching_delete_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/delete-user-account', [
                'delete_email' => $user->email,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_update_user_data_requires_authentication(): void
    {
        $response = $this->postJson('/api/profile/update-user-data', [
            'name' => 'New',
            'email' => 'new@b.com',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }

    public function test_update_user_password_requires_authentication(): void
    {
        $response = $this->postJson('/api/profile/update-user-password', [
            'current_password' => 'password',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }

    public function test_delete_user_account_requires_authentication(): void
    {
        $response = $this->postJson('/api/profile/delete-user-account', [
            'delete_email' => 'someone@b.com',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }
}
