# Translations (Laravel Standard Approach)

This project uses **Laravel native localization system** for both the application and Filament admin panel.

No custom translation layer is used.

---

# 🌍 Localization Strategy

We use Laravel built-in localization system:

* `resources/lang/{locale}/`
* helper: `__()`
* helper: `@lang()` (Blade)

No Filament-specific translation system is used.

---

# 📂 Language File Structure

```
resources/lang/en/admin.php
resources/lang/uk/admin.php
```

Structure Example:

```php
// resources/lang/en/admin.php
return [
    'users' => [
        'fields' => [
            'email' => 'Email address',
        ],
    ],
];
```

Usage:

```php
__('admin.users.fields.email')
```

---

# 🧠 Filament Integration

Filament v5 fully supports Laravel translations.

## Example usage in Filament Resource:

```php
TextInput::make('email')
    ->label(__('admin.users.fields.email'));
```

```php
TextColumn::make('name')
    ->label(__('admin.users.fields.name'));
```

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

Recommended approach: middleware

```
app/Http/Middleware/SetLocale.php
```

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
resources/lang/vendor/filament
resources/lang/vendor/forms
resources/lang/vendor/tables
```

---

# 📌 Project Convention

We follow these rules:

* All UI text MUST use `__()` helper
* No hardcoded strings in Filament resources
* Admin translations stored in `admin.php`

---

# 📎 Related Documentation

* [General README](../README.md)
* [Filament Admin Panel](../filament.md)
    * [Resource Generation](resource-generation.md)
* [Project Architecture](../architecture.md)
    * [images](../images.md)
* [local Setup](../local_setup.md)

---
