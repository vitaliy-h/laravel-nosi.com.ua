# Cart

The `app/Modules/ShopCart` module implements the shopping cart for the `Shop` module.
The cart is available to guests (no auth) and logged-in users, and supports being
shared via a direct link.

## Cart behavior

### Where the cart is stored

A cart is a row in the `carts` table, tied either to `user_id` (logged-in user)
or only to a `uuid` (guest). Which cart the current browser sees is determined by
the **Laravel session**: the uuid of the active cart is stored in the session under
the `cart_uuid` key (`Session::put('cart_uuid', $cart->uuid)`).

- **Guest**: the cart is not tied to a user (`user_id = null`), its `uuid` only
  lives in the session. Clearing cookies/session "loses" the cart (the data isn't
  deleted from the DB, the session just no longer knows its uuid).
- **Logged-in user**: the cart is tied to `user_id` (`User::hasOne(Cart)`), so it
  is available from any device/session after login — `FindCartAction` for a user
  simply takes `$user->cart`, no session lookup needed.

### What happens on login / registration

During login or registration (`AuthController::login/register`, `OAuthController`),
`MergeCartOnAuthAction` is called:

1. The cart `uuid` is read from the session (the guest cart, if any).
2. If the user doesn't have their own cart yet — the guest cart is simply
   "adopted" (its `user_id` is set to the user).
3. If the user already has their own cart — items from the guest cart are merged
   into the user's cart (`updateOrCreate` by `shop_product_id`, quantity/total are
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
  `uuid` with the one stored in the guest's session). If the cart is **not the
  caller's own**, `ForkCartAction` is triggered on the first modification attempt:
    - the caller's current (own) cart is deleted,
    - a **new** cart (fork) is created, and all items from the viewed (foreign)
      cart are copied into it,
    - the new uuid is stored in the session,
    - the frontend response contains a `location` redirect to `/cart/{new_uuid}`.

  In other words, a foreign cart is never mutated live by another person's action —
  the first attempt to change anything by whoever opened the foreign link silently
  forks an independent copy of the cart under the hood.

### Item lifecycle

- `AddOrUpdateCartItemAction` — adds an item or updates its quantity/price/total
  (`updateOrCreate` by the `cart_id` + `shop_product_id` pair), snapshotting the
  product's name/article/price at the moment of adding (`product_name`,
  `product_article`, `selling_price`).
- `RemoveCartItemAction` — removes an item from the cart.
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
trait on `Cart` also removes its `CartProduct` items. It's registered in
`bootstrap/app.php` via `withSchedule()` to run daily.

### Empty cart

If the cart has no items (`empty_cart`), a dedicated placeholder page is shown
instead of a form (`pages.cart.empty_cart`). A shared-link cart with no items
shows the same placeholder.

---

## Technical overview

### Models

- **`Cart`** (`carts`)
  - `uuid` (public identifier used for links, via the `HasUuid` trait)
  - `user_id` (nullable, `belongsTo(User)`)
  - `total_amount` (decimal, cached total)
  - `items()` → `hasMany(CartProduct::class, 'cart_id')`
  - `CascadesDeletes` — deleting a cart deletes all its `items`
  - `getTotalAmountFormattedAttribute()` — total with currency prefix
    (`t('shop.currency')`)

- **`CartProduct`** (`shop_cart_products`)
  - `cart_id`, `shop_product_id` (`belongsTo(Product)`)
  - a product "snapshot" taken at the time it was added: `product_name`,
    `product_article`, `selling_price`
  - `quantity`, `total`
  - `getTotalFormattedAttribute()` — same, with currency

### Actions

