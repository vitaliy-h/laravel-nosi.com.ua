# Backend Developer Guide

## Routing conventions

Module web controllers (`Http/Controllers/Web/`) extend `App\Modules\Core\Abstract\BaseWebController` and define one real Laravel action method per named route (typed route parameters, no manual segment parsing). See `docs/starter/routing.md`.

All API controllers (`Http/Controllers/Api/`) extend `App\Modules\Core\Abstract\BaseApiController`.

Controllers never call the `view()` helper directly — they return `$this->render('blade.path', $data)` from `BaseWebController`. (`view()->share()` for shared view data is unaffected.)

## Module seeders

Demo data for a module goes through one entry-point seeder named
`App\Modules\{Module}\database\seeders\{Module}Seeder`, which calls the
module's sub-seeders in order. `database/seeders/DatabaseSeeder.php` finds
and calls it automatically — don't add per-module `if` blocks there. See
`docs/starter/modules.md` §6.

## Module toggles

Modules can be disabled via `config/modules.php`, gated by
`App\Modules\Core\Support\ModuleGate`. This affects routes, commands,
migrations, seeders, admin nav/resources, and tests for that module. See
`docs/starter/modules.md`.

## View layer

Data going from a controller to a view goes through a **ViewModel**: fed an
Eloquent model, it exposes fields already prepared for rendering. Controllers
never hand a blade an Eloquent model.

ViewModels live in `app/Modules/{Module}/ViewModels/` on top of
`App\Modules\Core\Abstract\BaseViewModel`. Two levels: `{Model}ViewModel` per
entity (reused across screens) and `{Page}ViewModel` per screen. Build them with
spatie's magical `from{Type}()`, collect with `::collect()` (paginators stay
paginators), hand a page to the view with `$page->toViewData()`.

A ViewModel never queries the DB — eager-loading is the caller's job — never
reaches into a switchable module in place of `ModuleGate`, and never carries a
field the page does not render. See `docs/starter/view-models.md`.

## API layer conventions

- **Actions** hold business logic — shared between web and API controllers
- **Controllers** are thin HTTP adapters only
- **DTOs** (`spatie/laravel-data`) structure input validation
- **Resources** structure API responses
- Response envelope: `{ success, message?, data }`

## Action return values

An Action returns exactly one of three things — never `bool`:

1. `void` — it mutates state and has nothing to report
2. a domain value (`Order`, `User`, `float`, …) — it always succeeds, any failure is an exception
3. `ActionResult<T>` (`App\Modules\Core\Dtos\ActionResult`) — the failure is an
   expected business outcome the caller must branch on (invalid promocode, quota exceeded)

The payload of an `ActionResult` goes in its own small DTO inside the owning
module, with **non-nullable** fields; the envelope only carries `success`,
`message`, `data`. Annotate with `/** @return ActionResult<PromocodeData> */`.
A genuine two-state query belongs in a `Query` class, not an Action.
See `docs/starter/actions.md`.

## Code style

```bash
./vendor/bin/pint
```
