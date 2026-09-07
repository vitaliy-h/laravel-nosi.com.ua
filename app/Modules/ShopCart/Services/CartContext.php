<?php

namespace App\Modules\ShopCart\Services;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\ShopCart\Actions\GetOrCreateCartAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Queries\CountCartItemsQuery;
use App\Modules\ShopCart\Queries\FindCartQuery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * The module's single owner of request-scoped cart state.
 *
 * Which cart this browser works with lives in the session under `cart_uuid`,
 * with the header badge count cached next to it under `cart_count`. This class is
 * the only place that reads either key, or `Auth::user()` on the cart's behalf —
 * the Actions and Queries underneath take the user and the uuid as parameters, so
 * they can also be called from a command, a job or a test without a session.
 * See `.claude/backend.md`, «Request-scoped state».
 */
class CartContext
{
    private const UUID_KEY = 'cart_uuid';

    private const COUNT_KEY = 'cart_count';

    public function __construct(
        private FindCartQuery $findCart,
        private GetOrCreateCartAction $getOrCreateCart,
        private CountCartItemsQuery $countItems,
    ) {}

    /**
     * Uuid of the guest cart this session was last working with. Still relevant
     * for a logged-in user: a cart started before signing in is picked up from here.
     */
    public function uuid(): ?string
    {
        return Session::get(self::UUID_KEY);
    }

    public function current(): ?Cart
    {
        return $this->findCart->handle(Auth::user(), $this->uuid());
    }

    public function currentOrCreate(): Cart
    {
        return $this->remember(
            $this->getOrCreateCart->handle(Auth::user(), $this->uuid())
        );
    }

    /**
     * Make $cart the one this session works with.
     */
    public function remember(Cart $cart): Cart
    {
        Session::put(self::UUID_KEY, $cart->uuid);

        return $cart;
    }

    /**
     * The cart is gone — an order was placed off it.
     */
    public function clear(): void
    {
        Session::forget(self::UUID_KEY);

        $this->forgetCount();
    }

    /**
     * Drop the cached badge count without recounting: the next render works out
     * the new number. Used where the cart changed underneath the session — a
     * guest cart merged into the user's on sign-in.
     */
    public function forgetCount(): void
    {
        Session::forget(self::COUNT_KEY);
    }

    public function count(): int
    {
        if (!ModuleGate::isEnabled('shop_cart')) {
            return 0;
        }

        if (!Session::has(self::COUNT_KEY)) {
            $this->refreshCount($this->current());
        }

        return Session::get(self::COUNT_KEY);
    }

    /**
     * Recount and cache, returning the number the caller also renders.
     */
    public function refreshCount(?Cart $cart): int
    {
        $count = $this->countItems->handle($cart);

        Session::put(self::COUNT_KEY, $count);

        return $count;
    }
}
