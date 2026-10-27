<?php

namespace Tests\Feature\ShopOrder;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\Translation\Models\Language;
use App\Modules\Translation\Services\TranslationService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLetterTemplates();
    }

    public function test_place_order_creates_order_and_redirects_to_show(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create([
            'cart_id'       => $cart->id,
            'quantity'      => 2,
            'selling_price' => 10,
            'total'         => 20,
        ]);

        $response = $this->post(route('order.create'), [
            'cart_uuid'    => $cart->uuid,
            'user_name'    => 'John',
            'user_surname' => 'Doe',
            'user_email'   => 'john@example.com',
            'user_phone'   => '067 123-45-67',
            'comment'      => 'Please call before delivery',
        ]);

        $order = Order::firstOrFail();

        $response->assertRedirect(route('order.show', $order->uuid));

        $this->assertSame('John', $order->user_name);
        $this->assertSame('Doe', $order->user_surname);
        $this->assertSame('john@example.com', $order->user_email);
        $this->assertSame('+380671234567', $order->user_phone, 'the phone is stored normalized');
        $this->assertSame('Please call before delivery', $order->comment);
        $this->assertSame(1, $order->items()->count());
    }

    public function test_place_order_deletes_the_cart(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid'  => $cart->uuid,
            'user_email' => 'john@example.com',
            'user_phone' => '+380671234567',
        ]);

        $this->assertDatabaseMissing('shop_carts', ['id' => $cart->id]);
    }

    public function test_place_order_requires_email(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid' => $cart->uuid,
        ])->assertSessionHasErrors('user_email');
    }

    public function test_place_order_requires_valid_email(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid'  => $cart->uuid,
            'user_email' => 'not-an-email',
        ])->assertSessionHasErrors('user_email');
    }

    public function test_place_order_rejects_email_without_domain_zone(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid'  => $cart->uuid,
            'user_email' => 'john@example',
        ])->assertSessionHasErrors('user_email');

        $this->assertSame(0, Order::count());
    }

    public function test_place_order_requires_cart_uuid(): void
    {
        $this->post(route('order.create'), [
            'user_email' => 'john@example.com',
        ])->assertSessionHasErrors('cart_uuid');
    }

    public function test_place_order_with_unknown_cart_uuid_fails_validation(): void
    {
        $this->post(route('order.create'), [
            'cart_uuid'  => '00000000-0000-0000-0000-000000000000',
            'user_email' => 'john@example.com',
        ])->assertSessionHasErrors('cart_uuid');
    }

    public function test_place_order_requires_phone(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid'  => $cart->uuid,
            'user_email' => 'john@example.com',
        ])->assertSessionHasErrors('user_phone');

        $this->assertDatabaseCount('shop_orders', 0);
    }

    public function test_place_order_rejects_a_non_ukrainian_phone(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        foreach (['+48 512 345 678', '067 123 45', '+380 67 123 45 678', 'call me'] as $phone) {
            $this->post(route('order.create'), [
                'cart_uuid'  => $cart->uuid,
                'user_email' => 'john@example.com',
                'user_phone' => $phone,
            ])->assertSessionHasErrors('user_phone');
        }

        $this->assertDatabaseCount('shop_orders', 0);
    }

    public function test_unpaid_order_page_shows_payment_requisites(): void
    {
        $this->seed(LanguageSeeder::class);
        $order = Order::factory()->create(['payment_status' => PaymentStatusEnum::UNPAID]);

        $this->get(route('order.show', $order->uuid))
            ->assertOk()
            ->assertSee('class="requisites"', false)
            ->assertSee('data-copy="' . t('requisites.iban') . '"', false);
    }

    public function test_requisite_cleared_in_dictionary_is_not_shown(): void
    {
        $this->seed(LanguageSeeder::class);
        $iban = t('requisites.iban');

        // the admin form saves a cleared field as null
        Language::all()->each(function (Language $language) {
            $dictionary = $language->dictionary;
            $dictionary['requisites']['iban'] = null;
            $language->update(['dictionary' => $dictionary]);
            app(TranslationService::class)->clearCache($language->localization);
        });

        $order = Order::factory()->create(['payment_status' => PaymentStatusEnum::UNPAID]);

        $this->get(route('order.show', $order->uuid))
            ->assertOk()
            ->assertSee('class="requisites"', false)
            ->assertDontSee($iban);
    }

    public function test_paid_order_page_hides_payment_requisites(): void
    {
        $this->seed(LanguageSeeder::class);
        $order = Order::factory()->create(['payment_status' => PaymentStatusEnum::PAID]);

        $this->get(route('order.show', $order->uuid))
            ->assertOk()
            ->assertDontSee('class="requisites"', false);
    }
}
