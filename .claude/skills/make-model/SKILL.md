---
name: make-model
description: >
  Interactive generator for Laravel Eloquent models with migrations, placed inside the correct
  app/Modules/{Module}/Models/ directory. Handles new module creation (PageModule enum + folder).
  Use when the user says "create model", "make model", "нова модель", "створи модель", or invokes /make-model.
---

## Flow

### Step 1 — Model name

Ask: **Model name?** (PascalCase, e.g. `Product`)

### Step 2 — Module

Read `app/Modules/Core/Enums/PageModule.php` to list existing modules.
Present them as a numbered list plus a **"+ New module"** option.

If user picks an **existing module** → proceed to Step 3.

If user picks **New module**:
- Ask: module display name (e.g. `Blog`)
- Derive automatically:
  - Enum case: `BLOG` (UPPER_SNAKE of display name)
  - Enum value: `blog` (snake_case of display name)
  - Label: display name as-is
- Edit `app/Modules/Core/Enums/PageModule.php`:
  - Add `case {CASE} = '{value}';` to the cases block
  - Add `self::{CASE} => '{Label}',` to the `label()` match block
- Create directory: `app/Modules/{ModuleName}/Models/`

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

**String-backed enum:**
```php
<?php

namespace App\Modules\{ModuleName}\Enums;

enum {EnumClassName}: string
{
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

enum {EnumClassName}: int
{
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

Run:
```bash
php artisan make:model {ModelName} -m
```

This creates:
- `app/Models/{ModelName}.php` — move to `app/Modules/{ModuleName}/Models/{ModelName}.php`
- `database/migrations/{timestamp}_create_{table}_table.php`

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

### Step 8 — Summary

Print a short summary:
- Model path
- Migration file name
- Enum classes created (if any), with their paths
- Fields added
- Next suggested step: `php artisan migrate`
