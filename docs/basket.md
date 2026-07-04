# Basket

The `app/Modules/Basket` module implements the shopping cart for the `Shop` module.
The basket is available to guests (no auth) and logged-in users, and supports being
shared via a direct link.

## Basket behavior

### Where the basket is stored

A basket is a row in the `baskets` table, tied either to `user_id` (logged-in user)
or only to a `uuid` (guest). Which basket the current browser sees is determined by
the **Laravel session**: the uuid of the active basket is stored in the session under
the `basket_uuid` key (`Session::put('basket_uuid', $basket->uuid)`).

- **Guest**: the basket is not tied to a user (`user_id = null`), its `uuid` only
  lives in the session. Clearing cookies/session "loses" the basket (the data isn't
  deleted from the DB, the session just no longer knows its uuid).
- **Logged-in user**: the basket is tied to `user_id` (`User::hasOne(Basket)`), so it
  is available from any device/session after login — `FindBasketAction` for a user
  simply takes `$user->basket`, no session lookup needed.

### What happens on login / registration

During login or registration (`AuthController::login/register`, `OAuthController`),
`MergeBasketOnAuthAction` is called:

1. The basket `uuid` is read from the session (the guest basket, if any).
2. If the user doesn't have their own basket yet — the guest basket is simply
   "adopted" (its `user_id` is set to the user).
3. If the user already has their own basket — items from the guest basket are merged
   into the user's basket (`updateOrCreate` by `shop_product_id`, quantity/total are
   overwritten with the guest basket's values), then the guest basket is deleted.

### Behavior when sharing a link

Every basket page is reachable via a direct link containing the uuid: `/basket/{uuid}`
(`module_url('basket', [$basket->uuid])`). If the basket owner copies this link and
sends it to someone else:

- **Viewing** the basket page by someone else's uuid — opens without restriction,
  anyone with the link can see the basket contents (no read-side authorization check).
- **Modifying** the basket (add/remove item, change quantity) through the AJAX
  controller (`AjaxBasketController::change`) — checks whether it's the caller's
  "own" basket (`isOwnBasket()`: matching `user_id` with the current user, or matching
  `uuid` with the one stored in the guest's session). If the basket is **not the
  caller's own**, `ForkBasketAction` is triggered on the first modification attempt:
    - the caller's current (own) basket is deleted,
    - a **new** basket (fork) is created, and all items from the viewed (foreign)
      basket are copied into it,
    - the new uuid is stored in the session,
    - the frontend response contains a `location` redirect to `/basket/{new_uuid}`.

  In other words, a foreign basket is never mutated live by another person's action —
  the first attempt to change anything by whoever opened the foreign link silently
  forks an independent copy of the basket under the hood.

### Item lifecycle

- `AddOrUpdateBasketItemAction` — adds an item or updates its quantity/price/total
  (`updateOrCreate` by the `basket_id` + `shop_product_id` pair), snapshotting the
  product's name/article/price at the moment of adding (`product_name`,
  `product_article`, `selling_price`).
- `RemoveBasketItemAction` — removes an item from the basket.
- The basket total (`total_amount`) is **not** recalculated manually after every
  action: `BasketProductObserver` listens to `BasketProduct`'s `created`/`updated`
  events and calls `RecalculateBasketTotalAction`, which recomputes `total_amount` as
  the sum of `total` across all items (updated via `withoutEvents` to avoid
  re-triggering the observer).

### Cleanup of abandoned baskets

Guest baskets that never get attached to a user pile up over time. A scheduled
command, `basket:clear-abandoned` (`app/Modules/Basket/Console/Commands/ClearAbandonedBasketsCommand.php`),
deletes baskets where `user_id IS NULL` and `created_at` is older than a configurable
number of days (`30` by default, override with `basket:clear-abandoned {days}`).
Deletion goes through the Eloquent model (not a bulk query) so the `CascadesDeletes`
trait on `Basket` also removes its `BasketProduct` items. It's registered in
`bootstrap/app.php` via `withSchedule()` to run daily.

### Empty basket

If the basket has no items (`empty_basket`), a dedicated placeholder page is shown
instead of a form (`pages.basket.empty_basket`). A shared-link basket with no items
shows the same placeholder.

---

## Technical overview

### Models

- **`Basket`** (`baskets`)
  - `uuid` (public identifier used for links, via the `HasUuid` trait)
  - `user_id` (nullable, `belongsTo(User)`)
  - `total_amount` (decimal, cached total)
  - `items()` → `hasMany(BasketProduct::class, 'basket_id')`
  - `CascadesDeletes` — deleting a basket deletes all its `items`
  - `getTotalAmountFormattedAttribute()` — total with currency prefix
    (`t('shop.currency')`)

- **`BasketProduct`** (`basket_shop_products`)
  - `basket_id`, `shop_product_id` (`belongsTo(Product)`)
  - a product "snapshot" taken at the time it was added: `product_name`,
    `product_article`, `selling_price`
  - `quantity`, `total`
  - `getTotalFormattedAttribute()` — same, with currency

