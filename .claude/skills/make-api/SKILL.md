---
name: make-api
description: >
  Interactive generator for Laravel API endpoints following project conventions:
  BaseApiController, Actions (shared with web), FormRequest with attributes(), JsonResource, api.php routes.
  Use when the user says "make api", "create api method", "api endpoint", "зроби апі", "апі метод", or invokes /make-api.
---

## Principles (from Profile module)

1. **Controller** extends `BaseApiController` → gets `$this->success()` / `$this->error()`
2. **Actions** hold business logic, injectable into both web and API controllers
3. **DTOs** (`spatie/laravel-data`) carry validated data from request to action
4. **FormRequest** per method — always has `rules()` + `attributes()` (using `t('module.field')`)
5. **Resource** extends `JsonResource` — always used for output, never raw model/array.
   Resources are the **API's** output layer only; the web side has its own —
   a controller rendering a blade hands it a **ViewModel**
   (`app/Modules/{Module}/ViewModels/`, see `docs/starter/view-models.md`).
   Neither one is reused as the other: an API response and a rendered page
   need different fields.
6. **Routes** in `app/Modules/{Module}/Http/Routes/api.php` — auto-loaded by `ModuleServiceProvider` (wrapped in `Route::prefix('api')->middleware('api')->group(...)`)
7. **Translations** in `lang/en/dictionary.php` under `'shop' => [...]` key (key = module slug)

---

## Flow

### Step 1 — Module

If not specified by user, read `app/Modules/Core/Enums/PageModule.php` and list existing modules as numbered list.
Ask user to pick one.

This skill never creates modules — pick an existing one; if the task needs a new module,
hand it to `make-model` first. Either way the naming rule holds: module directory names are
**singular PascalCase** and name the module's aggregate root (`ShopOrder`, not `ShopOrders`).
Never write an `app/Modules/{Module}` path with a pluralised module name.

### Step 2 — Method name & HTTP verb

Ask: **Method name?** (e.g. `index`, `show`, `store`, `update`, `destroy`)
Ask: **HTTP verb?** (GET / POST / PUT/PATCH / DELETE) — suggest sensible default based on name.

### Step 3 — Input parameters

Ask the user to describe input parameters. Prompt with examples:
- Pagination? (per_page, page)
- Search? (search string)
- Filters? (which fields)
- Route parameters? (e.g. `{id}`, `{slug}`)
- Auth required? (yes/no — affects `middleware('auth:sanctum')`)

Collect answers as a list. If "none", FormRequest will have empty `rules()`.

### Step 4 — Output data

Ask: **What fields should the response contain?** (list model fields to expose, e.g. `id, title, slug, img_url`)
These become the `toArray()` body of the Resource.

Ask: **Single object or collection?** (affects whether we wrap in `ResourceName::collection()` or `new ResourceName()`)

Response data must always be wrapped in a named key matching the entity:
- Collection → `['categories' => CategoryResource::collection($result)]`
- Single → `['category' => new CategoryResource($result)]`

Key is the snake_case entity name (singular for object, plural for collection).

### Step 5 — Confirm plan

Print summary before writing any files:

```
Module:      {Module}
Controller:  app/Modules/{Module}/Http/Controllers/Api/{Module}Controller.php
  method:    {verb} {method}()
Action:      app/Modules/{Module}/Actions/{ActionName}Action.php
Request:     app/Modules/{Module}/Http/Requests/{Module}{MethodName}Request.php
Resource:    app/Modules/{Module}/Http/Resources/{ResourceName}Resource.php  (if not exists)
Route:       app/Modules/{Module}/Http/Routes/api.php  → {VERB} /{route-path}
Translations: lang/en/dictionary.php  → '{module}' key
```

Ask: **Proceed?**

### Step 6 — Detect existing infrastructure

Before writing, check what already exists:
- Does `Api/{Module}Controller.php` exist? → append method, not overwrite
- Does `{ResourceName}Resource.php` exist? → reuse, skip creation
- Does `Http/Routes/api.php` exist? → append route, not overwrite
- Does `'shop'` key exist in `dictionary.php`? → append keys, not overwrite
- Does the Action exist? → if web controller already does the same logic, extract it; if Action exists reuse it

### Step 7 — Extract Action (if web logic exists)

Check if the web controller for this module (`Web/IndexController.php` or similar) has equivalent logic.
If yes: extract repeated repository/service calls into `app/Modules/{Module}/Actions/{ActionName}Action.php`.

Action signature:
```php
class {ActionName}Action
{
    public function __construct(private readonly {Module}Repository $repository) {}

    public function handle({params}): {ReturnType}
    {
        // logic here
    }
}
```

