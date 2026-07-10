# Backend Developer Guide

## Routing conventions

Module web controllers (`Http/Controllers/Web/`) extend `App\Modules\Core\Abstract\BaseWebController` and define one real Laravel action method per named route (typed route parameters, no manual segment parsing). See `docs/routing.md`.

All API controllers (`Http/Controllers/Api/`) extend `App\Modules\Core\Abstract\BaseApiController`.

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
