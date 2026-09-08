# Backend Developer Guide

## Routing conventions

Module web controllers (`Http/Controllers/Web/`) extend `App\Modules\Core\Abstract\BaseWebController` and define one real Laravel action method per named route (typed route parameters, no manual segment parsing). See `docs/starter/routing.md`.

All API controllers (`Http/Controllers/Api/`) extend `App\Modules\Core\Abstract\BaseApiController`.

Controllers never call the `view()` helper directly — they return `$this->render('blade.path', $data)` from `BaseWebController`. (`view()->share()` for shared view data is unaffected.)

## Module seeders

Demo data for a module goes through one entry-point seeder named
`App\Modules\{Module}\database\seeders\{Module}Seeder`, which calls the
module's sub-seeders in order. `database/seeders/DatabaseSeeder.php` finds
and calls it automatically — don't add per-module `if` blocks there. See
`docs/starter/modules.md` §6.

## Module toggles

Modules can be disabled via `config/modules.php` (`list` key), gated by
`App\Modules\Core\Support\ModuleGate`. This affects routes, commands,
migrations, seeders, admin nav/resources, and tests for that module. See
`docs/starter/modules.md`.

## View layer

Data going from a controller to a view goes through a **ViewModel**: fed an
Eloquent model, it exposes fields already prepared for rendering. Controllers
never hand a blade an Eloquent model.

ViewModels live in `app/Modules/{Module}/ViewModels/` on top of
`App\Modules\Core\Abstract\BaseViewModel`. Two levels: `{Model}ViewModel` per
entity (reused across screens) and `{Page}ViewModel` per screen. Build them with
spatie's magical `from{Type}()`, collect with `::collect()` (paginators stay
paginators), hand a page to the view with `$page->toViewData()`.

A ViewModel never queries the DB — eager-loading is the caller's job — never
reaches into a switchable module in place of `ModuleGate`, and never carries a
field the page does not render. See `docs/starter/view-models.md`.

### Models do not format

A model holds state; turning it into something a customer reads is the view
layer's job. No `get*FormattedAttribute` on a model, and no `t()` inside one —
a model that translates makes the answer depend on the current locale, which is
request-scoped state by another name.

Money is the case this comes up most, so it has a value object:
`Core\Support\Money` rounds to the two decimals the schema stores, adds and
subtracts them, and is the only place that writes the currency symbol.

```php
// no — the model formats and translates
public function getTotalAmountFormattedAttribute(): string
{
    return t('shop.currency') . number_format($this->total_amount, 2);
}

// yes — the ViewModel asks for the string it is going to render
total_amount_formatted: Money::from($order->total_amount)->format(),
```

`Money` is deliberately **not** an Eloquent cast: the columns stay `decimal:2`,
so Filament, the dashboard queries and the API keep reading plain numbers. Build
a `Money` where an amount is used — in a ViewModel to print it, in an Action to
do arithmetic on it (`RecalculateCartTotalAction`).

Accessors that survive are written the Laravel 13 way, `Attribute::make()` in a
`protected function name(): Attribute`, not `getNameAttribute()`.

## Request-scoped state

`Session`, `Auth`, `request()` and `Cookie` are **ambient state**: a class that reads
them behaves differently depending on who happens to be calling. The rule is about
layers, not about class names — Services obey it exactly like Actions and Queries do.

- **Entry points** adapt a transport to the domain and may read ambient state:
  controllers, middleware, FormRequests, per-request view sharers
  (`Page\Services\PageContextSharer`), Filament pages and resources, console commands,
  and the place where a Job is constructed.
- **The domain** takes it as a parameter or a constructor dependency and never reads
  it: Actions, Queries, Services, Support, ViewModels, DTOs, observers.

The check is not "is this an Action" but: *can this class be called from a queued job
or an artisan command and still do the same thing?* If not, it is reading ambient
state and something above it should be passing the value in.

```php
// no — the Query decides for itself whose cart this is
public function handle(?User $user): ?Cart
{
    $uuid = Session::get('cart_uuid');
}

// yes — the caller says whose cart it wants
public function handle(?User $user, ?string $guestCartUuid = null): ?Cart
```

