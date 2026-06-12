# Testing Guide

## Stack

- **PHPUnit 12 only** — plain PHPUnit test classes extending `Tests\TestCase`
- **Pest is not used** — do not add `pestphp/pest`, `uses()`, or `it()`/`describe()` syntax
- Test directories:
  - `tests/Feature/` — HTTP, Livewire, full stack
  - `tests/Unit/` — Actions, DTOs, repositories, helpers (no HTTP)
  - `tests/Concerns/` — shared traits (`InteractsWithPages`, `InteractsWithRolesAndPermissions`)

`phpunit.xml` defines two suites: `Unit` and `Feature`.

## Run tests (Sail)

Tests **must** run inside Laravel Sail (PHP 8.4 in container). Do not run `php artisan test` on the host — local PHP 8.3 will fail on Symfony 8.

```bash
# all tests
./vendor/bin/sail test

# single class
./vendor/bin/sail test --filter=AuthApiTest

# directory
./vendor/bin/sail test tests/Feature/Smoke

# suite
./vendor/bin/sail test --testsuite=Feature
```

If containers are not up: `./vendor/bin/sail up -d`.

**Windows / PowerShell** (when `./vendor/bin/sail` is unavailable):

```powershell
docker compose exec laravel.test php artisan test
```

See also: [`.cursor/rules/run-tests-in-sail.mdc`](../.cursor/rules/run-tests-in-sail.mdc).

## Test structure

| Directory | Purpose |
|-----------|---------|
| `Feature/Smoke/` | Key URLs respond without 500 (frontend, admin, API validation) |
| `Feature/Errors/` | Custom 404 for frontend, admin, and API |
| `Feature/Auth/` | API (`AuthApiTest`) and web (`AuthWebTest`) authentication |
| `Feature/Admin/` | Filament login, permissions, panel access |
| `Feature/Shop/` | Shop catalog API |
| `Feature/Profile/` | Authenticated profile API |
| `Feature/Infrastructure/` | Factories, seeders, auth helpers smoke |
| `Unit/` | `CreateOrderAction`, DTOs, `ShopRepository`, `UrlHelper`, enums |

Group new tests by module and layer. Name methods: `test_{subject}_{condition}_{expected_result}`.

## Patterns

### Base test case

`Tests\TestCase` includes:

- `InteractsWithPages` — `seedPagesForRouting()`, `seedLetterTemplates()`
- `InteractsWithRolesAndPermissions` — `seedRolesAndPermissions()`, `actingAsAdmin()`

Factory resolution is configured in `setUp()` for models under `App\Modules\…`.

### Database

Use `RefreshDatabase` in any test that reads or writes the database.

### Frontend routing

Public pages are resolved via the `pages` table (`ModuleRouteController`). For smoke tests and web auth, seed routes in `setUp()` or per test:

```php
$this->seedPagesForRouting(); // wraps PagesTableSeeder
```

For a **single custom page**, use `Page::factory()` instead of the full seeder.

### Authentication

| Context | Pattern |
|---------|---------|
| API (Sanctum) | `$this->actingAs($user, 'sanctum')` |
| Web session | `$this->actingAs($user)` |
| Filament admin user | `$admin = $this->actingAsAdmin();` then `$this->actingAs($admin)` |

`actingAsAdmin()` creates a user with the `admin` permission (seeds permissions first). It does **not** call `actingAs()` — the caller decides.

Admin panel tests that use the Livewire login form: `Livewire::test(Login::class)` (`App\Filament\Pages\Auth\Login`).

### Assertions

- Assert HTTP status, JSON structure/paths (`assertJsonPath`, `assertJsonStructure`), redirects, session keys
- Assert database state with `assertDatabaseHas`, `assertModelExists`
- **Do not assert `t(…)` translation strings** — locales change; assert status codes, structure, HTML markers (e.g. CSS classes), or session flash keys instead
- Smoke tests: `assertStatus($expected)` and `assertNotEquals(500, $response->status())`

### Mocking

Do not mock internal classes. Mock only external boundaries (e.g. `LetterTemplateFacade::shouldReceive('send')` in password-recovery tests).

## Checklist: adding a new test

1. Choose `Feature/` or `Unit/` and the correct subdirectory (see table above)
2. Create `*Test.php` extending `Tests\TestCase` (PHPUnit class, not Pest)
3. Add `use RefreshDatabase;` if the test touches the DB
4. Seed what the feature needs:
   - `seedPagesForRouting()` — frontend URLs
   - `seedLetterTemplates()` — mail/letter flows
   - `seedRolesAndPermissions()` / `actingAsAdmin()` — admin/Filament
   - Model factories — shop catalog, orders, profile
5. Use the correct auth guard (`sanctum` vs web)
6. Assert behavior, not translated copy
7. Run via Sail: `./vendor/bin/sail test --filter=YourTest`
8. Stage new test files in git manually (do not auto-`git add` from agents)

## Factories and seeders

### Factories (`database/factories/`)

| Factory | Model |
|---------|-------|
| `UserFactory` | `App\Modules\Core\Models\User` |
| `PageFactory` | `App\Modules\Core\Models\Page` — states: `forModule()`, `withSlug()`, `invisible()` |
| `BrandFactory` | `App\Modules\Shop\Models\Brand` |
| `CategoryFactory` | `App\Modules\Shop\Models\Category` — `invisible()` |
| `ProductFactory` | `App\Modules\Shop\Models\Product` — `forCategory()`, `invisible()` |
| `OrderFactory` | `App\Modules\Shop\Models\Order` |
| `OrderProductFactory` | `App\Modules\Shop\Models\OrderProduct` |

Models under `App\Modules\…` set `protected $model` on the factory when namespace differs from Laravel defaults.

Default user password from factory: `password`.

### Seeders

| Seeder | When to use |
|--------|-------------|
| `PagesTableSeeder` | Full set of module routes (home, news, shop, auth, …) — use via `seedPagesForRouting()` in smoke/routing tests |
| `Page::factory()` | One-off pages, custom slugs, invisible pages — unit/feature tests that do not need the full site map |
| `LetterTemplateSeeder` | Password recovery and other letter-sending flows — use via `seedLetterTemplates()` |

`DatabaseSeeder` calls both in production-like setups; in tests seed only what you need.

## Examples

### Unit: Action

```php
namespace Tests\Unit\Shop;

use App\Modules\Core\Models\User;
use App\Modules\Shop\Actions\CreateOrderAction;
use App\Modules\Shop\Dtos\CreateOrderData;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_order_with_items_and_correct_total(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $dto = CreateOrderData::from([
            'user_id' => $user->id,
            'items' => [[
                'shop_product_id' => $product->id,
                'product_name'    => 'Product A',
                'product_sku'     => 'SKU-A',
                'sale_price'      => 10.00,
                'quantity'        => 1,
            ]],
        ]);

        $order = app(CreateOrderAction::class)->handle($dto);

        $this->assertDatabaseHas('shop_orders', ['id' => $order->id]);
    }
}
```

### Feature: API (Sanctum)

```php
namespace Tests\Feature\Profile;

use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_user_data_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/update-user-data', [
                'name' => 'New',
                'email' => 'new@b.com',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}
```

### Feature: frontend page (seeded routes)

```php
namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPagesForRouting();
    }

    public function test_home_returns_200(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertNotEquals(500, $response->status());
    }
}
```

### Feature: custom page via factory

```php
use App\Modules\Core\Models\Page;

$page = Page::factory()->withSlug('custom-route')->create();

$this->get('/custom-route')->assertOk();
```

### Feature: Filament admin

```php
namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->actingAsAdmin();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }
}
```
