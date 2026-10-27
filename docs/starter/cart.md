# Cart

The `app/Modules/ShopCart` module implements the shopping cart for the `Shop` module.
The cart is available to guests (no auth) and logged-in users, and supports being
shared via a direct link.

## Cart behavior

### Where the cart is stored

A cart is a row in the `shop_carts` table, tied either to `user_id` (logged-in user)
or only to a `uuid` (guest). Which cart the current browser sees is determined by
the **Laravel session**: the uuid of the active cart is stored under the `cart_uuid`
key, with the header badge count cached next to it under `cart_count`.

- **Guest**: the cart is not tied to a user (`user_id = null`), its `uuid` only
  lives in the session. Clearing cookies/session "loses" the cart (the data isn't
  deleted from the DB, the session just no longer knows its uuid).
- **Logged-in user**: the cart is tied to `user_id` (`User::hasOne(Cart)`), so it
  is available from any device/session after login — `FindCartQuery` for a user
  simply takes `$user->cart`, no session lookup needed.

Exactly one class touches those keys — `ShopCart\Services\CartContext`. It is also
the only place that asks `Auth::user()` on the cart's behalf. The Actions and Queries
below it take the user and the guest uuid as arguments, so they can be called from a
command, a job or a test with no session at all; see `.claude/backend.md`,
«Request-scoped state». Its surface:

| Method | Does |
|---|---|
| `uuid()` | the guest cart uuid in the session, if any |
| `current()` | the cart this browser works with, or `null` — `FindCartQuery` with the user and the uuid filled in |
| `currentOrCreate()` | same, creating one if needed (`GetOrCreateCartAction`), and storing its uuid |
| `remember(Cart)` | point the session at a cart — used after a fork |
| `count()` / `refreshCount(?Cart)` / `forgetCount()` | the cached badge count |
| `clear()` | the cart is gone (an order was placed): drop both keys |

### What happens on login / registration

During login or registration (`AuthController::login/register`, `OAuthController`),
`MergeCartOnAuthAction` is called:

1. The controller reads the guest cart `uuid` (`CartContext::uuid()`) — before
   `session()->regenerate()`, which would drop it — and passes it to the action.
2. If the user doesn't have their own cart yet — the guest cart is simply
   "adopted" (its `user_id` is set to the user).
3. If the user already has their own cart — items from the guest cart are merged
   into the user's cart (`updateOrCreate` by `shop_product_id` + `pack_quantity`, quantity/total are
   overwritten with the guest cart's values), then the guest cart is deleted.

### Behavior when sharing a link

Every cart page is reachable via a direct link containing the uuid: `/cart/{uuid}`
(`save_route('cart.show', $cart->uuid)`). If the cart owner copies this link and
sends it to someone else:

- **Viewing** the cart page by someone else's uuid — opens without restriction,
  anyone with the link can see the cart contents (no read-side authorization check).
- **Modifying** the cart (add/remove item, change quantity) through the AJAX
  controller (`AjaxCartController::change`) — checks whether it's the caller's
  "own" cart (`isOwnCart()`: matching `user_id` with the current user, or matching
  `uuid` with `CartContext::uuid()`). If the cart is **not the
  caller's own**, `DuplicateCartAction` is triggered on the first modification attempt:
    - the caller's current (own) cart is deleted,
    - a **new** cart (fork) is created, and all items from the viewed (foreign)
      cart are copied into it,
    - the controller points the session at the fork (`CartContext::remember()`),
    - the frontend response contains a `location` redirect to `/cart/{new_uuid}`.

  In other words, a foreign cart is never mutated live by another person's action —
  the first attempt to change anything by whoever opened the foreign link silently
  forks an independent copy of the cart under the hood.

### Item lifecycle

- `AddOrUpdateCartItemAction` — adds an item or updates its quantity/price/total
  (`updateOrCreate` by `cart_id` + `shop_product_id` + `pack_quantity`), snapshotting the
  product's name/article/price at the moment of adding (`product_name`,
  `product_article`, `selling_price`). A line is one **set** of the product
  (`Product::$packs`, «1 шт.» / «5 шт.» / «10 шт.»): `pack_quantity` is the set size,
  `quantity` how many such sets, `selling_price` the price of the set. Two sets of one
  product are two lines; the name snapshot carries the set («Шкарпетки — 5 шт.»)
  unless the product is sold by the piece only. A set the product is not sold in is
  refused (`InvalidArgumentException`; the AJAX controller answers 422 on `pack`).
