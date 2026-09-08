<?php

namespace Tests\Feature\ShopOrder;

use App\Modules\Notification\Services\LetterTemplateService;
use App\Modules\ShopCart\Actions\IncrementPromocodeUsageAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Models\Promocode;
use App\Modules\ShopOrder\Actions\GenerateOrderNumberAction;
use App\Modules\ShopOrder\Actions\PlaceOrderFromCartAction;
use App\Modules\ShopOrder\Dtos\CustomerData;
use App\Modules\ShopOrder\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Placing an order writes to four tables and sends a letter. Everything below
 * fails halfway on purpose: the order, its lines, the promocode counter and the
 * cart must all come back to where they were, and the customer must not be told
 * about an order that does not exist.
 */
class PlaceOrderTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
        $this->seedLetterTemplates();
    }

    public function test_a_failure_after_the_order_is_created_leaves_no_order_behind(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id'   => $promocode->id,
            'promocode_code' => 'MINUS20',
        ]);

        $this->failIn(IncrementPromocodeUsageAction::class);

        $this->assertPlacingFails($cart);

        $this->assertDatabaseCount('shop_orders', 0);
        $this->assertDatabaseCount('shop_order_products', 0);
    }

    public function test_a_failed_order_does_not_burn_the_promocode(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id'   => $promocode->id,
            'promocode_code' => 'MINUS20',
        ]);

        $this->failIn(IncrementPromocodeUsageAction::class);

        $this->assertPlacingFails($cart);

        $this->assertSame(0, $promocode->refresh()->used_count);
    }

    public function test_a_failed_order_leaves_the_cart_untouched(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id'   => $promocode->id,
            'promocode_code' => 'MINUS20',
        ]);

        $this->failIn(IncrementPromocodeUsageAction::class);

        $this->assertPlacingFails($cart);

        $this->assertDatabaseHas('shop_carts', ['id' => $cart->id]);
        $this->assertSame(1, $cart->items()->count());
    }

    /**
     * The checkout recalculation runs before the order exists, so it is only
     * covered by the outer transaction — the one in PlaceOrderFromCartAction.
     * An expired code is dropped from the cart by that recalculation; after a
     * rollback the cart must still carry it.
     */
    public function test_the_checkout_recalculation_is_rolled_back_too(): void
    {
        $promocode = Promocode::factory()->fixed(20)->expired()->create(['code' => 'OLD']);
        $cart = $this->cartWithItemTotal(100);

        // Straight to the table: going through the model would fire the cart
        // observer, which recalculates and drops the expired code right away.
        Cart::query()->whereKey($cart->id)->update([
            'promocode_id'   => $promocode->id,
            'promocode_code' => 'OLD',
        ]);

        // Fails from the order observer, i.e. after the cart was recalculated.
        $this->failIn(GenerateOrderNumberAction::class);

        $this->assertPlacingFails($cart);

        $cart->refresh();
        $this->assertSame($promocode->id, $cart->promocode_id);
        $this->assertSame('OLD', $cart->promocode_code);
        $this->assertDatabaseCount('shop_orders', 0);
    }

    public function test_no_letter_is_sent_when_the_order_rolls_back(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id'   => $promocode->id,
            'promocode_code' => 'MINUS20',
        ]);

        $this->mock(LetterTemplateService::class)->shouldReceive('send')->never();

        $this->failIn(IncrementPromocodeUsageAction::class);

        $this->assertPlacingFails($cart);
    }

    public function test_the_letter_still_goes_out_when_the_order_succeeds(): void
    {
        $cart = $this->cartWithItemTotal(100);

        app(PlaceOrderFromCartAction::class)->handle($cart, $this->customer());

        $this->assertDatabaseCount('shop_orders', 1);
        $this->assertDatabaseHas('log_letters', ['receiver' => 'john@example.com']);
    }

    public function test_the_order_exists_by_the_time_the_letter_is_prepared(): void
    {
        $cart = $this->cartWithItemTotal(100);

        $this->mock(LetterTemplateService::class)
            ->shouldReceive('send')
            ->once()
            ->andReturnUsing(function (): void {
                $this->assertSame(1, Order::query()->count());
            });

        app(PlaceOrderFromCartAction::class)->handle($cart, $this->customer());
    }

    /**
     * Replaces an action the checkout depends on with one that throws, so the
     * failure lands in the middle of the write sequence rather than before it.
     *
     * @param  class-string  $action
     */
    private function failIn(string $action): void
    {
        $this->mock($action)
            ->shouldReceive('handle')
            ->andThrow(new RuntimeException($action . ' failed'));
    }

    private function assertPlacingFails(Cart $cart): void
    {
        try {
            app(PlaceOrderFromCartAction::class)->handle($cart, $this->customer());
            $this->fail('Placing the order was expected to fail.');
        } catch (RuntimeException) {
            // The rollback is what the assertions in each test look at.
        }
    }

    private function customer(): CustomerData
    {
        return new CustomerData(
            user_name: 'John',
            user_surname: 'Doe',
            user_email: 'john@example.com',
            user_phone: '+10000000000',
            comment: null,
        );
    }

    /**
     * @param  array<string, mixed>  $cartAttributes
     */
    private function cartWithItemTotal(float $total, array $cartAttributes = []): Cart
    {
        $cart = Cart::factory()->create($cartAttributes);

        CartProduct::factory()->create([
            'cart_id'       => $cart->id,
            'selling_price' => $total,
            'quantity'      => 1,
            'total'         => $total,
        ]);

        return $cart->refresh();
    }
}
