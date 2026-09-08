# Routing Architecture (CMS Modules)

## Overview

Every module owns explicit, named Laravel routes. Pages are still stored in the
`pages` table and mapped to modules via the `PageModule` enum, but the DB only
supplies the **prefix** each module is mounted under (resolved once at boot) —
it no longer drives per-request dispatch.

---

## 1. Routing Strategy

Every module can define both GET and non-GET routes in one place:

```
app/Modules/{ModuleName}/Http/Routes/web.php
```

Routes inside this file are relative (`/`, `/{slug}`) — never `/module-name/...` —
because `ModuleServiceProvider` wraps the whole file in a prefix group.

```php
use App\Modules\News\Http\Controllers\Web\IndexController;

Route::get('/', [IndexController::class, 'index'])->name('news.index');
Route::get('/{slug}', [IndexController::class, 'show'])->name('news.show');
```

All module route files are **automatically loaded** if they exist at that path —
see `ModuleServiceProvider::registerRoutes()`, fed by `Core\Support\ModuleManifest`.

---

## 2. Prefix Resolution (`Core\Support\ModuleManifest`)

For every module folder that maps to a `PageModule` case, its `web.php` is
wrapped in:

```php
Route::middleware(['web', 'share.page:'.$pageModule->value])
    ->prefix($prefix)
    ->group($webRoute);
```

- `$prefix` is the module's `Page->slug` from the DB (resolved by `ModuleManifest`,
  once per boot or once per `modules:cache`),
  or `''` for `PageModule::INDEX` (mounted at the root). The slug is **not** the
  `PageModule` value: `user_auth` is served from `/auth`, `shop_cart` from
  `/cart`. With no page row in the DB yet the prefix falls back to the enum
  value itself — which is what tests hit, since they seed pages only after the
  app has booted (`/user_auth/login` in a test, `/auth/login` in a seeded app).
- `share.page` is `App\Modules\Page\Http\Middleware\ShareModulePageContext` —
  at **request time** it resolves the `Page` for that `PageModule` and shares
  `page`, `breadcrumbs`, `meta_title`, `meta_description`, `headerMenu`,
  `footerMenu`, `cartCount` to the view via `PageContextSharer::share()`.
  Migrated controllers never share these themselves.
- Modules that don't map to a `PageModule` get no page context, and only two of
  them get no prefix either:
  - `Core` and `Sitemap` mount at the app root
    (`ModuleManifest::ROOT_LEVEL_MODULES`), because `Core` owns
    `/storage/{preset}/{path}` image previews and `Sitemap` owns the SEO-standard
    `/sitemap*.xml` paths — a prefix would break both;
  - every other unmapped module is prefixed with `Str::kebab()` of its folder name:
    `Subscriber` → `/subscriber`, `_debug` → `/_debug`, or an explicit entry in
    `ModuleManifest::MODULE_PREFIX_OVERRIDES` — that is how `ShopOrder` becomes
    `/orders` rather than `/shop-order`.

  `Translation` has only `Http/Routes/api.php` and `Notification` has no `Http/`
  folder at all, so neither mounts a web route.

### Prefixes come from the DB, so `route:cache` freezes them

The prefix of every `PageModule` module is `pages.slug`, read from the database. That
read no longer happens on every boot: `ModuleManifest` resolves it, and
`php artisan modules:cache` freezes the answer in `bootstrap/cache/modules.php` — see
[modules.md §7](modules.md#7-the-module-manifest). `php artisan route:cache` then
freezes the route table built from it.

Because both are frozen, the order in the deploy is fixed:
`migrate` → `modules:cache` → `route:cache`. Caching routes first bakes in the previous
manifest's prefixes, or — against a database with no page rows yet — the fallback ones
(`/user_auth` instead of `/auth`).

Renaming a slug in the admin still works without a deploy: `PageObserver` drops the page
row cache, rebuilds the manifest if one is cached, and deletes the cached route table, so
the next request builds the routes fresh from the new slug.

What that leaves you with:

- after a slug edit the app runs **uncached** until someone runs
  `modules:cache` + `route:cache` again — the observer only invalidates, it does not
  rebuild the route cache;
- on a multi-server deploy the invalidation only reaches the node that served the
  request, so every node needs its own pair of commands.

**Two gotchas already hit — don't reintroduce them:**
- Never bind the shared page by DB id at boot time. The DB may not have that
  row yet when the ServiceProvider boots (fresh install, tests seeding after
  boot). Bind by the stable `PageModule::value` string instead, and resolve
  the `Page` inside the middleware at request time.
- `Route::middleware('web')->middleware('x')` **overwrites**, it does not merge
  (`RouteRegistrar::attribute()` replaces the `middleware` attribute on each
  call). Always pass one array: `Route::middleware(['web', 'x'])`.

---

## 3. PageText — the one exception

`page_text` has **N pages sharing one module**, each with its own independent
slug — there's no single prefix to resolve. It skips prefix/`share.page`
resolution and instead registers a `Route::fallback(...)`. Only one
`Route::fallback()` can ever be active app-wide, which is why `PageText` owns it.

---

## 4. Controller Pattern

Module web controllers extend `App\Modules\Core\Abstract\BaseWebController`
and define one real Laravel action method per route, with typed route
parameters:

A controller injects Queries, wraps what they return into ViewModels, and renders
through `$this->render()` — never the `view()` helper, and never an Eloquent model
handed straight to a blade (`app/Modules/News/Http/Controllers/Web/IndexController.php`):

```php
class IndexController extends BaseWebController
{
    public function __construct(
        private readonly GetNewsListQuery $getNewsList,
        private readonly FindNewsBySlugQuery $findNewsBySlug,
    ) {}

    public function index(Request $request)
    {
        $news = NewsViewModel::collect($this->getNewsList->handle());

        return $this->render('modules.news.index', compact('news'));
    }

    public function show(Request $request, string $slug)
    {
        $newsItem = NewsViewModel::from($this->findNewsBySlug->handle($slug));

        view()->share([
            'meta_title'       => $newsItem->meta_title,
            'meta_description' => $newsItem->meta_description,
        ]);

        return $this->render('modules.news.show', compact('newsItem'));
    }
}
```

Page-level meta comes from `share.page` for a module's own page; the `show()` above
shares its own only because a single news item's meta is per-record. See
`docs/starter/view-models.md`.

Access control (auth-only pages, guest-only pages) is real Laravel middleware
on the route/group (`->middleware('auth')`, `->middleware('guest')`), not a
controller method.

---

## 5. URL Generation

Use `save_route()` — same signature as Laravel's `route()`, but returns `'/'`
instead of throwing when the route isn't registered (e.g. module disabled):

```php
save_route(string $name, mixed $parameters = [], bool $absolute = true): string
```

Examples:

```php
save_route('news.show', $item->slug);
save_route('shop.product', [$category->slug, $product->slug_with_id]);
save_route('profile.orders.show', $order->id);
```

---

[Back to main readme](../../README.md)
