# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Code style
```bash
./vendor/bin/pint          # Laravel Pint (PSR-2 based)
```

### Filament resource generation
```bash
php artisan make:filament-resource ResourceName --simple --generate --no-interaction
```
(`--simple` = modal-based CRUD, `--generate` = auto-generate form from DB schema)

## Architecture

### Modular structure

All application logic lives in `app/Modules/`. Each module is self-contained:

```
app/Modules/
├── Core/          # shared utilities, base classes, traits, facades, helpers
├── Auth/          # API and WEB auth (Sanctum token flow)
├── Profile/       # user profile API. Here contains api methods for mobile app or SPA, and default web method. But both uses the same requests, and actions.
├── Translation/   # database-driven translations
├── Shop/          # shop module, products, categories ..
├── News/          # news module
├── Feedback/      # feeback module
├── TextPage/
└── Index/         # homepage
```

Typical module internals: `Http/Controllers/`, `Models/`, `Services/`, `Repositories/`, `Dtos/`, `Actions/`, `Enums/`, `routes/`.

**Adding a new module:** create the folder under `app/Modules/`, add a `case` to `app/Modules/Core/Enums/PageModule.php` (case name, value, and label).

**`php artisan make:model`** places the file in `app/Models/` — move it manually to `app/Modules/{Module}/Models/` and update the namespace to `App\Modules\{Module}\Models`.

### Routing

**GET requests are database-driven.** All GET routes pass through `App\Modules\Core\Http\Controllers\ModuleRouteController`, which resolves the target controller from the `pages` table using the `PageModule` enum. Module web controllers must implement `App\Modules\Core\Interfaces\ModuleIndexControllerInterface` and accept `(Request $request, array $moduleSegments = [])`.

**POST / mutation routes** use standard Laravel routing. Each module can define `app/Modules/{Name}/routes/web.php` and `routes/api.php`, which are **auto-loaded** by `AppServiceProvider::loadModuleRoutes()`. API routes are prefixed with `/api`.

Generate module URLs with the global helper:
```php
module_url(PageModule::NEWS, [$item->slug])
```

### API layer

- **Actions** hold business logic (shared between web and API)
- **Controllers** are thin HTTP adapters
- **DTOs** (`spatie/laravel-data`) structure input
- **Resources** structure API responses
- Response envelope: `{ success, message?, data }`
- Auth: Laravel Sanctum bearer token

### Filament admin panel conventions

Resources use `HasDefaultTable::applyDefaultTable($table)` and `BaseEditAction::make()` — these enforce the standard layout (modal-only CRUD, Edit+Delete row actions, Save / Save & Close buttons). See the Filament skill for generation details.

Resources live in `app/Filament/Resources/{Module}/` with a `Pages/Manage{Model}.php` inside. `discoverResources` auto-loads them, but **navigation items must be added manually** to `AdminPanelProvider` — add a `NavigationItem` (or `NavigationGroup`) in the `->navigation()` closure.

### Translations (two separate systems)

1. **Admin panel** (`__()` / `@lang()`): Laravel native files at `lang/{locale}/admin.php`. All Filament labels must use `__('admin.fields.field_name')`. Field labels are **auto-translated** by `AppServiceProvider::translateFilamentLabels()` if a matching `admin.fields.*` key exists. Menu/group labels go under `admin.tables.*`.

2. **Public site** (`t()`): Database-driven. Source of truth is `lang/{locale}/dictionary.php`. Seeded with `db:seed --class=LanguageSeeder`. Managed via Filament. Use `t('module.key')` in Blade and PHP. Keys use suffixes to control form rendering (e.g. `name:input6:br`, `body:html`).

### Logging

Model create/update/delete events are automatically observed via `LogObserver` (registered globally in `AppServiceProvider`).

### Filament generators

Custom stubs (`AdminResourceClassGenerator`, `AdminResourceManageRecordsPageClassGenerator`) are bound in `AppServiceProvider` and auto-apply project conventions when running `make:filament-resource`.

### Additional

Always add newly created files to git
