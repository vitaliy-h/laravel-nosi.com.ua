# Backend Developer Guide

## Additional information

- Stack: `@.ai/stack.md`


## Module workflow

Typical module internals: `Http/Controllers/`, `Models/`, `Services/`, `Repositories/`, `Dtos/`, `Actions/`, `Enums/`, `routes/`.

**Adding a new module:**
1. Create folder under `app/Modules/`
2. Add `case` to `app/Modules/Core/Enums/PageModule.php` (name, value, label)

**`php artisan make:model`** places file in `app/Models/` — move manually to `app/Modules/{Module}/Models/` and update namespace to `App\Modules\{Module}\Models`.

## Routing conventions

Module web controllers must implement `App\Modules\Core\Interfaces\ModuleIndexControllerInterface` and accept `(Request $request, array $moduleSegments = [])`.

Generate module URLs:
```php
module_url(PageModule::NEWS, [$item->slug])
```

## API layer conventions

- **Actions** hold business logic — shared between web and API controllers
- **Controllers** are thin HTTP adapters only
- **DTOs** (`spatie/laravel-data`) structure input validation
- **Resources** structure API responses
- Response envelope: `{ success, message?, data }`

## Filament admin panel

```bash
php artisan make:filament-resource ResourceName --simple --generate --no-interaction
```

- `--simple` = modal-based CRUD, `--generate` = auto-generate form from DB schema
- Resources live in `app/Filament/Resources/{Module}/` with `Pages/Manage{Model}.php`
- Use `HasDefaultTable::applyDefaultTable($table)` and `BaseEditAction::make()` for standard layout
- Navigation items must be added **manually** to `AdminPanelProvider` — add `NavigationItem` (or `NavigationGroup`) in `->navigation()` closure
- Custom stubs (`AdminResourceClassGenerator`, `AdminResourceManageRecordsPageClassGenerator`) auto-apply conventions

## Translations

**Admin panel** (`__()` / `@lang()`):
- Files at `lang/{locale}/admin.php`
- Filament labels must use `__('admin.fields.field_name')`
- Labels **auto-translated** by `AppServiceProvider::translateFilamentLabels()` if `admin.fields.*` key exists
- Menu/group labels under `admin.tables.*`

**Public site** (`t()`):
- Database-driven; source of truth is `lang/{locale}/dictionary.php`
- Seeded with `db:seed --class=LanguageSeeder`
- Managed via Filament admin
- Keys use suffixes for form rendering: `name:input6:br`, `body:html`

## Code style

```bash
./vendor/bin/pint
```
