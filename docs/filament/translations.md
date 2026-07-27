# Translations (Laravel Standard Approach)

This project uses the **Laravel native localization system** for the Filament admin panel.

No custom translation layer is used **in the admin panel**. The public-facing site is a
separate concern: it uses a database-backed translation layer with the `t()` helper —
see [Starter Translations](../starter/translations.md).

---

# 🌍 Localization Strategy

We use Laravel built-in localization system:

* `lang/{locale}/` (Laravel 11+ default location, not `resources/lang/`)
* helper: `__()`
* helper: `@lang()` (Blade)

No Filament-specific translation system is used.

---

# 📂 Language File Structure

```
lang/en/admin.php
```

Currently only the `en` locale is populated. The skill/generator conventions reference an
optional `lang/uk/admin.php` for projects that add a second locale — create it if/when the
project actually needs Ukrainian translations.

Structure Example:

```php
// lang/en/admin.php
return [
    'tables' => [
        'users' => 'Users',
    ],
    'fields' => [
        'email' => 'Email',
    ],
];
```

Field keys are flat (not nested per-table) — the same `admin.fields.email` key is reused
across every resource that has an `email` field.

Usage:

```php
__('admin.fields.email')
```

---

# 🧠 Filament Integration

Filament v5 fully supports Laravel translations.

## Example usage in Filament Resource:

```php
TextInput::make('email')
    ->label(__('admin.fields.email'));
```

```php
TextColumn::make('category.title')
    ->label(__('admin.fields.category_id'));
```

In practice, most simple scalar fields (`title`, `display`, `slug`, ...) are left without an
explicit `->label()` call and rely on Filament's default humanized label. Explicit
`->label(__('admin.fields.{field}'))` calls are mainly used for relation columns/fields
(e.g. `category.title` column, `category_id` select) where the field name doesn't match the
translation key on its own. See `docs/filament/resource-generation.md` for the full convention.

---

# ⚙️ Locale Configuration

Default locale is set in env file:

```ENV
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

---

# 🔄 Changing Locale

Locale can be changed via:

```php
App::setLocale('uk');
```

There is currently no `SetLocale` middleware in this project (only `en` is populated) — add
one under `app/Http/Middleware/` if/when multi-locale switching is implemented.

---

# 🧩 Filament Notes

Filament does NOT require separate translation setup.

However, vendor translations can be published if needed:

```bash
php artisan vendor:publish --tag=filament-panels-translations
php artisan vendor:publish --tag=forms-translations
php artisan vendor:publish --tag=tables-translations
php artisan vendor:publish --tag=filament-support-translations
```

Published files will appear in:

```
lang/vendor/filament
lang/vendor/forms
lang/vendor/tables
```

---

# 📌 Project Convention

We follow these rules:

* All UI text MUST use `__()` helper
* No hardcoded strings in Filament resources
* Admin translations stored in `admin.php`

---

[Back to main readme](../../README.md)
