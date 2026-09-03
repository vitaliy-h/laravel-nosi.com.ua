# Architecture

Project uses a lightweight modular structure.

---

# Modules

All site modules are stored in:

```text
app/Modules
```

Example:

```text
app/Modules/
├── Core/
├── Page/
├── User/
├── Index/
├── News/
├── Feedback/
└── PageText/
```

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

* `Http/Controllers`, `Http/Routes`
* Models
* Actions — what they may return is fixed, see `docs/starter/actions.md`
* Dtos
* Repositories
* Enums
* Policies
* `Console/Commands`
* `database/migrations`, `database/seeders`

Structure can grow depending on module complexity.

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

Routes remain in standard Laravel folders for POST and API methods:

```text
routes/web.php
routes/api.php
```

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
