---
name: make-api
description: >
  Interactive generator for Laravel API endpoints following project conventions:
  BaseApiController, Actions (shared with web), FormRequest with attributes(), JsonResource, api.php routes.
  Use when the user says "make api", "create api method", "api endpoint", "зроби апі", "апі метод", or invokes /make-api.
---

## Principles (from Profile module)

1. **Controller** extends `BaseApiController` → gets `$this->success()` / `$this->error()`
2. **Actions** hold write logic (create/update/delete, side effects), injectable into
   both web and API controllers
3. **Queries** (`app/Modules/{Module}/Queries/`) hold reads — this project has no
   repositories. One class per read, `handle()` returns models/collections/paginators.
   Controllers inject Queries directly; an Action is not created just to wrap a read
4. **DTOs** (`spatie/laravel-data`) carry validated data from request to action
5. **FormRequest** per method — always has `rules()` + `attributes()` (using `t('module.field')`)
6. **Resource** extends `JsonResource` — always used for output, never raw model/array.
   Resources are the **API's** output layer only; the web side has its own —
   a controller rendering a blade hands it a **ViewModel**
   (`app/Modules/{Module}/ViewModels/`, see `docs/starter/view-models.md`).
   Neither one is reused as the other: an API response and a rendered page
   need different fields.
7. **Routes** in `app/Modules/{Module}/Http/Routes/api.php` — auto-loaded by `ModuleServiceProvider` (wrapped in `Route::prefix('api')->middleware('api')->group(...)`)
8. **Translations** in `lang/en/dictionary.php` under `'shop' => [...]` key (key = module slug)

---

## Flow

### Step 1 — Module

If not specified by user, read `app/Modules/Page/Enums/PageModule.php` and list existing modules as numbered list.
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
- Does a Query for this read already exist in `app/Modules/{Module}/Queries/`? → reuse it
- Does the Action exist? → if web controller already does the same logic, extract it; if Action exists reuse it

### Step 7 — Reuse or create the read/write layer

**Reads (index, show, search, lists) → a Query class.** There are no repositories in
this project. Look in `app/Modules/{Module}/Queries/` first — 23 Query classes already
exist across 9 modules. Small modules keep them flat
(`News/Queries/GetNewsListQuery.php`); a module with many reads groups them per entity
(`Shop/Queries/Category/`, `Shop/Queries/Product/`, `Shop/Queries/ProductReview/`),
so drop the `{Entity}` namespace segment below when the module is flat.

Query template:
```php
<?php

namespace App\Modules\{Module}\Queries\{Entity};

use App\Modules\{Module}\Models\{Entity};

class Find{Entity}ByIdQuery
{
    public function handle(int $id): {Entity}
    {
        return {Entity}::visible()
            ->where('id', $id)
            ->firstOrFail();
    }
}
```

Full rules and layout: `docs/starter/queries.md`.

Rules for a Query:
- input is scalars or a DTO — **never** a `Request` (real example:
  `SearchCategoriesQuery::handle(CategorySearchData $criteria)`)
- one read per class; no writes, no side effects
- inject it straight into the controller — do not wrap a read in an Action

**Writes (store, update, delete, anything with side effects) → an Action.** If the web
controller for this module (`Web/IndexController.php` or similar) already contains the
same logic, extract it into `app/Modules/{Module}/Actions/{ActionName}Action.php` and
make the web controller call the Action too.

Action template:
```php
class {ActionName}Action
{
    public function __construct(
        private readonly Find{Entity}ByIdQuery $find{Entity}ById,
    ) {}

    public function handle({Dto} $data): {ReturnType}
    {
        // write logic here; reuse Queries for any reads it needs
    }
}
```

Web controller update: inject the Action/Query, replace inline logic with
`$this->{actionName}->handle(...)`.

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
use App\Modules\{Module}\Http\Requests\{Module}{MethodName}Request;
use App\Modules\{Module}\Http\Resources\{ResourceName}Resource;
use App\Modules\{Module}\Queries\{QueryName}Query;

class {Module}Controller extends BaseApiController
{
    // Reads inject Queries, writes inject Actions — see ShopController for a real one.
    public function __construct(
        private readonly {QueryName}Query ${queryName},
    ) {}

    public function {method}({RequestClass} $request): \Illuminate\Http\JsonResponse
    {
        $result = $this->{queryName}->handle(/* scalars or a DTO built from $request */);

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
- Next step suggestion (e.g., run tests, document the endpoint in `docs/api/{module}.md`)

---

## Naming conventions

| Thing | Convention | Example |
|---|---|---|
| Controller | `{Module}Controller` | `ShopController` |
| Action (writes) | `{Verb}{Entity}Action` | `CreateOrderAction`, `AddOrUpdateCartItemAction` |
| Query (reads) | `Find*Query` / `Get*Query` / `Search*Query` | `FindCategoryByIdQuery`, `SearchCategoriesQuery`, `GetReviewsByProductQuery` |
| Request | `{Module}{MethodName}Request` | `ShopCategoriesRequest`, `ShopProductsRequest` |
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

Before writing anything, list `app/Modules/{Module}/Queries/` and `app/Modules/{Module}/Actions/`.

- The read already exists as a Query → inject that Query into the controller, write nothing new.
- The read exists but returns the wrong shape → add a new Query beside it rather than
  adding a parameter that makes one class serve two callers.
- No Query yet → create one (see Step 7). Do **not** put the read in an Action, a
  Service, or the controller.
- The write already exists as an Action → reuse it; the API and the web controller are
  meant to call the same Action.

⚠️ There are no repository classes in this project. If a doc or an older example
mentions `ShopRepository` or `{Module}Repository`, it is stale — the read layer is
`Queries/`.