### Give the state one owner

Reading `Session::get('cart_uuid')` in the controller is correct but does not scale:
ten controllers repeat the key, and nothing says who may write it. So each kind of
ambient state gets **one named owner**, injected like any other dependency, and that
owner is the only class touching the underlying facade:

| Owner | Owns |
|---|---|
| `ShopCart\Services\CartContext` | `cart_uuid` / `cart_count` in the session, and `Auth::user()` on the cart's behalf: `current()`, `currentOrCreate()`, `remember()`, `count()`, `clear()` |
| `Core\Support\RequestContext` | IP, user agent and user id for the journals — `null` outside a request |
| `Core\Support\LogSourceResolver`, `AuthLogSourceResolver`, `Notification\Support\NotificationSourceResolver` | Which surface the call came from: console / admin / API / site |

An owner is a class, not a trait: a trait mixed into a service smuggles the ambient
read straight back into it, which is why `DetectsNotificationSource` became
`NotificationSourceResolver`.

### The exceptions, and there are only two kinds

1. **The ambient state is the effect.** `UserAuth\Actions\ImpersonateAction` and
   `LeaveImpersonationAction` call `Auth::login()` / `Auth::logout()` — that is what
   they are for, there is nothing to parametrise. Keep such actions countable.
2. **Null-safe enrichment of a journal.** `LogService`, `AuthLogService` and
   `AuthCooldownService` run inside a request as often as from a queue, and threading
   an IP address through every observer would say nothing about the domain. They read
   it through `RequestContext`, which degrades to `null` instead of failing.

Anything else that wants the session or the current user takes it as an argument.

## Container bindings

Bind and inject by **class name**. `AppServiceProvider` registers the shared services
(`TranslationService`, `LetterTemplateService`, `SmsTemplateService`, `LogService`,
`GetAllPagesQuery`) as singletons under their class, and everything else is resolved
automatically — no string aliases, so a dependency is visible where it is injected.

Facades (`Trans`, `LetterTemplateFacade`, `SmsTemplateFacade`) exist for blade and the
`t()` helper. In PHP code inject the service instead; that is also what makes it mockable
in tests without touching a facade.

A pure helper does not need any of this: `Core\Support\UrlHelper` is static.

## Services vs Support

Both live under `app/Modules/{Module}/`, and the split is by what the class touches:

- **`Services/`** — work with state: the database, the filesystem, an external API, the
  log. `LogService`, `AuthLogService`, `AuthCooldownService`, `ImageConversionService`.
- **`Support/`** — pure helpers and resolvers: same input, same output, nothing stored.
  `ModuleGate`, `UrlHelper`, `ImageResizer`, `UserAgentParser`, `LogSourceResolver`.
  The ambient-state resolvers (`LogSourceResolver`, `RequestContext`) live here too:
  they store nothing, they only answer «where is this call coming from» — see
  «Request-scoped state».

If a class needs no dependencies and could be unit-tested without a database, it belongs
in `Support/`.

## Read layer

Every read goes through a **Query** class in `app/Modules/{Module}/Queries/`. There are
no repositories in this project, and a read is not an Action: an Action changes state,
a Query answers a question.

- **Naming:** `Find*Query` returns one record (`FindOrderByUuidQuery`,
  `FindCategoryBySlugQuery`), `Get*Query` returns a known set
  (`GetNewsListQuery`, `GetUserOrdersQuery`), `Search*Query` returns a set built from
  criteria (`SearchProductsQuery`).
- **One read per class**, a single public `handle()`. Return the model, `Collection`,
  `LengthAwarePaginator` or array the caller needs — not a builder, unless the class says
  so in its name (`BuildProductsQuery`).
- **Input is scalars, enums, models or a DTO — never a `Request`, and never the
  session or the current user read from inside** (see «Request-scoped state»). Several criteria go
  into a DTO (`SearchCategoriesQuery::handle(CategorySearchData $criteria)`,
  `GetUserOrdersQuery::handle(int $userId, OrderFilterData $filter)`), so the same Query
  serves web and API — `PrepareShopFilterQuery` takes `ShopFilterCriteriaData`, which the
  controller builds from the request.
