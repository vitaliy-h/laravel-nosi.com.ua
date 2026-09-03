<?php

namespace Tests\Feature\Profile;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;
use App\Modules\User\Models\User;
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
        $response->assertViewIs('modules.profile.edit');
    }

    public function test_orders_returns_200_for_authenticated_user(): void
    {
        $this->skipUnlessModules('shop_cart');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile/orders');

        $response->assertStatus(200);
        $response->assertViewIs('modules.profile.orders');
    }

    public function test_orders_page_lists_the_users_orders(): void
    {
        $this->skipUnlessModules('shop_cart');

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        OrderProduct::factory()->create([
            'shop_order_id' => $order->id,
            'product_name' => 'Ordered Thing',
        ]);

        $response = $this->actingAs($user)->get('/profile/orders');

        $response->assertStatus(200)
            ->assertSee('#'.$order->order_number)
            ->assertSee($order->payment_status->label())
            ->assertSee($order->fresh()->total_amount_formatted, false);
    }

    public function test_order_page_shows_the_order_details(): void
    {
        $this->skipUnlessModules('shop_cart');

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $item = OrderProduct::factory()->create([
            'shop_order_id' => $order->id,
            'shop_product_id' => $product->id,
            'product_name' => 'Ordered Thing',
            'product_article' => 'SKU-9',
        ]);

        $response = $this->actingAs($user)->get(save_route('profile.orders.show', $order->id));

        $response->assertStatus(200)
            ->assertViewIs('modules.profile.order')
            ->assertSee('Ordered Thing')
            ->assertSee('SKU-9')
            ->assertSee(save_route('shop.product', [$category->slug, $product->slug_with_id]), false)
            ->assertSee($item->total_formatted, false);
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
