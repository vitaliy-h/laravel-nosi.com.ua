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

---

# 🧠 Architecture

## Data sources priority

1. Database main locale (`languages.dictionary`)
2. Database Fallback locale (`languages.dictionary`)
3. Empty string (if nothing found)

---

## Database structure

### `languages` table

| Field        | Type   | Description                      |
|--------------|--------|----------------------------------|
| title        | string | Language name                    |
| localization | enum   | Language code (`en`, `uk`, etc.) |
| dictionary   | json   | Key-value translations           |
| rank         | int    | Sorting order                    |
| display      | bool   | Visibility flag                  |

---

# 🌍 Language files (initial seed)

Language files are located in:

lang/{locale}/dictionary.php

Main languages file is `lang/en/dictionary.php`. This file used as default for new languages when you create them in admin panel, and as structure for exists ones.

Example:

``` php
return [
    'index.hero.title' => 'Welcome',
    'buttons.save' => 'Save',
];
```

These files are used only for initial database seeding.

After that, translations are managed in the database.

---

# 🚀 Seeding translations

The system automatically imports translations from language files using
a seeder.

### Behavior:

- Scans all folders inside `/lang`
- Loads `dictionary.php`
- Creates or updates language in DB
- Sets dictionary as initial data

### Seeder:

``` bash
sail artisan db:seed --class=LanguageSeeder
```

---

# 🛠 Translation usage

## Recommended helper

``` php
t('menu.home')
```

---

## Example in Blade

``` blade
<h1>{{ t('index_hero_title') }}</h1>
```

---

## Example in PHP

``` php
$message = t('buttons_save');
```

---

## With replacements

``` php
t('messages_welcome', [
    'name' => $user->name,
]);
```

---

# 🔄 Translation service

All translation logic is handled by:

App\Modules\Core\Services\TranslationService

### Responsibilities:

- Resolve current locale
- Fetch translations from DB
- Apply fallback locale
- Cache translations per language, and clear cache after update
- Replace placeholders variables

---

# ⚡ Helper function

A global helper is available:

`t(string $key, array $replace = [])`

This is a wrapper over the TranslationService.

---

# 🧩 Admin panel (Filament)

Translations are fully editable via Filament Resource:

## Features:

- Add/edit languages
- Manage dictionary keys
- Enable/disable language
- Set display order
- Edit translations per key

---

## Dictionary editing

Translations are stored as JSON:

``` json
{
    "menu_home": "Home",
    "buttons_save": "Save"
}
```

---

# ➕ Adding new translation keys

## Step 1 --- Add key to language file

Edit:

lang/en/dictionary.php

Example:

``` php
return [
    'new_key_example' => 'Example text',
];
```

---

## Step 2 --- Run seeder or manual update in admin panel.

Here you can choose your option:

* Run seeder. !IMPORTANT this will rewrite all dictionary to default values.
    ``` bash
    php artisan db:seed --class=LanguageSeeder
    ```
* Set value for new keys in admin panel. This is better solution for adding new keys.

---

## Step 3 --- Use in code

``` php
t('new_key_example')
```

---

# 🌍 Adding a new language

There are two ways to add a new language.

---

## First step

Add new case to `Localization` enum:

```php
case DE = 'de';
```

## ✅ Option 1 — Recommended (Admin Panel)

1. Open Filament Admin Panel
2. Create new Language
3. Set:
    - title
    - localization (de)
    - rank
    - display = true
4. Save

✔ Safe\
✔ Does not overwrite data\
✔ Production-ready

---

## ⚠️ Option 2 — File-based bootstrap

1. Create folder:

```
lang/de/
```

2. Add file:

```
lang/de/dictionary.php
```

3. Add translations

```php
return [
    'menu.home' => 'Startseite',
];
```

4. Run seeder:

```bash
php artisan db:seed --class=LanguageSeeder
```

⚠ WARNING:

- This will overwrite DB translations with file defaults
- Intended only for initial setup

---

# 🔁 Updating translations

After initial seeding:

- All translation changes are made in admin panel
- Language files are no longer the source of truth
- Database always takes priority

---

# 🧠 Cache system

Translations are cached per locale:

- Cache key: `translations.{locale}`
- Cache is automatically cleared when language is updated or deleted

---

# 🧭 Best practices

✔ Use `t()` everywhere\
✔ Keep keys in snake case (`menu_home`, `buttons_save`)\
✔ Do not store business logic translations in language files after seed\
✔ Manage translations via admin panel

---

[Back to main readme](./README.md)
