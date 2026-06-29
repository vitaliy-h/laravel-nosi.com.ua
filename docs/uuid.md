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

Put lookup logic in a repository, not a controller:

```php
// app/Modules/Shop/Repository/OrderRepository.php

public function findByUuid(string $uuid): Order
{
    return Order::where('uuid', $uuid)
        ->with(['items.product.category'])
        ->firstOrFail();
}
```

---

## Route example

Direct routes (not CMS-managed) go in the module's `routes/web.php`:

```php
// app/Modules/Shop/routes/web.php

Route::get('/order/{uuid}', [OrderController::class, 'show'])
    ->name('order.show');
```

Controllers that bypass `ModuleRouteController` must share layout variables manually:

```php
view()->share([
    'page'        => null,
    'breadcrumbs' => collect(),
    'headerMenu'  => $this->menu->getHeaderMenu(),
    'footerMenu'  => $this->menu->getFooterMenu(),
]);
```

---

## Testing

`Order::factory()->create()` automatically gets a UUID via `initializeHasUuid` — no extra setup needed.

```php
$order = Order::factory()->create();

$this->get('/order/' . $order->uuid)->assertOk();
```

To test the repository directly:

```php
$this->expectException(ModelNotFoundException::class);
$this->repository->findByUuid('00000000-0000-0000-0000-000000000000');
```
