# Backend Developer Guide

## Routing conventions

Module web controllers (`Http/Controllers/Web/`) extend `App\Modules\Core\Abstract\BaseWebController` and define one real Laravel action method per named route (typed route parameters, no manual segment parsing). See `docs/routing.md`.

All API controllers (`Http/Controllers/Api/`) extend `App\Modules\Core\Abstract\BaseApiController`.

## Module seeders

Demo data for a module goes through one entry-point seeder named
`App\Modules\{Module}\database\seeders\{Module}Seeder`, which calls the
module's sub-seeders in order. `database/seeders/DatabaseSeeder.php` finds
and calls it automatically — don't add per-module `if` blocks there. See
`docs/modules.md` §6.

## Module toggles

Modules can be disabled via `config/modules.php`, gated by
`App\Modules\Core\Support\ModuleGate`. This affects routes, commands,
migrations, seeders, admin nav/resources, and tests for that module. See
`docs/modules.md`.

## API layer conventions

- **Actions** hold business logic — shared between web and API controllers
- **Controllers** are thin HTTP adapters only
- **DTOs** (`spatie/laravel-data`) structure input validation
- **Resources** structure API responses
- Response envelope: `{ success, message?, data }`

## Code style

```bash
./vendor/bin/pint
```
