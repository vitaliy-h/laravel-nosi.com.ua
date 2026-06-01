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

Example:

```text
app/Modules/News/
├── Controllers/
├── Models/
├── Services/
└── DTO/
```

Typical contents:

* Controllers
* Models
* Services
* DTO
* Repositories
* Enums
* Policies

Structure can grow depending on module complexity.

---

# Routing

Routes remain in standard Laravel folders for POST and API methods:

```text
routes/web.php
routes/api.php
```

For GET request used a catch-all `fallback` route (`Core/routes/web.php`) dispatches unmatched URLs to
`ModuleRouteController`, which resolves a `Page` by slug, reads its `module` enum, and invokes
`App\Modules\{Module}\Http\Controllers\Web\IndexController` (contract: `ModuleIndexControllerInterface` →
`accessCheck()` then `index()`). This acts as a lightweight CMS layer.

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

[Back to main readme](./README.md)