- `RemoveCartItemAction` — removes one set's line from the cart.

The forms send `pack` alongside `id`; the page tells lines apart by
`CartItemViewModel::$line_key` (`{product_id}-{pack}`) — `data-line` on the row and
`.js_product_checkout_total_{line_key}`.
- The cart total (`total_amount`) is **not** recalculated manually after every
  action: `CartProductObserver` listens to `CartProduct`'s `created`/`updated`
  events and calls `RecalculateCartTotalAction`, which recomputes `total_amount` as
  the sum of `total` across all items (updated via `withoutEvents` to avoid
  re-triggering the observer).

### Cleanup of abandoned carts

Guest carts that never get attached to a user pile up over time. A scheduled
command, `cart:clear-abandoned` (`app/Modules/ShopCart/Console/Commands/ClearAbandonedCartsCommand.php`),
deletes carts where `user_id IS NULL` and `created_at` is older than a configurable
number of days (`30` by default, override with `cart:clear-abandoned {days}`).
Deletion goes through the Eloquent model (not a bulk query) so the `CascadesDeletes`
trait on `Cart` also removes its `CartProduct` items. It's scheduled daily in
`routes/console.php`.

### Empty cart

If the cart has no items (`empty_cart`), a dedicated placeholder page is shown
instead of a form (`modules.shop_cart.empty_cart`). A shared-link cart with no items
shows the same placeholder.

---

## Technical overview

### Models

- **`Cart`** (`shop_carts`)
  - `uuid` (public identifier used for links, via the `HasUuid` trait)
  - `user_id` (nullable, `belongsTo(User)`)
  - `total_amount` (decimal, cached total)
  - `items()` → `hasMany(CartProduct::class, 'cart_id')`
  - `CascadesDeletes` — deleting a cart deletes all its `items`
  - amounts are plain `decimal:2` — the currency prefix is added by the view
    layer through `Core\Support\Money`, never by the model

- **`CartProduct`** (`shop_cart_products`)
  - `cart_id`, `shop_product_id` (`belongsTo(Product)`), `pack_quantity` (the set size, default 1)
  - a product "snapshot" taken at the time it was added: `product_name`,
    `product_article`, `selling_price`
  - `quantity` (number of sets), `total`

### Queries

| Query | Purpose |
|---|---|
| `FindCartQuery` | `handle(?User $user, ?string $guestCartUuid = null)` — finds an *existing* cart (user → `$user->cart`, guest → lookup by that uuid among carts with no `user_id`). The uuid is passed in, not read off the session. Never creates anything — creating is `GetOrCreateCartAction`'s job. |
| `CountCartItemsQuery` | `handle(?Cart $cart): int` — sums quantities, not rows: the number in the header badge. |

### Actions

