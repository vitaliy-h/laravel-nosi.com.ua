# Filament Resource Generation

This document describes the **default format and generation flow** for admin modules (Filament Resources).

---

# 🧩 Default Module Format

All generated resources MUST follow these rules:

* No breadcrumbs
* No H1 page titles
* Table-first layout
* Create button inside table (top section)
* Row actions:

    * Edit → left
    * Delete → left
    * View → not used
* Editing via modal (no separate pages)

---

## 🪟 Edit Modal

All create/edit actions use modal forms with:

* **Save** (save without closing)
* **Save & Close** (save and close modal)

---

# ⚙️ Generation Workflow

## Step 1 — Create Model + Migration

```bash
php artisan make:model Post -m
```

---

## Step 2 — Run Migration

```bash
php artisan migrate
```

---

## Step 3 — Generate Filament Resource

```bash
php artisan make:filament-resource Page --simple --generate --no-interaction
```

### Flags explanation:

* `--simple` → no separate pages (modal-based CRUD)
* `--generate` → auto-generate form from database
* `--no-interaction` → no questions when creating a resource

---

# 📎 Related Documentation

* [General README](../README.md)
* [Filament Admin Panel](../filament.md)
    * [Translations Guide](translations.md)
* [Project Architecture](../architecture.md)
    * [images](../images.md)
* [local Setup](../local_setup.md)

---
