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

Routes remain in standard Laravel folders:

```text
routes/web.php
routes/api.php
```

No custom route loader is used.

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

# 📎 Related Documentation

* [General README](./README.md)
* [local Setup](./local_setup.md)
* [Architecture Overview](./architecture.md)
  * [images](images.md)
* [Filament Admin Panel](./filament.md)
    * [Resource Generation](filament/resource-generation.md)
    * [Translations Guide](filament/translations.md)
