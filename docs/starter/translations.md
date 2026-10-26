# Localization System (Database-based translations)

This project uses a hybrid localization system based on:

- Laravel language files (as initial source)
- Database storage (as runtime editable translations)
- Filament admin panel (for management)
- Custom translation service (`t()` helper)

> **Scope:** this database-backed layer powers the **public-facing site** via the `t()`
> helper. The Filament **admin panel** UI labels use Laravel's native `__()` translations
> instead — see [Filament Translations](../filament/translations.md).

---

# 📦 Overview

The system provides:

- Database-driven translations
- Easy translation usage via `t('key')`
- Admin management via Filament
- Automatic language seeding from `/lang/*/dictionary.php`
- Dynamic translation form generation
- Module-based translation grouping

---

# 🧠 Architecture

## Data sources priority

1. Database main locale (`languages.dictionary`)
2. Database fallback locale (`languages.dictionary`)
3. Empty string (if nothing found)

---

## Database structure

### `languages` table

| Field        | Type   | Description                      |
|--------------|--------|----------------------------------|
| title        | string | Language name                    |
| localization | enum   | Language code (`en`, `uk`, etc.) |
| dictionary   | json   | Nested translation dictionary    |
| rank         | int    | Sorting order                    |
| display      | bool   | Visibility flag                  |

---

# 🌍 Language files (initial seed)

Language files are located in:

```text
lang/{locale}/dictionary.php
```

Main languages file is `lang/en/dictionary.php`.

This file is used:
- as default structure for new languages
- as initial seed source
- as translation form template

Example:

```php
return [

    'feedback' => [
        'name:input6' => 'Name',
        'message:textarea:br' => 'Message',
        'saved:input12' => 'Feedback has been saved.',
    ],

];
```

---

# 🧩 Dictionary structure

Translations are grouped by modules/pages.

Stored in database as nested arrays without suffixes.

The order of the groups in the file is the order of the sections in the admin form; only the
first one is expanded. The home page groups (`header`, `home`, `benefits`, `contacts`,
`footer`) go first. A section title comes from `admin.dictionary_sections.{group}` — add the
key to `lang/en/admin.php` and `lang/uk/admin.php` with a new group, otherwise the raw group
name is shown.

The `counters` group (`counters.head`, `counters.body`) holds analytics code (GA, GTM, Meta
Pixel…). `layouts/app.blade.php` prints it **unescaped**: `head` at the end of `<head>`,
`body` right after `<body>`.

Fixed system texts the manager does not edit — form attributes and notices of `feedback` /
`subscribe`, `news.back`, API messages, social login — are not in the dictionary: they live
in `lang/{locale}/site.php` and are read with `__('site.{group}.{key}')`.

---

# 🏗 Supported field suffixes

| Suffix         | Description           |
|----------------|-----------------------|
| textarea       | Render textarea field |
| html           | Render rich editor    |
| input1-input12 | Column span           |
| br             | Start new row         |

Example:

```php
'name:input6:br'
```

---

# 🛠 Filament admin panel

Features:

- Add/edit languages
- Module-based collapsible sections
- Dynamic fields generation
- Autofill translations from language templates
- Rich editor support
- Automatic cache clearing

---

# 🔄 Dynamic language templates

When creating a new language:

1. System tries loading:

```text
lang/{locale}/dictionary.php
```

2. If file does not exist:
    - fallback to:

```text
lang/en/dictionary.php
```

3. Fields are generated dynamically

---

# 🚀 Seeding translations

Seeder behavior:

- Scans all folders inside `/lang`
- Loads `dictionary.php`
- Removes UI suffixes from keys
- Creates or updates language in DB
- Clears translation cache

Run:

```bash
sail artisan db:seed --class=LanguageSeeder
```

⚠ IMPORTANT:
Running seeder again rewrites dictionaries using file defaults.

---

# 🛠 Translation usage

## Helper

```php
t('auth.name')
```

## Blade

```blade
{!! t('buttons.save') !!}
```

## PHP

```php
$message = t('buttons.save');
```

---

# ➕ Adding new translation keys

1. Add key to:

```text
lang/en/dictionary.php
```

Example:

```php
'feedback' => [
    'phone:input6' => 'Phone',
],
```

2. Add translations in admin panel

OR run:

```bash
php artisan db:seed --class=LanguageSeeder
```

⚠ WARNING:
Seeder rewrites dictionaries using default file values.

---

# 🌍 Adding a new language

## Step 1

Add new case to Localization enum:

```php
case DE = 'de';
```

---

## Option 1 — Recommended

Create language in admin panel.

✔ Safe  
✔ Does not overwrite translations

---

## Option 2 — Bootstrap from file

Create:

```text
lang/de/dictionary.php
```

Run:

```bash
php artisan db:seed --class=LanguageSeeder
```

⚠ WARNING:
This overwrites DB translations with file defaults.

---

# 🧠 Cache system

Translations are cached per locale:

```text
translations.{locale}
```

Cache clears automatically after:
- update
- delete
- seeder execution

---

# 📌 Summary

- Laravel files = bootstrap + templates
- Database = runtime source
- Filament = admin UI
- TranslationService = translation engine
- `t()` = helper API

---

[Back to main readme](../../README.md)
