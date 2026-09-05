<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Enums\LogEventEnum;
use App\Modules\Core\Models\Log;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The cascade lives in the CascadesDeletes trait itself (bootCascadesDeletes),
 * not in the log listeners, so it must keep working on its own.
 */
class CascadesDeletesTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_cart_deletes_its_items(): void
    {
        $cart = Cart::factory()->create();
        $items = CartProduct::factory()->count(2)->create(['cart_id' => $cart->id]);

        $cart->delete();

        foreach ($items as $item) {
            $this->assertModelMissing($item);
        }
    }

    public function test_deleting_an_order_deletes_its_items(): void
    {
        $order = Order::factory()->create();
        $item = OrderProduct::factory()->create(['shop_order_id' => $order->id]);

        $order->delete();

        $this->assertModelMissing($item);
    }

    public function test_a_model_without_the_trait_keeps_its_relations(): void
    {
        $cart = Cart::factory()->create();
        $item = CartProduct::factory()->create(['cart_id' => $cart->id]);

        $item->delete();

        $this->assertModelExists($cart);
    }

    public function test_a_cascade_writes_its_journal_rows_in_one_insert(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->count(3)->create(['cart_id' => $cart->id]);

        $inserts = 0;
        DB::listen(function ($query) use (&$inserts): void {
            if (str_starts_with(strtolower($query->sql), 'insert into `logs`')) {
                $inserts++;
            }
        });

        $cart->delete();

        // Three children plus the cart itself, but the children are buffered by
        // LogService::batch() — one INSERT for them, one for the cart.
        $this->assertSame(2, $inserts);
        $this->assertSame(4, Log::query()->where('event', LogEventEnum::DELETED)->count());
    }

    public function test_cascaded_children_still_fire_their_own_events(): void
    {
        $cart = Cart::factory()->create();
        $item = CartProduct::factory()->create(['cart_id' => $cart->id]);

        Log::query()->delete();

        $cart->delete();

        $this->assertDatabaseHas('logs', [
            'model_type' => CartProduct::class,
            'model_id'   => $item->id,
            'event'      => LogEventEnum::DELETED->value,
        ]);
    }
}
