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

## 1. Everything is enabled by default

A module is enabled unless `config/modules.php` explicitly says otherwise:
no key in the config means "on". The shipped config therefore lists only the
modules that may be switched off, every line commented out:

```php
// config/modules.php
return [
    'list' => [
        // 'user_auth'            => false,
        // 'feedback'             => false,
        // 'geo'                  => false,
        // 'news'                 => false,
        // 'shop'                 => false,
        // 'shop_cart'            => false,
        // 'shop_brands'          => false,
        // 'shop_promocodes'      => false,
        // 'shop_product_reviews' => false,
        // 'subscriber'           => false,
        // 'sms'                  => false,
        // 'letter_templates'     => false,
    ],

    'dependencies' => [
        'shop_cart'            => 'shop',      // no cart without the shop
        'shop_brands'          => 'shop',
        'shop_product_reviews' => 'shop',
        'shop_order'           => 'shop_cart', // no orders without the cart
        'shop_promocodes'      => 'shop_cart',
        'profile'              => 'user_auth', // no profile without auth
    ],
];
```

A module listed under `dependencies` goes off together with its parent,
whatever its own line says: `list.shop => false` also disables `shop_cart` and
`shop_brands`, and through the cart the orders and promocodes. `shop_order`
and `profile` have no line of their own at all — they exist only as
dependants, so the only way to switch them off is to switch off the cart or
`user_auth`.

Not every key matches an `app/Modules` folder: `shop_brands`,
`shop_product_reviews`, `shop_promocodes`, `sms` and `letter_templates` are
feature toggles for code living inside Shop / ShopCart / Notification —
switching one off hides its admin surfaces (navigation item, resource,
dashboard card), the tables stay.

Core CMS modules (`Core`, `Page`, `User`, `Index`, `PageText`, `Sitemap`,
`Translation`, `Notification`, `_debug`, ...) have no line here and cannot be
disabled — `ModuleGate::isEnabled()` simply always reports them as enabled.
`Page` (the CMS `pages` table and everything that renders it) and `User` (the
`users` table) are the two the whole app is built on, so they have no toggle by
design.

`_debug` is in that list too, even though it is the one module a production
site has no use for. It is deliberate: the module is a single controller behind
`auth` + `RoleMiddleware::using('super_admin')`
(`app/Modules/_debug/Http/Routes/web.php`), so it is unreachable for everyone
but a super admin, and its dev tooling is worth having on a live site when
something has to be inspected there. If a project wants it gone anyway, both
routes are open: add `'_debug' => false,` to the config, or delete
`app/Modules/_debug/` outright (see «4. Deleting a disabled module's code» below)
— nothing outside the folder references it.

The key/folder name is the `Str::snake()`'d module folder name and matches
the `PageModule` enum's string value (`shop_cart`, `user_auth`, `page_text`,
`geo`, `news`, ...). It is **not** the page's public URL: the `UserAuth`
module's page is served from `/auth`, `ShopCart`'s from `/cart` — that comes
from `pages.slug`, not from this key.

---

## 2. Turning a module off

Uncomment its line under `list` in `config/modules.php`:

```php
'list' => [
    'news' => false,
],
```

That's it — `ModuleGate::isEnabled('news')` now returns `false` everywhere,
and everything listed under Overview reacts to it automatically. No other
file needs editing to take a module offline.

Use `App\Modules\Core\Support\ModuleGate::isEnabled(string $module): bool`
if you need the same check in new code (a custom command, a view, a test);
in a test class, `TestCase::skipUnlessModules()` skips when a module the
test depends on is off.

---

## 3. What disabling a module does NOT do

- **It does not delete data.** Existing rows in the module's tables are left
  untouched; only the migrations that would create/alter them stop loading.
- **It does not clean up references from other modules.** If another
  module's view, page content block, or query reaches into a disabled
  module's tables, it will error (missing table) once that module's
  migrations stop running. The known case in this project is `shop`: its
  product teasers/featured blocks can be placed in any page's CMS content by
  the `Page` module's content builder, and those blocks don't know the module
  is off. Remove or replace that content manually before disabling `shop`.
  `user_auth` and its dependents (`profile`; `shop_cart`/`shop_order` are
  shop-only, not auth) are self-contained and safe to flip without extra
  cleanup.
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

Model observers are part of this contract: `ModuleServiceProvider` auto-registers
every `app/Modules/{Module}/Observers/{Model}Observer.php` against
`App\Modules\{Module}\Models\{Model}` for enabled modules only, so no file
outside the module names a module's observers. An observer with no matching
model class is skipped — a non-model event listener belongs in the module's
`Listeners/` folder instead (e.g. `Core\Listeners\LogListener`).

Two back-references on `App\Modules\User\Models\User` are the exception, and
have to be removed by hand when you delete the module they point at:
`User::socialProfiles()` → `UserAuth\Models\SocialProfile` and `User::cart()`
→ `ShopCart\Models\Cart`.

### 4.1 Do the deletion with Claude Code, not by hand