| Action | Purpose |
|---|---|
| `FindCartAction` | Finds an *existing* cart for the user/guest (user → `$user->cart`, guest → lookup by `cart_uuid` from the session among carts with no `user_id`). Never creates anything. |
| `GetOrCreateCartAction` | Same, but creates a new cart and stores its `uuid` in the session if none is found. For a logged-in user it additionally tries to pick up a "leftover" guest cart from the session before creating a new one. |
| `AddOrUpdateCartItemAction` | `updateOrCreate`s an item in the cart (add / change quantity). |
| `RemoveCartItemAction` | Removes an item, then recalculates the total. |
| `RecalculateCartTotalAction` | `sum('total')` over items → `Cart::update(['total_amount' => ...])` without firing events. |
| `ForkCartAction` | Copies items from a foreign/viewed cart (`$source`) into a newly created cart for the current user/guest, deleting their previous own cart; updates `cart_uuid` in the session. |
| `MergeCartOnAuthAction` | On login/registration, merges the guest cart (from the session) into the user's cart (or "adopts" it if the user had no cart yet). |

### Controllers

- **`IndexController`** (`pages.cart.*`, explicit named routes — see Routes below)
  - `index(Request $request)` — resolves the current user's/guest's cart; if it
    has items, redirects to `/cart/{uuid}`, otherwise shows the empty-cart
    placeholder
  - `checkout(Request $request)` → checkout page (`pages.cart.checkout`,
    **currently a static mockup, not wired to real order-placement logic**)
  - `show(Request $request, string $uuid)` — shows a specific cart by link
    (`pages.cart.index`, or `pages.cart.empty_cart` if it has no items or
    `$uuid` isn't a valid UUID — falls back to `index()`'s own-cart behavior)

- **`AjaxCartController::change`** (`POST /cart/change`, route `change_product_count`)
  - Accepts `id` (product), `count`, `action` (the `CartAction` enum), and
    optionally `cart_uuid` (uuid of the cart currently being viewed on the page —
    needed to tell "own" cart from a "foreign" one)
  - Resolves the working cart:
    - if `cart_uuid` is passed and it's the caller's own cart (`isOwnCart`) —
      works with it
    - if `cart_uuid` is passed but the cart is **foreign** — forks it
      (`ForkCartAction`) and returns a redirect command to the frontend instead of a
      regular response
    - if no `cart_uuid` is passed — gets/creates the caller's own cart
      (`GetOrCreateCartAction`)
  - Performs one of 5 actions (`CartAction`) and returns JSON with an `actions`
    array for the frontend's "ABC-response" runner (DOM patches: update text/html,
    remove an element, open a modal)

### `CartAction` enum

```
DeleteModal            — remove an item from the cart modal
DeleteCheckout          — remove an item from the cart/checkout page
ChangeCount             — add an item from the product page
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
'cart_uuid' => ['nullable', 'uuid', 'exists:carts,uuid'],
```

### Observer

`CartProductObserver` is registered in `AppServiceProvider`
(`CartProduct::observe(CartProductObserver::class)`); on `created`/`updated`
events it calls `RecalculateCartTotalAction`, so the cart's `total_amount` is
always up to date without an explicit call after every item action.

### Routes

```php
// app/Modules/ShopCart/Http/Routes/web.php
Route::get('/', [IndexController::class, 'index'])->name('cart.index');
Route::get('/checkout', [IndexController::class, 'checkout'])->name('cart.checkout');
Route::get('/{uuid}', [IndexController::class, 'show'])->name('cart.show');

Route::post('/change', [AjaxCartController::class, 'change'])->name('change_product_count');
```

Mounted under the `/cart` prefix (the module's CMS page slug) by
`ModuleServiceProvider`.

### Migrations

- `carts`: `id`, `uuid` (unique), `user_id` (nullable, FK to `users`), `total_amount`,
  timestamps
- `shop_cart_products`: `id`, `cart_id` (FK), `shop_product_id` (FK to
  `shop_products`), `product_name`, `product_article`, `selling_price`, `quantity`,
  `total`, timestamps

### Related modules

- **Auth** (`AuthController`, `OAuthController`) — calls `MergeCartOnAuthAction` on
  login/registration/OAuth
- **Shop** (`Product`) — the product being added to the cart. `CreateOrderAction` in
  Shop currently creates an order directly from a passed DTO and is **not** wired to
  the cart — the `/cart/checkout` page is still a static mockup with no real
  order-placement logic
- **Core** (`ShareModulePageContext` middleware) — shares `cartCount` with all
  views via `PageContextSharer::share()`, using `FindCartAction`
