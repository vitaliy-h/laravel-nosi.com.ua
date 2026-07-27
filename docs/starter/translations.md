# Localization System (Database-based translations)

This project uses a hybrid localization system based on:

- Laravel language files (as initial source)
- Database storage (as runtime editable translations)
- Filament admin panel (for management)
- Custom translation service (`t()` helper)

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
t('feedback.name')
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