| Action | Purpose |
|---|---|
| `GetOrCreateCartAction` | `handle(?User $user, ?string $guestCartUuid = null)` — same, but creates a cart when none is found. For a logged-in user it first tries to pick up a "leftover" guest cart by that uuid. Storing the uuid back is the caller's job (`CartContext::currentOrCreate()`). |
| `AddOrUpdateCartItemAction` | `updateOrCreate`s an item in the cart (add / change quantity). |
| `RemoveCartItemAction` | Removes an item, then recalculates the total. |
| `RecalculateCartTotalAction` | `sum('total')` over items → `Cart::update(['total_amount' => ...])` without firing events. It runs from an observer, so it fires for *any* cart the process touches (an admin editing a customer's cart, the cleanup command) — which is why it never touches the session-cached badge count. |
| `DuplicateCartAction` | `handle(Cart $source, ?User $user, ?string $guestCartUuid = null)` — copies items from a foreign/viewed cart into a newly created cart for the caller, deleting their previous own cart. Returns the fork; storing its uuid is the caller's job. |
| `MergeCartOnAuthAction` | `handle(User $user, ?string $guestCartUuid)` — on login/registration, merges the guest cart into the user's cart (or "adopts" it if the user had no cart yet). The controller reads the uuid *before* `session()->regenerate()` and passes it in. |
| `ValidatePromocodeAction` | `handle(string $code, float $subtotal): ActionResult` — checks the code exists, is visible, is inside its `starts_at`/`expires_at` window, has usage left and meets `min_order_amount`. Returns `ActionResult` because every failure is an expected business outcome with its own message. |
| `ApplyPromocodeToCartAction` | `handle(Cart $cart, string $code): ActionResult` — validates, computes the discount and writes `promocode_id`, `promocode_code`, `discount_amount` and the new `total_amount` onto the cart. |
| `RemovePromocodeFromCartAction` | `handle(Cart $cart): void` — clears those columns and restores the total to the subtotal. |
| `CalculateDiscountAction` | `handle(Promocode $promocode, float $subtotal): float` — fixed or percentage discount, capped by `max_discount`. |
| `IncrementPromocodeUsageAction` | `handle(Promocode $promocode): void` — bumps `used_count`; called when an order is actually placed, not when the code is applied. |

### Controllers

- **`IndexController`** (`modules.shop_cart.*`, explicit named routes — see Routes below)
  - `index(Request $request)` — resolves the current user's/guest's cart; if it
    has items, redirects to `/cart/{uuid}`, otherwise shows the empty-cart
    placeholder
  - `checkout(Request $request)` → renders `modules.shop_cart.checkout`. The template
    itself carries no cart data — the real checkout form lives on the cart page
    (`modules/shop_cart/index.blade.php`), which posts to `order.create`
  - `show(Request $request, string $uuid)` — shows a specific cart by link
    (`modules.shop_cart.index`, or `modules.shop_cart.empty_cart` if it has no items or
    `$uuid` isn't a valid UUID — falls back to `index()`'s own-cart behavior)

- **`AjaxCartController::change`** (`POST /cart/change`, route `change_product_count`)
  - Accepts `id` (product), `count`, `action` (the `CartAction` enum), and
    optionally `cart_uuid` (uuid of the cart currently being viewed on the page —
    needed to tell "own" cart from a "foreign" one)
  - Resolves the working cart:
    - if `cart_uuid` is passed and it's the caller's own cart (`isOwnCart`) —
      works with it
    - if `cart_uuid` is passed but the cart is **foreign** — forks it
      (`DuplicateCartAction`) and returns a redirect command to the frontend instead of a
      regular response
    - if no `cart_uuid` is passed — gets/creates the caller's own cart
      (`CartContext::currentOrCreate()`)
  - every response carrying the `.js_cart_count` badge goes through
    `CartContext::refreshCount()`, so the number the page is told to show and the one
    cached in the session cannot drift apart
  - Performs one of 5 actions (`CartAction`) and returns JSON with an `actions`
    array for the frontend's "ABC-response" runner (DOM patches: update text/html,
    remove an element, open a modal) — see `docs/starter/abc-response.md`

- **`PromocodeController`** (`POST /cart/promocode/apply`, `POST /cart/promocode/remove`)
  - resolves the cart by `cart_uuid`, then calls `ApplyPromocodeToCartAction` /
    `RemovePromocodeFromCartAction`
  - answers JSON in the same "ABC-response" action format as `AjaxCartController` when
    the request expects JSON (re-rendering the promocode form, the discount rows and the
    total from `CartViewModel`), and a `back()` redirect with a flash message otherwise

### `CartAction` enum

```
DeleteModal            — remove an item from the cart modal
DeleteCheckout          — remove an item from the cart/checkout page
ChangeCount             — add `count` more sets from the product page or a card
                          (2 pressed five times → 10)
ChangeCountModal        — change quantity in the cart modal
ChangeCountCheckout     — change quantity on the cart/checkout page
```

Each case has its own set of DOM selectors to update in the response (the header item
count `.js_cart_count`, an item's total, the cart's grand total, etc.).

### Validation (`ChangeCartRequest`)

```php
'id' => ['required', 'integer', 'exists:shop_products,id'],
'count' => ['required', 'integer', 'min:0'],
'action' => ['required', Rule::enum(CartAction::class)],
'cart_uuid' => ['nullable', 'uuid', 'exists:shop_carts,uuid'],
```

### Observer

