# Testing Guide

This project uses **PHPUnit** (`phpunit/phpunit ^12.5`). Tests are split into two
suites defined in `phpunit.xml`:

- **Unit** (`tests/Unit`) — fast, isolated tests for a single class (services, DTOs,
  enums, helpers, actions in isolation).
- **Feature** (`tests/Feature`) — full HTTP / framework tests that boot the
  application (routes, controllers, middleware, database, observers).

All test classes extend `Tests\TestCase`, which wires up factory name resolution and
the shared concerns `InteractsWithPages` and `InteractsWithRolesAndPermissions`.

---

## 1. Running tests

The test database is configured in `phpunit.xml` (`DB_DATABASE=testing`,
`APP_ENV=testing`). When using Sail, the MySQL container runs Laravel's
`create-testing-database.sh` init script, so the `testing` database is created
automatically on first container startup. If you run tests against a local MySQL
server outside Sail, create the `testing` database manually. If an old Sail MySQL
volume was initialized before this script existed, recreate the volume or create
the database manually once.

### Run the whole suite

```bash
# via composer script (clears config first, then runs artisan test)
composer test

# or directly
php artisan test
```

When using Sail (Docker), prefix with `./vendor/bin/sail`:

```bash
./vendor/bin/sail artisan test
```

### Run a single suite

```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

### Run a single file, directory, or method

```bash
# one file
php artisan test tests/Feature/Shop/ShopCatalogApiTest.php

# one directory
php artisan test tests/Unit/Shop

