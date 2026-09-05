---
name: make-model
description: >
  Interactive generator for Laravel Eloquent models with migrations, placed inside the correct
  app/Modules/{Module}/Models/ directory. Handles new module creation (folder, optional
  PageModule case, optional config/modules.php switch).
  Use when the user says "create model", "make model", "нова модель", "створи модель", or invokes /make-model.
---

## Flow

### Step 1 — Model name

Ask: **Model name?** (PascalCase, e.g. `Product`)

### Step 2 — Module

List the directories in `app/Modules/` — that is the full set of modules
(`PageModule` holds only the ones that own a public CMS page, so it is **not** the
module list). Present them as a numbered list plus a **"+ New module"** option.

If user picks an **existing module** → proceed to Step 3.

If user picks **New module**:
- Ask: module display name (e.g. `Blog`)
- Normalise it into `{ModuleName}` — the module directory name:
  - **Singular, PascalCase**, naming the module's aggregate root: `ShopOrder`, not
    `ShopOrders`; `Subscriber`, not `Subscribers`. A module is one entity, and the folder
    must read as that entity — the same word as the Eloquent model it holds. (`News` is
    fine as-is: uncountable, and its model is `News` too.)
  - If the display name the user gave is plural, singularise it and say so before
    creating anything.
  - DB table names are unaffected and stay plural (`shop_orders`, `subscribers`) — this
    rule covers the folder and namespace only.
- Create directory: `app/Modules/{ModuleName}/Models/`
- The module's key everywhere else is `Str::snake({ModuleName})` (`blog`, `shop_order`):
  `ModuleServiceProvider` and `ModuleGate` derive the gate key, route prefix and seeder
  class from the folder name that way.

Then ask the two questions below. They are independent — a module can be either, both,
or neither.

#### Step 2a-1 — Does this module own a public CMS page?

