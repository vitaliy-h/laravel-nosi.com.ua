# Disabling Modules

## Overview

Some modules under `app/Modules` can be turned off entirely from
`config/modules.php`. Disabling a module stops it from being wired into the
app at every level:

- routes (`app/Modules/{Module}/Http/Routes/{web,api}.php`)
- console commands (`app/Modules/{Module}/Console/Commands`)
- migrations (`app/Modules/{Module}/database/migrations`)
- database seeders (`database/seeders/DatabaseSeeder.php`)
- admin panel navigation and resources (`app/Providers/Filament/AdminPanelProvider.php`)
- the module's `PageModule` case, so it disappears from the page-type select
  in `PageResource` and its CMS page is skipped by `PagesTableSeeder`
- its own and dependent test suites (`tests/Feature/{Module}`, `tests/Unit/{Module}`)

All of this is driven by one class: `App\Modules\Core\Support\ModuleGate`.

---

## 1. Two kinds of modules

**Toggleable** — has a key in `config/modules.php`, or is listed under
`modules.dependencies` as depending on one:

```php
// config/modules.php
return [
    'geo' => true,
    'shop' => true,
    'news' => true,
    'auth' => true,
    'feedback' => true,
    'sms' => true,

    'dependencies' => [
        'shop_cart' => 'shop',      // no cart without the shop
        'shop_orders' => 'shop',    // no orders without the shop
        'profile' => 'auth',        // no profile without auth
    ],
];
```

A dependent module (`shop_cart`, `shop_orders`, `profile`) has no key of its
own — it's enabled only when its parent module is enabled. `shop => false`
also disables `shop_cart` and `shop_orders` automatically.

**Core** — every other folder under `app/Modules` (`Core`, `Index`, `TextPage`,
`Sitemap`, `Translation`, `Notification`, `_debug`, ...). No key here, no
dependency — always enabled, cannot be turned off.

The key/folder name is the `Str::snake()`'d module folder name and matches
the `PageModule` enum's string value (`shop_cart`, `geo`, `news`, ...).

---

## 2. Turning a module off

Flip its key in `config/modules.php`:

```php
'news' => false,
```

That's it — `ModuleGate::isEnabled('news')` now returns `false` everywhere,
and everything listed under Overview reacts to it automatically. No other
file needs editing to take a module offline.

Check `App\Modules\Core\Support\ModuleGate::isEnabled(string $module): bool`
and `ModuleGate::isToggleable(string $module): bool` if you need the same
check in new code (a custom command, a view, a test).

---

## 3. What disabling a module does NOT do

- **It does not delete data.** Existing rows in the module's tables are left
  untouched; only the migrations that would create/alter them stop loading.
- **It does not clean up references from other modules.** If another
  module's view, page content block, or query reaches into a disabled
  module's tables, it will error (missing table) once that module's
  migrations stop running. The known case in this project is `shop`: its
  product teasers/featured blocks can be placed in any page's CMS content by
  the `Core` `Page` builder, and those blocks don't know the module is off.
  Remove or replace that content manually before disabling `shop`. `auth` and
  its dependents (`profile`, `shop_cart`/`shop_orders` are shop-only, not
  auth) are self-contained and safe to flip without extra cleanup.
- **It is not a substitute for hiding a single page.** Model `Page` has its
  own `display` flag that hides one page from menus/routing without
  touching code loading. Use that for "this one page shouldn't show up
  right now." Use `config/modules.php` for "this whole feature (and its
  code) shouldn't exist in this project."

---

## 4. Deleting a disabled module's code

The module system is built so that, once a module is disabled, its
`app/Modules/{Module}` folder (and any `app/Filament/Resources/{Module}`
folder, and `tests/Feature/{Module}` / `tests/Unit/{Module}`) can be deleted
without breaking the app — nothing outside those folders should still
reference the module once its config key is `false` (see the shop content
caveat above). This is the intended way to strip an unused module out of a
project permanently, once you've confirmed the project genuinely never
enables it.

---

## 5. Disable modules at project start, not mid-project

**Decide which modules a new project needs at the very beginning**, right
after cloning the starter, before building on top of it. Toggling a module
off later, after real features/content/data have grown around it, is exactly
when the "does NOT do" caveats above (dangling content blocks, stale data)
bite hardest — this is a starter kit; module selection is meant to be a
day-one decision, not an ongoing feature flag.

If a project will never need `geo`, `feedback`, or `sms`, turn them off (or
delete their code, per §4) before the first real feature is built, not
after.

---

## 6. Demo data seeders per module

Modules that ship demo data expose one entry-point seeder named after the
module folder: `App\Modules\{Module}\database\seeders\{Module}Seeder`. That
class is the only one `DatabaseSeeder` calls directly — it in turn calls the
module's own sub-seeders in whatever order they need (FK order, parent
before child, etc).

Example — `app/Modules/Shop/database/seeders/ShopSeeder.php` calls
`ShopCategorySeeder` → `ShopBrandSeeder` → `ShopProductSeeder` in that order
(wrapped in `SET FOREIGN_KEY_CHECKS=0/1` since products truncate/reseed).
`app/Modules/ShopOrders/database/seeders/ShopOrdersSeeder.php` calls
`ShopOrderSeeder`, which depends on Shop's products already existing.

`DatabaseSeeder::callModuleSeeders()` does the wiring automatically — no
per-module `if` blocks to maintain:

```php
foreach (File::directories(app_path('Modules')) as $module) {
    $moduleName = basename($module);

    if (!ModuleGate::isEnabled(Str::snake($moduleName))) {
        continue; // module disabled — skip its seeder entirely
    }

    $seederClass = "App\\Modules\\{$moduleName}\\database\\seeders\\{$moduleName}Seeder";

    if (class_exists($seederClass)) {
        $this->call($seederClass);
    }
}
```

**Adding demo data to a module:**

1. Write your sub-seeder(s) as usual in `app/Modules/{Module}/database/seeders/`.
2. Create (or extend) `app/Modules/{Module}/database/seeders/{Module}Seeder.php`
   — namespace `App\Modules\{Module}\database\seeders` (lowercase
   `database\seeders`, matching the folder) — and call the sub-seeders from
   its `run()` in the required order.
3. Do nothing else. `DatabaseSeeder` picks it up on the next
   `php artisan db:seed` / `migrate:fresh --seed` automatically, and it's
   already skipped when the module is disabled.

Cross-module ordering (e.g. Shop's products must exist before ShopOrders
seeds orders) falls out of `File::directories()` returning folders
alphabetically — `Shop` sorts before `ShopOrders`. If a future module needs
an order that alphabetical sorting doesn't give you, that's a sign the
dependent seeder should instead call the parent's sub-seeder methods
directly rather than relying on loop order.

Core seeders that aren't tied to one toggleable module (`RolesAndPermissionsSeeder`,
`PagesTableSeeder`, `LanguageSeeder`, `LetterTemplateSeeder`, `SmsTemplateSeeder`)
stay as direct calls at the top of `DatabaseSeeder::run()` — they don't go
through the per-module loop.

---

[Back to main readme](../../README.md)
