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
see `ModuleServiceProvider::loadModuleRoutes()`.

---

## 2. Prefix Resolution (`ModuleServiceProvider::registerWebRoute()`)

For every module folder that maps to a `PageModule` case, its `web.php` is
wrapped in:

```php
Route::middleware(['web', 'share.page:'.$pageModule->value])
    ->prefix($prefix)
    ->group($webRoute);
```

- `$prefix` is the module's `Page->slug` from the DB (resolved once at boot),
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
- Modules that don't map to a `PageModule` (`Core`, `ShopOrder`, `Sitemap`,
  `_debug`, `Translation`, `Notification`) register with plain
  `Route::middleware('web')->group($webRoute)` — no prefix, no page context.

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

```php
class IndexController extends BaseWebController
{
    public function index(Request $request)
    {
        $news = $this->getNewsList->handle();

        return view('pages.news.index', compact('news'));
    }

    public function show(Request $request, string $slug)
    {
        $newsItem = $this->findNewsBySlug->handle($slug);

        view()->share([
            'meta_title' => $newsItem->meta_title,
            'meta_description' => $newsItem->meta_description,
        ]);

        return view('pages.news.show', compact('newsItem'));
    }
}
```

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
