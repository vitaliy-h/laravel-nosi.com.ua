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
lang/en/admin.php      lang/uk/admin.php        admin UI strings
lang/en/enums.php      lang/uk/enums.php        enum label() values (admin + public site)
lang/{locale}/validation.php, auth.php, passwords.php, pagination.php
```

Both `en` and `uk` are populated and must keep the same keys — every new key goes into both
files. The admin panel itself is shown in Ukrainian by default (see *Locale Configuration*).
Filament's own UI strings (buttons, tables, modals) come from Filament's bundled `uk` translations.

`admin.dictionary_sections.{group}` holds the section titles of the language form — one per
`lang/{locale}/dictionary.php` group (see [Starter Translations](../starter/translations.md)).

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

Most simple scalar fields (`title`, `display`, `slug`, ...) are left without an explicit
`->label()` call: `App\Providers\FilamentServiceProvider::translateFilamentLabels()` registers
`configureUsing()` on every form field, table column, text entry and table filter, which sets the
label from `admin.fields.{name}` (last segment of a dotted name) when that key exists (otherwise
Filament's humanized English name is used — so add the key, in both `en` and `uk`).
Table actions get the resource's model label via `Table::configureUsing()` in `AdminPanelProvider`.
An explicit `->label()` in a resource still wins. Explicit
`->label(__('admin.fields.{field}'))` calls are mainly used for relation columns/fields
(e.g. `category.title` column, `category_id` select) where the field name doesn't match the
translation key on its own. See `docs/filament/resource-generation.md` for the full convention.

## Resource labels

Every resource defines its labels, otherwise Filament shows the English model class name
("Create product"):

```php
public static function getModelLabel(): string
{
    return __('admin.models.shop_product');   // singular, accusative in uk: "Створити товар"
}

public static function getPluralModelLabel(): string
{
    return __('admin.tables.shop_products');
}
```

`admin.models.*` holds the singular forms in the accusative case, because Filament inserts
them into "Створити :label" / "Редагувати :label" / "Видалити :label".

## Enum labels

Enum `label()` methods return `__("enums.{enum}.{$this->value}")` (`lang/{locale}/enums.php`),
or `__('admin.…')` for admin-only enums such as the auth journal ones.

---

# ⚙️ Locale Configuration

```ENV
APP_LOCALE=en          # public site / API
APP_FALLBACK_LOCALE=en
ADMIN_LOCALE=uk        # admin panel interface (config('app.admin_locale'), default uk)
```

The admin locale is applied by `App\Filament\Http\Middleware\SetAdminLocale`, registered on the
panel as **persistent** middleware so Livewire update requests (table actions, modals, form
saves) get it too. The public site is not affected. Set `ADMIN_LOCALE=en` for an English panel.

Tests that call `Livewire::test()` directly don't pass through panel middleware, so they run in
`APP_LOCALE` (`en`); HTTP tests against `/admin/...` run in `ADMIN_LOCALE`.

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
* Admin translations stored in `admin.php`, both `lang/en/` and `lang/uk/`

---

[Back to main readme](../../README.md)
