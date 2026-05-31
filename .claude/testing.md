# Testing Guide

## Stack

- **PHPUnit** 12 + **Pest** plugin
- Tests live in `tests/Feature/` and `tests/Unit/`

## Run tests

```bash
php artisan test              # all tests
php artisan test --filter=ClassName  # single class
php artisan test tests/Feature/Auth  # single directory
```

## Feature vs Unit

| Type | Location | Purpose |
|------|----------|---------|
| Feature | `tests/Feature/` | HTTP endpoints, full stack, DB interaction |
| Unit | `tests/Unit/` | Isolated class logic (Actions, DTOs, helpers) |

## Testing Actions (business logic)

Actions hold shared business logic. Test them directly — no HTTP overhead:

```php
namespace Tests\Unit\Shop;

use App\Modules\Shop\Actions\CreateProductAction;
use App\Modules\Shop\Dtos\ProductData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProductActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_product_with_valid_data(): void
    {
        $dto = ProductData::from(['name' => 'Widget', 'price' => 9.99]);
        $product = (new CreateProductAction)->execute($dto);

        $this->assertDatabaseHas('products', ['name' => 'Widget']);
        $this->assertEquals(9.99, $product->price);
    }
}
```

## Testing API endpoints

Controllers are thin — test the full HTTP layer via Feature tests:

```php
namespace Tests\Feature\Shop;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_product(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/products', ['name' => 'Widget', 'price' => 9.99]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['success', 'data' => ['id', 'name']]);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->postJson('/api/products', [])->assertStatus(401);
    }
}
```

## Testing GET routes

GET routes are database-driven through `ModuleRouteController`. Test public pages via seeded `pages` table records:

```php
public function test_news_index_returns_200(): void
{
    // Ensure pages table has the route seeded (use DatabaseSeeder or factory)
    $this->seed(PagesTableSeeder::class);

    $this->get('/news')->assertStatus(200);
}
```

## Conventions

- Use `RefreshDatabase` for any test that reads/writes DB
- Name test methods descriptively: `test_{subject}_{condition}_{expected_result}`
- Group tests by module: `tests/Feature/Shop/`, `tests/Unit/Auth/`, etc.
- Factories live in `database/factories/` — create one per model and keep them minimal
- Don't mock internals; mock only external services (payment gateways, mail, queues)
- Use `$this->actingAs($user, 'sanctum')` for API auth, `$this->actingAs($user)` for web


