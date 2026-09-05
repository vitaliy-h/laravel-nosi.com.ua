# UUID Fields

The project provides `HasUuid` trait for automatically generating UUID values on model creation.

Trait location: `app/Modules/Core/Traits/Models/HasUuid.php`

---

## How it works

`initializeHasUuid()` is called by Laravel on every new model instance (during `__construct`), before `fill()`. This guarantees the UUID is always set before the `INSERT` query — including factory creation, seeders, and direct `Model::create()` calls.

If a field already has a value (e.g. set explicitly), it is not overwritten.

---

## Adding UUID to a model

### 1. Add column to migration

```php
$table->uuid('uuid')->unique();
```

Place it after the primary `id`:

```php
$table->bigIncrements('id');
$table->uuid('uuid')->unique();
```

> Edit the existing creation migration — do not create a new one unless the table is already in production.

### 2. Add trait to model

```php
use App\Modules\Core\Traits\Models\HasUuid;

class Order extends Model
{
    use HasUuid;

    protected array $uuidFields = ['uuid'];

    protected $fillable = [
        'uuid',
        // ...
    ];
}
```

`$uuidFields` defaults to `['uuid']` in the trait, so it can be omitted if the field name is `uuid`.

### 3. Multiple UUID fields

```php
protected array $uuidFields = ['uuid', 'public_token'];
```

Each field listed will be auto-generated on creation.

---

## Fetching by UUID

Put the lookup in a Query class, not a controller (there are no repositories in this
project — see `.claude/backend.md` → «Read layer»):

```php
// app/Modules/ShopOrder/Queries/FindOrderByUuidQuery.php

class FindOrderByUuidQuery
{
    public function handle(string $uuid): Order
    {
        return Order::where('uuid', $uuid)
            ->with(['items.product.category'])
            ->firstOrFail();
    }
}
```

The controller injects it and hands the result to a ViewModel
(`ShopOrder\Http\Controllers\Web\IndexController`):

```php
$order = OrderViewModel::from($this->findOrderByUuid->handle($uuid));
```

---

## Route example

Routes go in the module's `Http/Routes/web.php`:

```php
// app/Modules/ShopOrder/Http/Routes/web.php

Route::get('/{uuid}', [IndexController::class, 'show'])
    ->name('order.show');
```

`ShopOrder` maps to no `PageModule`, so the group is mounted under `/orders`
(`ModuleServiceProvider::MODULE_PREFIX_OVERRIDES`) — the public URL is
`/orders/{uuid}`.

For modules mapped to a `PageModule`, `ShareModulePageContext` middleware
shares `page`, `breadcrumbs`, `headerMenu`, `footerMenu`, `cartCount`
automatically — no manual `view()->share()` needed. Modules without a
`PageModule` mapping don't get this and must share what the layout needs
themselves: `ShopOrder`'s controller calls
`PageContextSharer::shareWithoutPage()` for exactly that. See
`docs/starter/routing.md`.

---

## Testing

`Order::factory()->create()` automatically gets a UUID via `initializeHasUuid` — no extra setup needed.

```php
$order = Order::factory()->create();

$this->get('/orders/' . $order->uuid)->assertOk();
```

To test the Query directly:

```php
$this->expectException(ModelNotFoundException::class);
app(FindOrderByUuidQuery::class)->handle('00000000-0000-0000-0000-000000000000');
```