### Actions

| Action | Purpose |
|---|---|
| `FindBasketAction` | Finds an *existing* basket for the user/guest (user → `$user->basket`, guest → lookup by `basket_uuid` from the session among baskets with no `user_id`). Never creates anything. |
| `GetOrCreateBasketAction` | Same, but creates a new basket and stores its `uuid` in the session if none is found. For a logged-in user it additionally tries to pick up a "leftover" guest basket from the session before creating a new one. |
| `AddOrUpdateBasketItemAction` | `updateOrCreate`s an item in the basket (add / change quantity). |
| `RemoveBasketItemAction` | Removes an item, then recalculates the total. |
| `RecalculateBasketTotalAction` | `sum('total')` over items → `Basket::update(['total_amount' => ...])` without firing events. |
| `ForkBasketAction` | Copies items from a foreign/viewed basket (`$source`) into a newly created basket for the current user/guest, deleting their previous own basket; updates `basket_uuid` in the session. |
| `MergeBasketOnAuthAction` | On login/registration, merges the guest basket (from the session) into the user's basket (or "adopts" it if the user had no basket yet). |

### Controllers

- **`IndexController`** (`pages.basket.*`, resolved through the module routing in `ModuleRouteController`)
  - `index(Request $request, array $segments)`:
    - `segments[0] === 'checkout'` → checkout page (`pages.basket.checkout`, **currently a static mockup, not wired to real logic**)
    - `segments[0]` is a valid UUID → shows a specific basket by link
      (`pages.basket.index`, or `pages.basket.empty_basket` if it has no items)
    - otherwise → resolves the current user's/guest's basket; if it has items —
      redirects to `/basket/{uuid}`, otherwise shows the empty-basket placeholder
  - `checkout()` — a stub, no logic yet

- **`AjaxBasketController::change`** (`POST /basket/change`, route `change_product_count`)
  - Accepts `id` (product), `count`, `action` (the `BasketAction` enum), and
    optionally `basket_uuid` (uuid of the basket currently being viewed on the page —
    needed to tell "own" basket from a "foreign" one)
  - Resolves the working basket:
    - if `basket_uuid` is passed and it's the caller's own basket (`isOwnBasket`) —
      works with it
    - if `basket_uuid` is passed but the basket is **foreign** — forks it
      (`ForkBasketAction`) and returns a redirect command to the frontend instead of a
      regular response
    - if no `basket_uuid` is passed — gets/creates the caller's own basket
      (`GetOrCreateBasketAction`)
  - Performs one of 5 actions (`BasketAction`) and returns JSON with an `actions`
    array for the frontend's "ABC-response" runner (DOM patches: update text/html,
    remove an element, open a modal)

### `BasketAction` enum

```
DeleteModal            — remove an item from the basket modal
DeleteCheckout          — remove an item from the basket/checkout page
ChangeCount             — add an item from the product page
ChangeCountModal        — change quantity in the basket modal
ChangeCountCheckout     — change quantity on the basket/checkout page
```

Each case has its own set of DOM selectors to update in the response (the header item
count `.js_basket_count`, an item's total, the basket's grand total, etc.).

### Validation (`ChangeBasketRequest`)

```php
'id' => ['required', 'integer', 'exists:shop_products,id'],
'count' => ['required', 'integer', 'min:0'],
'action' => ['required', Rule::enum(BasketAction::class)],
'basket_uuid' => ['nullable', 'uuid', 'exists:baskets,uuid'],
```

### Observer

`BasketProductObserver` is registered in `AppServiceProvider`
(`BasketProduct::observe(BasketProductObserver::class)`); on `created`/`updated`
events it calls `RecalculateBasketTotalAction`, so the basket's `total_amount` is
always up to date without an explicit call after every item action.

### Routes

```php
// app/Modules/Basket/routes/web.php
Route::post('/basket/change', AjaxBasketController::class . '@change')->name('change_product_count');
```

Basket pages (`/basket`, `/basket/checkout`, `/basket/{uuid}`) don't have dedicated
routes — they're handled by the generic module router `ModuleRouteController`, which
resolves the module from the CMS page slug (`PageModule::Basket`) and calls
`Basket\IndexController::index()`.

### Migrations

- `baskets`: `id`, `uuid` (unique), `user_id` (nullable, FK to `users`), `total_amount`,
  timestamps
- `basket_shop_products`: `id`, `basket_id` (FK), `shop_product_id` (FK to
  `shop_products`), `product_name`, `product_article`, `selling_price`, `quantity`,
  `total`, timestamps

### Related modules

- **Auth** (`AuthController`, `OAuthController`) — calls `MergeBasketOnAuthAction` on
  login/registration/OAuth
- **Shop** (`Product`) — the product being added to the basket. `CreateOrderAction` in
  Shop currently creates an order directly from a passed DTO and is **not** wired to
  the basket — the `/basket/checkout` page is still a static mockup with no real
  order-placement logic
- **Core** (`ModuleRouteController`) — shares `basketCount` with all views via
  `view()->share()`, using `FindBasketAction`