Deleting a module is a search-and-clean job across a dozen places, and the
files that break are exactly the ones a manual `rm -rf` forgets. Hand it to
Claude Code with a prompt like:

> Delete the `shop_brands` module completely: remove its code, admin
> resources, references, translations, tests and config entry, then run the
> test suite.

What has to be checked, and what the agent should work through:

| Place | What to remove |
|---|---|
| `app/Modules/{Module}/` | the module folder itself (models, actions, queries, observers, routes, commands, migrations) |
| `app/Filament/Resources/{Module}/`, `app/Filament/Widgets/{Module}/` | admin resources, widgets, pages of that module |
| `app/Providers/Filament/AdminPanelProvider.php` | its `NavigationItem`s, its entries in `->resources()` / `->widgets()`, **and the now-unused `use` imports at the top** |
| `config/modules.php` | the module's key (and any child keys it gates, see §2) |
| `app/Modules/User/Models/User.php` | the back-references listed above, if they point at this module |
| `resources/views/modules/{module}/` | the module's frontend views |
| `lang/{locale}/*.php` | translation keys used only by that module (`admin.tables.*`, its own file if it has one) |
| `database/seeders/`, `database/factories/` | seed rows and factories for its tables (e.g. the page row in `PagesTableSeeder`) |
| `tests/Feature/{Module}/`, `tests/Unit/{Module}/` | its tests |
| page content | CMS blocks that render the module (see the caveat in §3) |

Then: `grep -ri {module}` over `app/`, `config/`, `routes/`, `resources/`,
`lang/`, `database/` to catch leftovers, and `./vendor/bin/sail test` to prove
nothing else depended on it. Dropping the module's DB tables is a separate,
deliberate step — do it only after the code is gone and you have a backup.

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
`app/Modules/ShopOrder/database/seeders/ShopOrderSeeder.php` is the module's
only seeder and depends on Shop's products already existing.

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

Cross-module ordering (e.g. Shop's products must exist before ShopOrder
seeds orders) falls out of `File::directories()` returning folders
alphabetically — `Shop` sorts before `ShopOrder`. If a future module needs
an order that alphabetical sorting doesn't give you, that's a sign the
dependent seeder should instead call the parent's sub-seeder methods
directly rather than relying on loop order.

Core seeders that aren't tied to one toggleable module (`RolesAndPermissionsSeeder`,
`PagesTableSeeder`, `LanguageSeeder`, `LetterTemplateSeeder`, `SmsTemplateSeeder`)
stay as direct calls at the top of `DatabaseSeeder::run()` — they don't go
through the per-module loop.

---

## 7. The module manifest

Everything above — which modules are enabled, where each one's `web.php` mounts,
which migrations and observers it brings, which commands it registers — is
resolved by `App\Modules\Core\Support\ModuleManifest` and handed to
`ModuleServiceProvider`, which only registers what it is told.

Without a cached manifest that resolution happens on every boot: a directory
listing of `app/Modules`, four `File::exists()` per module, a walk of every
`Console/Commands` folder (even for an HTTP request that will never run one),
and a database read for each page-driven route prefix. A fresh clone depends on
this working before anything has been run, so it stays the default.

`php artisan modules:cache` writes the resolved array to
`bootstrap/cache/modules.php` (git-ignored, like the rest of that folder) and
boot then reads a plain PHP array — no scan, no database.

### Why the order in the deploy matters

A page-driven module's prefix **is** `pages.slug` (see
[routing.md](routing.md#2-prefix-resolution-moduleserviceproviderregisterwebroute)),
so it is data, and `route:cache` freezes it. The deploy therefore runs:

```bash
php artisan migrate        # page rows are current
php artisan modules:cache  # prefixes resolved from them
php artisan route:cache    # route table frozen with those prefixes
```

Caching routes before the manifest would bake in whatever prefixes the previous
manifest held — or, on a database with no page rows yet, the fallback prefixes
(`/user_auth` instead of `/auth`).

`composer install` runs `modules:clear` from `post-autoload-dump`, so a manifest
never survives a code change that added or removed a module.

### Tests always build their own

A manifest holds prefixes read from a database, so the one cached locally
describes the *development* database. A test run boots against the test database
and would otherwise mount every page-driven module behind the wrong URL — so
under PHPUnit the manifest file on disk is ignored and the array is built fresh
(`ModuleServiceProvider` passes `useCache: false`). Caching a manifest locally is
therefore safe: `sail test` keeps working.

### Editing a slug in the admin

`PageObserver` fires on every page create/update/delete and drops everything
derived from the page rows in one place: the cached rows (`GetAllPagesQuery::clear()`),
the manifest (rebuilt, but only if one was cached) and the cached route table.
The app then runs with uncached routes until the next deploy re-caches them —
correct URLs, one boot slower.

On a multi-server deploy that invalidation only reaches the node that served the
request, so every node needs its own `modules:cache` + `route:cache`.

---

[Back to main readme](../../README.md)