`CartProductObserver` is auto-registered by `ModuleServiceProvider` from
`app/Modules/ShopCart/Observers/` (see modules.md §4); on `created`/`updated`
events it calls `RecalculateCartTotalAction`, so the cart's `total_amount` is
always up to date without an explicit call after every item action.
`CartObserver` is registered the same way and watches the cart row itself: when
`promocode_id` or `discount_amount` changes (an admin editing the cart in Filament), it
re-runs `RecalculateCartTotalAction`. That write goes through `withoutEvents()`, so it
does not re-trigger the observer.

### Routes

```php
// app/Modules/ShopCart/Http/Routes/web.php
Route::get('/', [IndexController::class, 'index'])->name('cart.index');
Route::get('/checkout', [IndexController::class, 'checkout'])->name('cart.checkout');
Route::get('/{uuid}', [IndexController::class, 'show'])->name('cart.show');

Route::post('/change', [AjaxCartController::class, 'change'])->name('change_product_count');
Route::post('/promocode/apply', [PromocodeController::class, 'apply'])->name('promocode.apply');
Route::post('/promocode/remove', [PromocodeController::class, 'remove'])->name('promocode.remove');
```

Mounted under the `/cart` prefix (the module's CMS page slug) by
`ModuleServiceProvider`. Order placement itself belongs to `ShopOrder`:
`POST /orders/create` (`order.create`), see below.

### Promocodes

A promocode lives on the cart, not on the order: applying one writes
`promocode_id`, `promocode_code` and `discount_amount` onto `shop_carts`, and
`total_amount` becomes `subtotal_amount - discount_amount`.

- **Model** `Promocode` (`shop_promocodes`), `PromocodeTypeEnum` — `FIXED` or `PERCENT`
- **Flow** `PromocodeController` → `ApplyPromocodeToCartAction` →
  `ValidatePromocodeAction` (+ `FindPromocodeByCodeQuery`) → `CalculateDiscountAction`
- **Usage counting** happens at order time: `CreateOrderAction` calls
  `IncrementPromocodeUsageAction`, so an applied-but-abandoned cart never burns a code
- **Admin** `app/Filament/Resources/Shop/Promocodes/PromocodeResource.php`,
  demo rows in `app/Modules/ShopCart/database/seeders/PromocodeSeeder.php`
- **Toggle** `shop_promocodes` in `config/modules.php` (a dependant of `shop_cart`, so
  it also goes off with `shop_cart` or `shop`) — it hides the admin surfaces, the
  `shop_promocodes` table stays

### Checkout and order placement

The cart page's checkout form posts to `POST /orders/create` (`order.create`,
`ShopOrder\Http\Controllers\Web\OrderController::create`), which resolves the cart by
`cart_uuid`, builds a `CustomerData` DTO from the validated request and calls
`ShopOrder\Actions\PlaceOrderFromCartAction::handle(Cart $cart, CustomerData $customer)`.
That action copies the cart items into the order (through `CreateOrderAction`, which also
increments promocode usage) and the controller redirects to `order.show`
(`/orders/{uuid}`). See `docs/starter/uuid.md` for the uuid-addressed order pages.

### Migrations

- `shop_carts`: `id`, `uuid` (unique), `user_id` (nullable, FK to `users`),
  `total_amount`, `subtotal_amount`, `promocode_id` (nullable, indexed),
  `promocode_code`, `discount_amount`, timestamps
- `shop_promocodes`: `id`, `code` (unique), `type` (`PromocodeTypeEnum`), `value`,
  `min_order_amount`, `max_discount`, `starts_at`, `expires_at`, `usage_limit`,
  `used_count`, `display`, `comment`, timestamps
- `shop_cart_products`: `id`, `cart_id` (FK), `shop_product_id` (FK to
  `shop_products`), `pack_quantity`, `product_name`, `product_article`, `selling_price`,
  `quantity`, `total`, timestamps (`shop_order_products` copies `pack_quantity` too)

### Related modules

- **Auth** (`AuthController`, `OAuthController`) — calls `MergeCartOnAuthAction` on
  login/registration/OAuth
- **Shop** (`Product`) — the product being added to the cart
- **ShopOrder** — turns a cart into an order: `PlaceOrderFromCartAction`,
  `CreateOrderAction`, route `POST /orders/create`
- **Page** (`ShareModulePageContext` middleware) — shares `cart_count` with all
  views via `PageContextSharer::share()`, which asks `CartContext::count()`