# filter by method/class name
php artisan test --filter=test_login_with_valid_credentials_succeeds
```

### Useful flags

```bash
php artisan test --parallel        # run suites in parallel (faster)
php artisan test --stop-on-failure # stop at the first failure
php artisan test --coverage        # requires Xdebug/PCOV
```

> Reminder (project rule): **always add newly created test files to git.**

---

## 2. Feature tests

### When to create one

Write a Feature test whenever behaviour is observable through the framework, i.e.:

- **A new route is added** (API or web) — cover the happy path plus the key failure
  paths (validation `422`, auth `401`, not found `404`, forbidden `403`).
- **The logic of an existing route changes** — add/adjust assertions so the new
  behaviour is locked in and regressions are caught.
- **Middleware, policies, or authentication** affect access to an endpoint.
- **Model observers / side effects** that only happen during a real request lifecycle.

Rule of thumb: if you touched `routes/*.php`, a controller, a FormRequest, a Resource,
or a Filament page, there should be a Feature test for it.

### Conventions

- Location: `tests/Feature/{Module}/{Thing}Test.php`, namespace `Tests\Feature\{Module}`.
- Add `use RefreshDatabase;` so each test runs against a clean migrated database.
- Use model **factories** to arrange data, and `getJson` / `postJson` for API calls.
- API responses in this project follow the `{ success, message, data }` envelope —
  assert it with `assertJsonPath('success', ...)` and `assertJsonStructure([...])`.

### Example — testing a new API route

This is the pattern used in `tests/Feature/Shop/ShopCatalogApiTest.php`. It arranges
data with factories, hits the route, and asserts the response envelope and content:

```php
namespace Tests\Feature\Shop;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index_returns_only_visible_categories(): void
    {
        Category::factory()->create(['slug' => 'visible-cat']);
        Category::factory()->invisible()->create(['slug' => 'hidden-cat']);

        $response = $this->getJson('/api/shop/categories');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['categories'],
        ]);

        $slugs = collect($response->json('data.categories'))->pluck('slug')->all();

        $this->assertContains('visible-cat', $slugs);
        $this->assertNotContains('hidden-cat', $slugs);
    }

    public function test_category_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/shop/categories/missing-slug');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }
}
```

### Example — validation and authentication

From `tests/Feature/Auth/AuthApiTest.php`. Note `assertJsonValidationErrors(...)` for
`422` responses and `actingAs($user, 'sanctum')` to test authenticated endpoints:

```php
public function test_login_validates_empty_request(): void
{
    $response = $this->postJson('/api/auth/login', []);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonValidationErrors(['email', 'password']);
}

public function test_logout_with_token_succeeds(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/logout');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
}
```

### Shared test helpers (Concerns)

`Tests\TestCase` mixes in two reusable traits — use them instead of re-seeding by hand:

- `InteractsWithRolesAndPermissions` — `actingAsAdmin()` returns a user holding the
  `admin` permission; `seedRolesAndPermissions()` creates the base permissions.
- `InteractsWithPages` — `seedPagesForRouting()` and `seedLetterTemplates()` seed the
  data needed for routing/letters Feature tests.

```php
public function test_admin_endpoint_requires_admin(): void
{
    $admin = $this->actingAsAdmin();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
}
```

For broad coverage of "does every endpoint still boot", see the smoke tests in
`tests/Feature/Smoke/` — a lightweight way to guard against `500`s across many routes.

---

## 3. Unit tests

### When to create one

Write a Unit test when logic can be exercised **without the HTTP layer**:

- **Pure logic / calculations** — formatting, normalization, rounding, mapping.
- **DTOs** (`spatie/laravel-data`) — defaults, type casting, hydration of nested data.
- **Enums** — values, labels, helper methods.
- **Services / helpers** resolved from the container (`app(MyService::class)`).
- **Actions in isolation** — when you want to assert business rules without going
  through a controller (note: an Action that hits the DB still needs `RefreshDatabase`).

Rule of thumb: if you can call the class directly and assert the return value, it
belongs in `tests/Unit`.

### Conventions

- Location: `tests/Unit/{Module}/{Thing}Test.php`, namespace `Tests\Unit\{Module}`.
- Add `use RefreshDatabase;` **only if** the test touches the database; pure logic
  tests (DTOs, enums, string helpers) should not, so they stay fast.
- Resolve dependencies via the container with `app(...)` rather than `new`.

### Example — pure service (no database)

From `tests/Unit/Core/UrlHelperTest.php`:

```php
namespace Tests\Unit\Core;

use App\Modules\Core\Services\UrlHelper;
use Tests\TestCase;

class UrlHelperTest extends TestCase
{
    private function helper(): UrlHelper
    {
        return app(UrlHelper::class);
    }

    public function test_normalize_trims_whitespace_and_slashes(): void
    {
        $this->assertSame('foo/bar', $this->helper()->normalize('  /foo/bar/  '));
    }

    public function test_slug_supports_nested_segments(): void
    {
        $this->assertSame('parent/child-page', $this->helper()->slug('Parent/Child Page'));
    }
}
```

### Example — DTO casting and defaults (no database)

From `tests/Unit/Shop/OrderDtoTest.php`:

```php
public function test_order_item_data_maps_fields_and_types(): void
{
    $item = OrderItemData::from([
        'shop_product_id' => 10,
        'product_name'    => 'Widget',
        'product_sku'     => 'SKU-1',
        'sale_price'      => '19.99',
        'quantity'        => '2',
    ]);

    $this->assertSame(10, $item->shop_product_id);
    $this->assertSame(19.99, $item->sale_price);
    $this->assertSame(2.0, $item->quantity);
}

public function test_create_order_data_applies_defaults(): void
{
    $order = CreateOrderData::from(['user_id' => 1]);

    $this->assertSame(PaymentStatusEnum::UNPAID, $order->payment_status);
    $this->assertSame(PaymentTypeEnum::NONE, $order->payment_type);
    $this->assertSame([], $order->items);
}
```

### Example — Action with database side effects

From `tests/Unit/Shop/CreateOrderActionTest.php`. Because the Action persists records,
this test uses `RefreshDatabase` and asserts against the database directly:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateOrderActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_order_with_items_and_correct_total(): void
    {
        $user = User::factory()->create();
        $productA = Product::factory()->create();

        $dto = CreateOrderData::from([
            'user_id' => $user->id,
            'items'   => [[
                'shop_product_id' => $productA->id,
                'product_name'    => 'Product A',
                'product_sku'     => 'SKU-A',
                'sale_price'      => 10.00,
                'quantity'        => 2,
            ]],
        ]);

        $order = app(CreateOrderAction::class)->handle($dto);

        $this->assertDatabaseHas('shop_orders', ['id' => $order->id]);
        $this->assertSame('20.00', (string) $order->total_amount);
    }
}
```

---

## Quick reference

| Situation | Suite | `RefreshDatabase`? |
| --- | --- | --- |
| New/changed route, controller, middleware | Feature | Yes |
| Auth / permission protected endpoint | Feature | Yes |
| Pure helper, formatter, calculation | Unit | No |
| DTO defaults / casting, Enum behaviour | Unit | No |
| Action / service that hits the DB | Unit | Yes |
| "Everything still boots" sanity check | Feature (`Smoke/`) | Yes |

**Naming:** test methods use `snake_case` prefixed with `test_` and describe the
expected outcome (e.g. `test_category_show_returns_404_for_invisible_category`).