- **Layout:** flat in `Queries/` for a small module (`News/Queries/GetNewsListQuery.php`);
  grouped per entity once there are many
  (`Shop/Queries/{Category,Product,ProductReview}/`).
- **Who calls it:** controllers inject Queries directly, and so do Actions that need to
  read before they write. A ViewModel never queries — it is fed what a Query returned.
- Dashboard widgets have their own family under `app/Filament/Dashboard/Queries/`,
  same rules.
- Caching belongs inside the Query that owns the read — and so does its invalidation
  (`GetAllPagesQuery` + `PageObserver`). Full page: `docs/starter/queries.md`.

## API layer conventions

- **Actions** hold write logic — shared between web and API controllers
- **Queries** hold reads — see «Read layer» above
- **Controllers** are thin HTTP adapters only
- **DTOs** (`spatie/laravel-data`) structure input validation, and are named `*Data`
  — one suffix for the whole project, so `Dto` never appears in a class name
  (`ProductSearchData`, `ShopFilterCriteriaData`); the `Dtos/` folder keeps its name
- **Resources** structure API responses
- Response envelope: `{ success, message, data, errors }` — the same four keys on
  success and on failure, built only by `Core\Support\ApiEnvelope` (controllers
  reach it through the `ApiResponse` trait, exceptions through the `api/*` renderer
  in `bootstrap/app.php`). A success nulls `errors`, a failure nulls `data`; neither
  key is ever omitted, so a client parses one shape. See `docs/api/README.md`.

## Action return values

An Action returns exactly one of three things — never `bool`:

1. `void` — it mutates state and has nothing to report
2. a domain value (`Order`, `User`, `float`, …) — it always succeeds, any failure is an exception
3. `ActionResult<T>` (`App\Modules\Core\Dtos\ActionResult`) — the failure is an
   expected business outcome the caller must branch on (invalid promocode, quota exceeded)

The payload of an `ActionResult` goes in its own small DTO inside the owning
module, with **non-nullable** fields; the envelope only carries `success`,
`message`, `data`. Annotate with `/** @return ActionResult<PromocodeData> */`.
A genuine two-state query belongs in a `Query` class, not an Action.
See `docs/starter/actions.md`.

## Transactions

An Action that writes to **more than one table** wraps its writes in
`DB::transaction()`. Placing an order touches `shop_orders`, `shop_order_products`,
`shop_promocodes` and `shop_carts`; a failure halfway used to leave a half-written
order, a promocode already counted as used and a cart still full.

```php
public function handle(Cart $cart, CustomerData $customer): Order
{
    return DB::transaction(fn () => $this->place($cart, $customer));
}
```

Keep the closure a one-liner delegating to a private method when the body is long —
the transaction boundary stays readable and the logic keeps its own name.

Nesting is fine and expected: `PlaceOrderFromCartAction` wraps `CreateOrderAction`,
which has a transaction of its own. Laravel turns the inner one into a savepoint, so
each action is still safe when called on its own.

### Side effects go after the commit

Letters, SMS, webhooks and queued jobs must not fire from inside a transaction — a
rollback would leave the customer holding a letter about an order that no longer
exists. Use `DB::afterCommit()`, which runs the callback at the **outermost** commit
and immediately when there is no transaction at all:

```php
DB::afterCommit(fn () => $this->letters->send(
    new SendLetterTemplateData(key: LetterEnum::ORDER_CREATED, ...)
));
```

Writing the call after the closure instead is not equivalent: the action may itself be
running inside a caller's transaction, and then "after the closure" is still inside it.

Single-statement atomicity does not need a transaction — `$promocode->increment('used_count')`
is one SQL statement (`IncrementPromocodeUsageAction`).

## Code style

```bash
./vendor/bin/pint          # fix
./vendor/bin/pint --test   # check only, no writes
```

`pint.json` pins the `laravel` preset. CI runs `pint --test` before the test
suite (`.github/workflows/tests.yml`), so unformatted code fails the build —
run Pint before committing.
