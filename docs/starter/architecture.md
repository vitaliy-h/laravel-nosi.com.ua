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
├── Index/
├── News/
├── Feedback/
└── TextPage/
```

Module folder names correspond to site page/modules.

Examples:

* `Index` → homepage
* `News` → news module
* `Feedback` → feedback page
* `TextPage` → static text pages

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

Core is used by all other modules.

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
The one exception is `TextPage`, which owns the app's single `Route::fallback()`
(N pages, no fixed prefix). See `docs/starter/routing.md` for the full pattern.

---

# Blade Templates

Blade templates remain in standard Laravel location:

```text
resources/views
```

Example:

```text
resources/views/news
resources/views/feedback
```

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
