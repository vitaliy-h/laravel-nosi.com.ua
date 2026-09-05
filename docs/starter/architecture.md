# Architecture

Project uses a lightweight modular structure.

---

# Modules

All site modules are stored in:

```text
app/Modules
```

All 18 modules currently in the starter:

```text
app/Modules/
├── Core/           # shared abstractions, logging, images, helpers — never disabled
├── Page/           # CMS pages, content builder, menus
├── Index/          # home page
├── PageText/       # free-form text pages (owns the fallback route)
├── News/
├── Feedback/
├── Shop/           # catalog: products, categories, brands, reviews
├── ShopCart/       # cart + promocodes
├── ShopOrder/      # orders and payment state
├── Subscriber/
├── Geo/            # countries / regions / cities
├── User/           # the User model itself
├── UserAuth/       # login, registration, OAuth, impersonation
├── Profile/        # authenticated user's own account pages
├── Notification/   # letter and SMS templates, sending jobs
├── Translation/    # locales and dictionaries
├── Sitemap/        # sitemap*.xml
└── _debug/         # dev-only tooling
```

### What each module is

Modules with a page of their own in the CMS (a `PageModule` case, so an editor renames the
page and its URL prefix follows — see `docs/starter/routing.md`):

| Module | What it does |
|---|---|
| `Index` | The home page: `GET /` and the view models behind it |
| `PageText` | Free-form CMS pages. It owns the app's single `Route::fallback()`, because each of its N pages has its own slug |
| `News` | News list and item, with the CMS content builder |
| `Feedback` | The contact page (`GET /`) and its form handler (`POST /`), which stores a `Feedback` row and sends a letter |
| `Shop` | Catalog: products, categories, brands, reviews, filters |
| `ShopCart` | Cart and promocodes — `docs/starter/cart.md` |
| `Profile` | The signed-in user's own pages: profile, edit, orders list and one order, plus the update/delete/social-disconnect endpoints. Everything behind `auth` |
| `UserAuth` | Login, registration, password recovery, OAuth (`docs/starter/oauth.md`) and impersonation (`docs/starter/impersonation.md`) |
| `Geo` | Countries, regions, cities — `docs/starter/geo.md` |

Modules with no page of their own — they mount under a prefix derived from the folder name,
or nowhere at all:

| Module | What it does |
|---|---|
| `Core` | Shared abstractions used by every other module: base controllers and view models, `ActionResult`, `ModuleGate`, image handling, logging, helpers. Mounted at the app root for image previews |
| `Page` | The CMS itself: `pages` table, content builder, menus, breadcrumbs. Not switchable |
| `User` | The `User` model and its queries; auth behaviour lives in `UserAuth` |
| `ShopOrder` | Orders and payment state, mounted at `/orders` — the checkout target for `ShopCart` |
| `Subscriber` | One `POST /subscriber` endpoint behind `SubscribeAction`, which is idempotent: re-subscribing an existing email returns success with `alreadyExists`, never an error |
| `Notification` | Letter and SMS templates, their sending jobs and logs — `docs/starter/letters.md`, `docs/starter/sms.md` |
| `Translation` | Locales and database-backed dictionaries behind the `t()` helper — `docs/starter/translations.md` |
| `Sitemap` | `sitemap*.xml`, mounted at the app root — `docs/starter/sitemap.md` |
| `_debug` | Super-admin-only playground for the ABC-response protocol — `docs/starter/abc-response.md` |

Module folder names correspond to site page/modules.

Examples:

* `Index` → homepage
* `News` → news module
* `Feedback` → feedback page
* `PageText` → static text pages
* `Page` → the CMS `pages` table itself and everything that renders it
* `User` → the `users` table

---

# Core Module

Shared functionality is stored in:

```text
app/Modules/Core
```

Core contains:

* Traits
* Facades
* Helpers
* Shared services
* Base classes
* Common utilities

Core is used by all other modules. It holds nothing domain-specific: the CMS
page lives in the `Page` module, the user in `User`, authentication in
`UserAuth`.

---

# Module Structure

Each module contains only logic related to that module.

Example (illustrative — real modules use Laravel-style nesting; see `docs/starter/routing.md`):

```text
app/Modules/News/
├── Http/
│   ├── Controllers/
│   └── Routes/
├── Models/
├── Actions/
├── Dtos/
└── database/
    ├── migrations/
    └── seeders/
```

Typical contents:

* `Http/` — `Controllers/{Web,Api}`, `Routes/{web,api}.php`, `Requests`, `Resources`
* `Models`
* `Actions` — write logic; what they may return is fixed, see `docs/starter/actions.md`
* `Queries` — reads, one class per read (`Find*`/`Get*`/`Search*`Query)
* `ViewModels` — what a controller hands a blade, see `docs/starter/view-models.md`
* `Dtos`
* `Enums`
* `Observers` — auto-registered per module by `ModuleServiceProvider`
* `Services` — only where neither an Action nor a Query fits (`CartService`, `GeoImporter`)
* `Console/Commands`
* `database/migrations`, `database/seeders`

No module has a `Repositories/` or a `Policies/` folder: reads go through `Queries`, and
the app's single policy is `app/Policies/UserPolicy.php`.

No module has all of these. `Sitemap` and `_debug` are just `Http/`; `User` is
`Models` + `Queries` + `database`; `Shop` and `ShopCart` are the widest.
Structure grows with module complexity.

---

# Actions

Business logic lives in Actions, shared between web and API controllers. An
Action returns exactly one of three things — `void`, a domain value, or
`App\Modules\Core\Dtos\ActionResult<T>` for an expected business failure the
caller must branch on. See `docs/starter/actions.md`.

---

# Disabling Modules

Some modules can be turned off entirely via `config/modules.php`, gated by
`App\Modules\Core\Support\ModuleGate`. See `docs/starter/modules.md` for how it
works and when to use it.

---

# Routing

Routes are declared per module, in `app/Modules/{Module}/Http/Routes/web.php` and
`api.php`. The root `routes/web.php` and `routes/api.php` stay empty.

Every module has its own explicit, named routes in `app/Modules/{Module}/Http/Routes/web.php`,
mounted under a DB-driven prefix resolved once at boot by `ModuleServiceProvider`.
The one exception is `PageText`, which owns the app's single `Route::fallback()`
(N pages, no fixed prefix). See `docs/starter/routing.md` for the full pattern.

---

# View Models

Data going from a controller to a view goes through a ViewModel: it is fed an
Eloquent model and exposes fields already prepared for rendering, so blade never
walks relations or formats values. They live in
`app/Modules/{Module}/ViewModels/`, on top of
`App\Modules\Core\Abstract\BaseViewModel`. See `docs/starter/view-models.md`.

---

# Blade Templates

Blade templates live in the standard Laravel location, mirroring the module
layout of `app/Modules`:

```text
resources/views/modules/{module}/
```

The folder name is the `PageModule` enum value, so `app/Modules/ShopCart`
renders from `resources/views/modules/shop_cart/`:

```text
resources/views/modules/news/index.blade.php
resources/views/modules/shop_cart/partials/promocode.blade.php
```

Shared templates sit outside `modules/`: `layouts/` (base layouts),
`components/` (anonymous `<x-...>` components) and `partials/` (global
`@include` templates). See `.claude/frontend.md` for the full convention.

---

# Principles

* Module-oriented structure
* Shared logic inside Core
* Laravel standard routing
* Laravel standard Blade structure
* Minimal abstraction
* Simple scalability

---

[Back to main readme](../../README.md)
