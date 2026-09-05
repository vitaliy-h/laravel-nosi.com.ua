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

`app/Filament/Resources/` is grouped by **admin domain**, and only a domain that owns
several resources gets a folder of its own:

* several resources → `Resources/{Domain}/{ModelNamePlural}/{ModelName}Resource.php`
  (`Shop/Products/ProductResource.php`, `Geo/GeoCountries/GeoCountryResource.php`)
* a single resource → `Resources/{ModelNamePlural}/{ModelName}Resource.php`
  (`Users/UserResource.php`, `Subscribers/SubscriberResource.php`)
* a name that is already plural → `Resources/{ModelName}/{ModelName}Resource.php`
  (`News/NewsResource.php`, `Feedback/FeedbackResource.php`)

The domain is not the module: `ShopCart` and `ShopOrder` models are administered under
`Shop/` (`Shop/Cart/CartResource.php` — singular, not pluralised). Check the neighbouring
folders before generating, and move what the generator produced if it guessed differently.

See `.claude/skills/make-filament-resource/SKILL.md` for the full generation flow
(including navigation wiring and translations), and
[Shared building blocks](README.md#shared-building-blocks) for `HasDefaultTable`,
`BaseEditAction` and the other pieces a generated resource is expected to use.

### Flags explanation:

* `--simple` → no separate pages (modal-based CRUD)
* `--generate` → auto-generate form from database
* `--no-interaction` → no questions when creating a resource

---

[Back to main readme](../../README.md)
