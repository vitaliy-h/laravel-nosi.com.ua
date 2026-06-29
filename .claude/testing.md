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
| `Feature/Filament/{Module}/` | Filament resource CRUD tests (list, search, filter, create, edit, delete) |
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

### Rule: always create a factory for models that lack one

Before writing a test for a model, check if `database/factories/{Model}Factory.php` exists. If not:

1. Create `database/factories/{Model}Factory.php` — set `protected $model`, use `fake()`, add relevant states
2. Add `HasFactory` + explicit factory import to the model class
3. Use `Model::factory()` in the test — never create records via `Model::create()` inline

Pattern follows existing factories (`NewsFactory`, `UserFactory`): explicit `protected $model`, `unique()` on email/slug fields, named states for boolean variants (e.g. `processed()`, `invisible()`).

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
| `FeedbackFactory` | `App\Modules\Feedback\Models\Feedback` — states: `processed()` |
| `LetterTemplateFactory` | `App\Modules\Notification\Models\LetterTemplate` |
| `SmsTemplateFactory` | `App\Modules\Notification\Models\SmsTemplate` |
| `LanguageFactory` | `App\Modules\Translation\Models\Language` — states: `forLocalization(Localization)` — loads real `lang/{locale}/dictionary.php` |
| `GeoCountryFactory` | `App\Modules\Geo\Models\GeoCountry` |
| `GeoRegionFactory` | `App\Modules\Geo\Models\GeoRegion` — states: `forCountry(GeoCountry)` |
| `GeoCityFactory` | `App\Modules\Geo\Models\GeoCity` — states: `forCountry(GeoCountry)`, `forRegion(GeoRegion)` |
| `LogLetterFactory` | `App\Modules\Notification\Models\LogLetter` — states: `failed()` |
| `LogSmsFactory` | `App\Modules\Notification\Models\LogSms` — states: `failed()` |
| `LogFactory` | `App\Modules\Core\Models\Log` — states: `forUser(User)`, `forEvent(LogEventEnum)` |

Models under `App\Modules\…` set `protected $model` on the factory when namespace differs from Laravel defaults.

Default user password from factory: `password`.

### Seeders

| Seeder | When to use |
|--------|-------------|
| `PagesTableSeeder` | Full set of module routes (home, news, shop, auth, …) — use via `seedPagesForRouting()` in smoke/routing tests |
| `Page::factory()` | One-off pages, custom slugs, invisible pages — unit/feature tests that do not need the full site map |
| `LetterTemplateSeeder` | Password recovery and other letter-sending flows — use via `seedLetterTemplates()` |

`DatabaseSeeder` calls both in production-like setups; in tests seed only what you need.

## Filament module tests

### Directory

```
tests/Feature/Filament/{Module}/
```

Each Filament resource gets its own subdirectory. Name the class `{Resource}ResourceTest`.

### What to test per resource

| Test | What it covers |
|------|----------------|
| `test_list_renders_table` | ManageRecords page mounts, table renders |
| `test_list_can_search_{field}` | `->searchTable()` finds matching record, hides non-matching |
| `test_list_can_filter_by_{filter}` | `->filterTable()` narrows results |
| `test_create_{resource}_with_valid_data` | CreateAction form submits, record in DB |
| `test_create_{resource}_requires_{field}` | Required field empty → validation error |
| `test_edit_{resource}_with_valid_data` | EditAction form submits, DB updated |
| `test_delete_{resource}` | DeleteAction removes record from DB |

### Conventions

- All tests use `Livewire::test(ManageXxx::class)` — resources in this project are single-page `ManageRecords`
- Always call `$this->actingAsAdmin()` + `$this->actingAs($admin)` before Livewire assertion
- Use `callAction(TestAction::make(...)->table(...))` — `callTableAction` is deprecated in Filament v5
- Toolbar action (Create, no record): `TestAction::make(CreateAction::getDefaultName())->table()`
- Row action (Edit/Delete, with record): `TestAction::make(EditAction::getDefaultName())->table($record)`
- Error assertions: `assertHasNoFormErrors()` / `assertHasFormErrors(['field'])` — `assertHasNoTableActionErrors` is deprecated
- `filterTable('roles', $role)` — pass Model directly for single `SelectFilter`; no array wrapping needed
- Do not assert translated strings — assert DB state or Livewire component state

### Example: `tests/Feature/Filament/Users/UserResourceTest.php`

```php
namespace Tests\Feature\Filament\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Modules\Core\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        User::factory()->count(3)->create();

        Livewire::test(ManageUsers::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_email(): void
    {
        $target = User::factory()->create(['email' => 'find@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        Livewire::test(ManageUsers::class)
            ->searchTable('find@example.com')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([/* other user */]);
    }

    public function test_list_can_filter_by_role(): void
    {
        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        $withRole = User::factory()->create();
        $withRole->assignRole($role);
        $withoutRole = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->filterTable('roles', $role)          // pass Model directly for single SelectFilter
            ->assertCanSeeTableRecords([$withRole])
            ->assertCanNotSeeTableRecords([$withoutRole]);
    }

    public function test_create_user_with_valid_data(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'     => 'John',
                'email'    => 'john@example.com',
                'password' => 'secret123',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_create_user_requires_email(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'     => 'No Email',
                'password' => 'secret123',
            ])
            ->assertHasFormErrors(['email']);
    }

    public function test_edit_user_with_valid_data(): void
    {
        $user = User::factory()->create(['name' => 'Old']);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($user), data: [
                'name'  => 'Updated',
                'email' => $user->email,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated']);
    }

    public function test_delete_user(): void
    {
        $user = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($user))
            ->assertSuccessful();

        $this->assertModelMissing($user);
    }
}
```

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
