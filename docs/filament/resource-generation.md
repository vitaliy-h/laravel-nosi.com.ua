# Filament Resource Generation

This document describes the **default format and generation flow** for admin modules (Filament Resources).

---

# 🧩 Default Module Format

All generated resources MUST follow these rules:

* No breadcrumbs
* No H1 page titles
* Table-first layout
* Create button inside table (top section)
* Row actions (positioned via `RecordActionsPosition::BeforeColumns`, i.e. left of the columns):

    * Edit → left
    * Delete → left
    * View → not used as a Filament "view record" page (resources are `--simple`, no ViewRecord
      page exists); some resources repurpose `ViewAction` as an external "preview on site" link
      (e.g. `CategoryResource`, `ProductResource`, `NewsResource` open the public page URL via
      `save_route(...)` in a new tab) — this is not a record detail page
* Editing via modal (no separate pages)

---

## 🪟 Edit Modal

All create/edit actions use modal forms with:

* **Save** (save without closing)
* **Save & Close** (save and close modal)

---

# ⚙️ Generation Workflow

Models are **not** created at the project root — this project is modular, and models live
under `app/Modules/{Module}/Models/`. Use the `/make-model` skill (or `php artisan make:model`
with an explicit path) rather than the bare `make:model` example below if you need a new model.

## Step 1 — Create Model + Migration

```bash
php artisan make:model App\\Modules\\Shop\\Models\\Product -m
```

(migration is created in the module's own `database/migrations/` directory via the model's
`--path`/module conventions — see `/make-model`)

---

## Step 2 — Run Migration

```bash
php artisan migrate
```

---

## Step 3 — Generate Filament Resource

```bash
php artisan make:filament-resource Shop/Product --simple --generate --no-interaction
```

Resources are namespaced per module, e.g. `App\Filament\Resources\Shop\Products\ProductResource`,
under `app/Filament/Resources/{Module}/{ModelName}s/`. See `.claude/skills/make-filament-resource/SKILL.md`
for the full generation flow (including navigation wiring and translations).

### Flags explanation:

* `--simple` → no separate pages (modal-based CRUD)
* `--generate` → auto-generate form from database
* `--no-interaction` → no questions when creating a resource

---

[Back to main readme](../README.md)