Web controller update: inject Action, replace inline logic with `$this->{actionName}->handle(...)`.

### Step 8 — Create FormRequest

Path: `app/Modules/{Module}/Http/Requests/{Module}{MethodName}Request.php`

Template:
```php
<?php

namespace App\Modules\{Module}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {Module}{MethodName}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // fields from Step 3
        ];
    }

    public function attributes(): array
    {
        return [
            'field' => t('{module}.field_label'),
        ];
    }
}
```

For **GET list** methods (index, search) with pagination/filters only → use `Illuminate\Http\Request` directly instead of a custom FormRequest (no validation needed for optional query params). Skip FormRequest creation for these.

### Step 9 — Create/update Resource

Path: `app/Modules/{Module}/Http/Resources/{ResourceName}Resource.php`

Template:
```php
<?php

namespace App\Modules\{Module}\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {ResourceName}Resource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // fields from Step 4
        ];
    }
}
```

If model has image (`img` field), use `$this->image` (accessor from `HasImage` trait), not raw `$this->img`.

### Step 10 — Create/update Controller

Path: `app/Modules/{Module}/Http/Controllers/Api/{Module}Controller.php`

Template (new controller):
```php
<?php

namespace App\Modules\{Module}\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\{Module}\Actions\{ActionName}Action;
use App\Modules\{Module}\Http\Requests\{Module}{MethodName}Request;
use App\Modules\{Module}\Http\Resources\{ResourceName}Resource;

class {Module}Controller extends BaseApiController
{
    public function __construct(
        private readonly {ActionName}Action $action,
    ) {}

    public function {method}({RequestClass} $request): \Illuminate\Http\JsonResponse
    {
        $result = $this->action->handle(/* params */);

        return $this->success(
            ['{entities}' => {ResourceName}Resource::collection($result)],  // collection
            // or: ['{entity}' => new {ResourceName}Resource($result)],      // single object
        );
    }
}
```

For paginated responses, pass the paginator directly to `::collection()` — Laravel Resource handles pagination meta automatically.

### Step 11 — Add route

If `app/Modules/{Module}/Http/Routes/api.php` does not exist, create it with:
```php
<?php
Route::prefix('/{module-slug}')
    ->group(function () {
        // routes here
    });
```

Add the new route inside the prefix group:
```php
Route::get('/categories', [\App\Modules\{Module}\Http\Controllers\Api\{Module}Controller::class, '{method}'])
    ->name('api.{module}.{method}');
```

For auth-protected routes, wrap in `->middleware('auth:sanctum')`.

### Step 12 — Add translations

Open `lang/en/dictionary.php`.
Find or add `'{module}' => [...]` key.

**Only add translations for:**
1. Fields used in `FormRequest::attributes()` — human-readable labels shown in validation error messages (e.g. `name`, `email`, `price`, `category_id`)
2. Success/error messages returned by the controller

**Do NOT add translations for:**
- Fields in Resource output that the user never submits (id, slug, image, meta_title, etc.)
- Standard pagination/technical params the user doesn't interact with directly (page, per_page, sort, order)
- Any field not present in a FormRequest `rules()`

Example — request has `name` and `category_id` fields:
```php
'shop' => [
    'name'        => 'Name',
    'category_id' => 'Category',
],
```

### Step 13 — Summary

Print:
- Files created (with paths)
- Files modified (with paths)
- Route: `GET /api/{module-slug}/...` → `{Module}Controller@{method}`
- Next step suggestion (e.g., run tests, check Postman)

---

## Naming conventions

| Thing | Convention | Example |
|---|---|---|
| Controller | `{Module}Controller` | `ShopController` |
| Action | `{Verb}{Entity}Action` | `GetCategoriesAction`, `ShowCategoryAction` |
| Request | `{Module}{MethodName}Request` | `ShopIndexCategoriesRequest` |
| Resource | `{Entity}Resource` | `CategoryResource`, `ProductResource` |
| Route name | `api.{module}.{method}` | `api.shop.categories` |
| Translation key | `{module}.{key}` | `shop.category` |

## DTO usage

Create a DTO only for **write operations** (store, update) with multiple fields.
For read operations (index, show) skip DTO — pass params directly to Action.

DTO template:
```php
<?php

namespace App\Modules\{Module}\Dtos;

use Spatie\LaravelData\Data;

class {ActionName}Data extends Data
{
    public function __construct(
        public string $field,
    ) {}
}
```

## Reuse checklist

Before creating a new Action, check if `ShopRepository` (or module repository) already has the method.
If it does: Action just calls `$this->repository->method(...)` and returns result.
If repository doesn't have the method: add it to the repository, then call from Action.
