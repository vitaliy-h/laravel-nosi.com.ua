<?php

namespace Tests\Feature\Profile;

use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileWebControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_index_redirects_to_edit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertRedirect(save_route('profile.edit'));
    }

    public function test_edit_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertViewIs('pages.profile.edit');
    }

    public function test_orders_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile/orders');

        $response->assertStatus(200);
        $response->assertViewIs('pages.profile.orders');
    }

    public function test_profile_routes_require_authentication(): void
    {
        $this->get('/profile/edit')->assertRedirect(save_route('auth.index'));
        $this->get('/profile/orders')->assertRedirect(save_route('auth.index'));
    }

    public function test_update_user_data_with_valid_data_redirects_back_with_success(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/profile/edit')
            ->post('/profile/update-user-data', [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertRedirect('/profile/edit');
        $response->assertSessionHas('user_data_success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_update_user_data_rejects_email_already_taken_by_another_user(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)
            ->from('/profile/edit')
            ->post('/profile/update-user-data', [
                'name' => 'Updated Name',
                'email' => 'taken@example.com',
            ]);

        $response->assertRedirect('/profile/edit');
        $response->assertSessionHasErrors('email');
    }
}
