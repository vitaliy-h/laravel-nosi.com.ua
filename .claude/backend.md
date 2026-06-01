# Backend Developer Guide

## Additional information

- Stack: `@.ai/stack.md`


## Routing conventions

Module web controllers must implement `App\Modules\Core\Interfaces\ModuleIndexControllerInterface` and accept `(Request $request, array $moduleSegments = [])`.

All Api controllers must extends ` App\Modules\Core\Abstract\BaseApiController`

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