Ask: **Will this module have its own page in the CMS (a `Page` row an editor can
rename, whose slug becomes the module's URL prefix)?**

**Yes** → add a `PageModule` case. Edit `app/Modules/Page/Enums/PageModule.php`:
- Add `case {CASE} = '{value}';` to the cases block (`{CASE}` = UPPER_SNAKE of
  `{ModuleName}`, `{value}` = `Str::snake({ModuleName})` — the match matters, the enum
  value is what `ModuleGate` and the route prefix resolution use)
- Add `self::{CASE} => '{Label}',` to the `label()` match block

**No** → do not touch the enum. Most modules are in this group: `ShopOrder`,
`Subscriber`, `Sitemap`, `Translation`, `Notification`, `User`, `Core`, `_debug` have no
case. Their `Http/Routes/web.php` (if any) is mounted under a prefix derived from the
folder name — `Str::kebab({ModuleName})` — or under an explicit entry in
`ModuleServiceProvider::MODULE_PREFIX_OVERRIDES` (that is how `ShopOrder` becomes
`/orders`). Add such an override only when the folder name would give a bad URL, and say
so in the summary.

⚠️ Do not add a case "just in case": a case with no `Page` row makes the module fall back
to `Str::lower($case->value)` as its prefix and shows up in admin page-module pickers.

#### Step 2a-2 — Should this module be switchable off?

Ask: **Should this module be removable from a project via `config/modules.php`?**

**Yes** → add a commented line under the `list` key in `config/modules.php`, keeping the
existing alignment:
```php
'list' => [
    // 'blog'            => false,
],
```
If it only makes sense together with another module, also add it to `dependencies`
(`'blog' => 'news'` means `news => false` takes `blog` down too), and remember that a
dependant does not need its own line — `shop_order` and `profile` exist only there.

**No** → leave `config/modules.php` alone; the module is then core and always loaded
(like `Page`, `Core`, `Translation`, `Notification`).

Whichever answer: `ModuleGate::isEnabled('{value}')` returns `true` for a module with no
line at all, so nothing else has to change.

### Step 2b — Cascade delete relations

Ask: **Does this model have child relations that should be deleted (and logged) when this model is deleted? List relation method names, or press Enter to skip.**

Example input: `items payments shipments`

- If user provides names → store as `$cascadeRelations` list, use in Step 7
- If user skips → `$cascadeRelations` is empty, skip trait

Note: these are Eloquent relation method names that must be defined on the model (Step 7 will add stubs if needed).

### Step 3 — Migration fields

Ask the user to list fields in free form, e.g.:
```
title string
slug string
display boolean default:false
text text nullable
published_at timestamp nullable
```

Accepted types (map to Blueprint methods):
- `string` → `string`
- `text` → `text`
- `longtext` → `longText`
- `integer` / `int` → `integer`
- `biginteger` / `bigint` → `bigInteger`
- `boolean` / `bool` → `boolean`
- `decimal` → `decimal(total, places)` — ask precision if not given
- `float` → `float`
- `json` → `json`
- `timestamp` → `timestamp`
- `date` → `date`
- `enum` → **DO NOT use `enum` column type in DB.** See Enum handling below.

**Enum field handling:**
When user specifies a field type of `enum`:
1. Ask: enum values (e.g. `active,inactive,banned`)
2. Ask: backing type — `string` (default) or `int`
3. Ask: PHP Enum class name (default: PascalCase of field name, e.g. `status` → `StatusEnum`)
4. In migration: use `string` (for string-backed) or `integer` (for int-backed) column type instead of `enum`
5. Create PHP enum class at `app/Modules/{ModuleName}/Enums/{EnumClassName}.php` — see Step 3a below
6. In model `$casts`: cast the field to the Enum class

Modifiers parsed from the field definition:
- `nullable` → `->nullable()`
- `default:value` → `->default(value)` (string values quoted, boolean/numeric unquoted)
- `unique` → `->unique()`

**Auto-nullable rule:**
Apply `->nullable()` automatically to any field that:
- has no `default:` value specified, AND
- is NOT one of the exempt fields: `rank`, `display`, `slug`, `created_at`, `updated_at`

Exempt fields never get auto-nullable (they always have a meaningful default or are handled by Laravel). If user explicitly writes `nullable` on an exempt field, still apply it. User-specified `default:` on any field suppresses auto-nullable.

**Image field handling:**
When user mentions image(s) for a model (single image, multiple images, gallery, etc.):
- Single image → field name `img`, DB type `string`, nullable
- Multiple images → field name `imgs`, DB type `json`, cast to `'array'`
- Both can coexist on same model
- Always add `HasImage` trait from `App\Modules\Core\Traits\Models\HasImage`
- In model: set properties based on which image fields are present:
  - only `img` → no overrides needed (trait defaults: `$imgFields = ['img']`, `$imgArrayFields = []`)
  - only `imgs` → add `protected array $imgFields = [];` and `protected array $imgArrayFields = ['imgs'];`
  - both → no overrides needed for `$imgFields`; add only `protected array $imgArrayFields = ['imgs'];`

### Step 3a — Create Enum classes (if any enum fields)

For each enum field, create `app/Modules/{ModuleName}/Enums/{EnumClassName}.php`:

Always import and use `HasEnumHelpers` trait — it provides the `options()` static method used by Filament selects and filters.

**String-backed enum:**
```php
<?php

namespace App\Modules\{ModuleName}\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum {EnumClassName}: string
{
    use HasEnumHelpers;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE   => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }
}
```

**Int-backed enum:**
```php
<?php

namespace App\Modules\{ModuleName}\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum {EnumClassName}: int
{
    use HasEnumHelpers;

    case ACTIVE = 1;
    case INACTIVE = 0;

    public function label(): string
    {
        return match($this) {
            self::ACTIVE   => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }
}
```

Rules:
- Case names: PascalCase of each value (e.g. `active` → `Active`, `out_of_stock` → `OutOfStock`)
- String-backed: case value = original string from user
- Int-backed: assign sequential integers starting from 1 (or ask user if order matters)
- `label()` returns human-readable string — derive from case name with spaces (e.g. `OutOfStock` → `'Out of stock'`)
- Create `Enums/` directory inside module if it doesn't exist

### Step 4 — Field ordering

Sort migration fields in this order (short → long):
1. `boolean`, `tinyInteger`, `smallInteger`, `integer`, `bigInteger`, `unsignedBigInteger`
2. `string`, `char`, `decimal`, `float`, `date`, `time`, `timestamp`
3. `text`, `mediumText`, `longText`, `json`, `jsonb`, `binary`

Within the same group, preserve user's order.
`$table->id()` and `$table->timestamps()` are always first/last — never reorder them.

### Step 5 — Generate via artisan

**Before running any artisan/composer command**, detect the correct runner:

```bash
# Check for Laravel Sail first
if [ -f ./vendor/bin/sail ]; then
    ARTISAN="./vendor/bin/sail artisan"
    COMPOSER="./vendor/bin/sail composer"
else
    # Sail not present — find a compatible PHP
    ARTISAN="php artisan"
    COMPOSER="composer"
fi
```

If Sail is present → use `./vendor/bin/sail artisan` for all artisan calls and `./vendor/bin/sail composer` for composer.
If Sail is absent → check that `php --version` satisfies the project's PHP requirement (`composer.json` → `require.php`). If the system `php` doesn't match, look for versioned binaries (`php8.4`, `php8.3`, …) or report that no suitable PHP was found and create files manually.

Run:
```bash
# with Sail:
./vendor/bin/sail artisan make:model {ModelName} -m

# without Sail (compatible php):
php artisan make:model {ModelName} -m
```

This creates:
- `app/Models/{ModelName}.php` — move to `app/Modules/{ModuleName}/Models/{ModelName}.php`
- `database/migrations/{timestamp}_create_{table}_table.php` — move to `app/Modules/{ModuleName}/database/migrations/{timestamp}_create_{table}_table.php` (lowercase `database`; `ModuleServiceProvider::loadModuleMigrations()` only loads migrations from this per-module path, not from the root `database/migrations/`)

After moving the model file, update its namespace to `App\Modules\{ModuleName}\Models`.

### Step 6 — Fill migration

Open the generated migration file and replace the `up()` body with the sorted fields from Step 4.

### Step 7 — Fill model

Set in the model file:
- `namespace App\Modules\{ModuleName}\Models;`
- `protected $fillable = [...]` — all fields except `id`, `timestamps`
- `protected $casts = [...]` — booleans → `'boolean'`, timestamps/dates → `'datetime'`/`'date'`, json → `'array'`, enum fields → `{EnumClassName}::class` (with use import at top)
- If fields contain `display`: add `use HasVisibility;` inside the class body and `use App\Modules\Core\Traits\Models\HasVisibility;` to the top-level imports
- If fields contain `img` or `imgs` (or both): add `use HasImage;` inside the class body and `use App\Modules\Core\Traits\Models\HasImage;` to the top-level imports; add `$imgFields` / `$imgArrayFields` overrides per image-field rules in Step 3
- If fields contain any of `slug`, `meta_title`, `meta_description`: add `use HasSeoFields;` inside the class body and `use App\Modules\Core\Traits\Models\HasSeoFields;` to the top-level imports
- If `$cascadeRelations` is not empty: add `use CascadesDeletes;` inside the class body and `use App\Modules\Core\Traits\Models\CascadesDeletes;` to the top-level imports; add `protected array $cascadeDeletes = ['relation1', 'relation2'];` with the provided relation names

### Step 8 — Seeder (optional)

Ask: **Add a demo data seeder for this model?**

If yes:
- Check whether `app/Modules/{ModuleName}/database/seeders/{ModuleName}Seeder.php`
  already exists (it's the module's single entry-point seeder, called
  automatically by `database/seeders/DatabaseSeeder.php` — see `docs/starter/modules.md` §6).
- Create a dedicated sub-seeder `app/Modules/{ModuleName}/database/seeders/{ModelName}Seeder.php`
  (namespace `App\Modules\{ModuleName}\database\seeders`, lowercase
  `database\seeders`) with sample rows for this model.
- If `{ModuleName}Seeder` already exists, add `$this->call({ModelName}Seeder::class);`
  to its `run()`, in FK-safe order relative to existing calls.
- If `{ModuleName}Seeder` doesn't exist yet, create it and have it call
  `{ModelName}Seeder`. Nothing else needs wiring — `DatabaseSeeder` picks up
  any `{ModuleName}Seeder` class automatically, and it's skipped when the
  module is disabled via `config/modules.php`.

If no: skip this step entirely, don't create empty seeder files.

### Step 9 — Summary

Print a short summary:
- Model path
- Migration file name
- Enum classes created (if any), with their paths
- Fields added
- Seeder created (if any), with its path
- If a new module was created: its directory name (singular PascalCase), its
  `Str::snake()` key, whether a `PageModule` case was added, whether a
  `config/modules.php` line (and dependency) was added, and the route prefix it will get
- Next suggested step: `php artisan migrate` (or `./vendor/bin/sail artisan migrate` if Sail is used)

---

## When the model reaches a page

This skill stops at the model. As soon as the model is rendered by a blade
template, it does not go to the view itself: a controller hands blade a
**ViewModel** — fed the Eloquent model, exposing fields already prepared for
rendering (URLs, formatted amounts and dates, derived flags).

ViewModels live in `app/Modules/{Module}/ViewModels/` on top of
`App\Modules\Core\Abstract\BaseViewModel`. See `docs/starter/view-models.md`.
