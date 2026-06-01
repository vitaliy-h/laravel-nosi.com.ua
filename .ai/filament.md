# Laravel Filament Admin Panel Architecture Rules

You are working on a Laravel + Filament admin panel project.

Your task is to generate production-ready admin modules that follow the existing project architecture, naming conventions, UI patterns, and coding style.

Always reuse existing project patterns whenever possible.

---

# MAIN PRINCIPLES

* Prefer Laravel + Filament native solutions.
* Prefer predictable CRUD architecture over complex abstractions.
* Reuse existing project patterns.
* Avoid unnecessary custom solutions.
* Avoid duplicated logic.
* Generate production-ready code.

---

# CANONICAL REFERENCE MODULES

Use these existing modules as canonical architecture references.

Study and follow their:

* folder structure
* naming
* relationships
* migrations
* form layout
* table layout
* filters
* actions
* validation style
* UI patterns
* upload patterns
* SEO patterns

Reference Resources for modules with a single entity:

```txt
app/Filament/Resources/Shop/Products/ProductResource.php
app/Filament/Resources/Shop/Products/Pages/ManageProducts.php
```

Reference Resources for modules with multiple related entities:

```txt
app/Filament/Resources/Pages/PageResource.php
app/Filament/Resources/Pages/Pages/ManagePages.php
```

Reference Models:

```txt
app/Modules/News/Models/News.php
app/Modules/Shop/Models/Category.php
```

Reference Migrations:

```txt
database/migrations/2026_05_04_170207_create_pages_table.php
database/migrations/2026_05_06_175508_create_news_table.php
database/migrations/2026_05_08_133407_create_shop_categories_table.php
database/migrations/2026_05_08_133408_create_shop_brands_table.php
```

When generating a new module:

* identify the closest existing module
* follow its structure and implementation style
* reuse existing project patterns
* avoid inventing new architecture

---

# CODE REUSE RULES

* Reuse existing shared components, traits, actions, helpers, and patterns whenever possible.
* Before generating new code, check whether a similar implementation already exists in the project.
* Reuse existing Filament form schemas, table schemas, filters, and actions whenever possible.

Example shared patterns:

```php
BaseEditAction::make()
```

```php
use HasImage;
```

---

# CONSISTENCY RULES

* Match the formatting, naming, structure, and coding style of nearby project files.
* Make minimal and targeted changes.
* Avoid modifying unrelated files.
* Do not refactor or redesign existing architecture unless explicitly requested.
* Follow existing namespace and folder structure conventions.

---

# FILAMENT CRUD RULES

* Prefer standard Filament CRUD Resources over custom pages, widgets, or workflows.
* Use custom implementations only when explicitly required.
* Use one Resource per entity.
* Use Relation Managers instead of custom relation pages whenever possible.

---

# DATABASE RULES

Always use:

```php
$table->id();
$table->timestamps();
```

at the top of migrations.

---

# SORTING STRATEGIES

Use only ONE sorting strategy per entity.

## Publication sorting

Use for:

* articles
* news
* blog posts

```php
$table->timestamp('published_at')->nullable();
```

---

## Nested tree sorting

Use for:

* categories
* menus
* hierarchical pages

```php
NestedSet::columns($table);
```

---

## Manual sorting

Use for:

* sliders
* blocks
* manually ordered lists

```php
$table->integer('rank')->default(0);
```

---

# DEFAULT SYSTEM FIELDS

Public entities should usually include:

```php
$table->boolean('display')->default(true);

$table->string('slug')
    ->nullable()
    ->unique();

$table->string('meta_title')->nullable();

$table->text('meta_description')->nullable();
```

---

# RELATIONSHIP RULES

## Categories

Categories always use NestedSet.

Reuse the existing category select implementation from canonical resources.

---

## Tags

Tags always use many-to-many relationships.

Example:

```txt
articles
tags
article_tag
```

Never store relationships as JSON.

---

## Foreign keys

Use nullable foreign keys unless relation is strictly required.

Example:

```php
$table->foreignId('category_id')
    ->nullable()
    ->constrained('shop_categories');
```

---

# FILAMENT FORM RULES

All forms should use:

```php
->columns(12)
```

---

# FORM LAYOUT RULES

First row should usually contain:

* title
* slug
* sorting field
* display toggle

Display toggle should be on the right side.

Large text editors should be closer to the bottom.

SEO fields should always be at the bottom.

Reuse existing form layouts from canonical resources.

---

# SELECT RULES

All Select fields should use:

```php
->searchable()
->preload()
```

Reuse the existing hierarchical category select implementation.

---

# FILE UPLOAD RULES

Images should use:

```php
FileUpload::make('img')
    ->image()
    ->visibility('public')
```

Reuse existing upload naming patterns and helper methods.

---

# TABLE RULES

Tables should usually contain:

* image column first
* title
* slug
* relations
* sorting fields
* display toggle

`created_at` and `updated_at` should usually be hidden by default using toggleable columns.

Reuse existing table layouts from canonical resources.

---

# FILTER RULES

Filters should usually support:

```php
->searchable()
->preload()
```

---

# TRANSLATION RULES

All admin panel text must use translations from:

```txt
lang/en/admin.php
```

This includes:

* tables - table names in the database
* fields - fields & filters
* messages - messages
* actions - buttons & actions

Example:

```php
->label(__('admin.fields.category_id'))
->label(__('admin.tables.shop'))
->label(__('admin.messages.delete_child_items_forbidden'))
->label(__('admin.actions.saveClose'))
```

---

# MENU RULES

Register resources in:

```txt
app/Providers/Filament/AdminPanelProvider.php
```

Group related resources together.

---

# DO NOT

* Do not generate unnecessary services.
* Do not generate unnecessary custom Livewire components.
* Do not invent new architecture patterns if project patterns already exist.
* Do not duplicate CRUD logic.
* Do not store relationships as JSON.
* Do not generate overly complex solutions.
* Do not create API endpoints unless explicitly requested.
* Do not refactor unrelated code.
* Do not modify unrelated files.

---

# PREFERRED APPROACH

Prefer:

* Filament Resources
* Laravel relationships
* shared traits
* shared actions
* reusable UI patterns
* predictable CRUD structure

over custom or complex solutions.

---

# OUTPUT REQUIREMENTS

When generating a module:

1. Create migration
2. Create model
3. Create relationships
4. Create Filament Resource
5. Create Manage page
6. Register navigation/menu
7. Follow existing project patterns

Generated code must be complete and production-ready.
