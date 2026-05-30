# Tech Stack

> Laravel Filament Starter — a modular admin-panel starter kit (Laravel 13 + Filament v5).
> Cloned as a foundation for new projects with auth, RBAC, localization, logging, a shop module, and a REST API pre-wired.

## Runtime & Platform

| Area | Choice | Version |
|------|--------|---------|
| Language | PHP | ^8.3 |
| Framework | Laravel | ^13.0 |
| Local env | Laravel Sail (Docker) / OpenServer (Windows) | — |
| Database | MariaDB | 11.8 |
| Queue driver | database | — |
| Cache store | database | — |
| Session driver | database (default) | — |

## Backend Packages

| Package | Purpose | Version |
|---------|---------|---------|
| `filament/filament` | Admin panel UI | ^5.4 |
| `laravel/sanctum` | Token-based API auth | ^4.3 |
| `spatie/laravel-permission` | Roles & permissions (RBAC) | ^7.2 |
| `spatie/laravel-data` | Typed DTOs | ^4.23 |
| `kalnoy/nestedset` | Nested-set trees (shop categories) | ^7.0 |
| `laravel/tinker` | REPL | ^3.0 |

## Frontend / Build

| Tool | Purpose | Version |
|------|---------|---------|
| Vite | Asset bundler | ^8.0 |
| `laravel-vite-plugin` | Laravel ↔ Vite integration | ^3.0 |
| Tailwind CSS | Styling (`@tailwindcss/vite`) | ^4.0 |
| concurrently | Parallel dev processes | ^9.0 |

> Public-facing pages use Blade + Bootstrap pagination (`Paginator::useBootstrap()`); the admin UI is Filament (Livewire/Alpine under the hood).

## Dev / Tooling

| Tool | Purpose | Version |
|------|---------|---------|
| PHPUnit | Test runner | ^12.5 |
| Laravel Pint | Code style | ^1.27 |
| Laravel Pail | Log tailing | ^1.2.5 |
| barryvdh/laravel-debugbar | Debugging | ^4.2 |
| nunomaduro/collision | CLI error reporting | ^8.6 |
| fakerphp/faker | Test/seed data | ^1.23 |
| mockery/mockery | Mocking | ^1.6 |

## Architecture

**Modular structure** under `app/Modules/` — each module owns its controllers, models, services, DTOs, repositories, enums, and routes.

```
app/Modules/
├── Core/         shared kernel: traits, facades, helpers, services, base classes,
│                 logging, menu injection, module route dispatcher
├── Auth/         login/register/password-reset (web + API), Sanctum
├── Index/        homepage
├── News/         news module
├── Feedback/     feedback page
├── TextPage/     static text pages
├── Profile/      user profile
├── Shop/         products / categories / brands
└── Translation/  DB-backed i18n
```

### Routing
- `routes/web.php` and `routes/api.php` are intentionally **empty**.
- `AppServiceProvider::loadModuleRoutes()` auto-discovers each module's `routes/web.php` (web middleware) and `routes/api.php` (`api` prefix + middleware).
- A catch-all `fallback` route (`Core/routes/web.php`) dispatches unmatched URLs to `ModuleRouteController`, which resolves a `Page` by slug, reads its `module` enum, and invokes `App\Modules\{Module}\Http\Controllers\Web\IndexController` (contract: `ModuleIndexControllerInterface` → `accessCheck()` then `index()`). This acts as a lightweight CMS layer.

### Admin Panel (Filament)
- Single `admin` panel mounted at `/admin`, configured in `app/Providers/Filament/AdminPanelProvider.php`.
- Manually curated navigation via `NavigationBuilder` (Pages, Languages, News, Feedback, Letter Templates; grouped Shop / Users / Logs).
- Resources under `app/Filament/Resources/` organized by domain (Shop → Brands/Categories/Products).
- **Auto-translated labels**: `Field/Column/Filter/TextEntry::configureUsing` pull labels from `admin.fields.*` translation keys.
- Custom resource generators (`App\Filament\Generators\*`) override Filament scaffolding via container bindings.

### Cross-cutting systems
- **Activity logging** — global wildcard Eloquent listeners (`created`/`updated`/`deleting`) feed `LogObserver` → `activity_logs` table.
- **Letters/email** — `LetterTemplate` + queued `SendLetterTemplateJob` + `TemplateMail`; delivery tracked in `log_letters`.
- **Localization** — DB-backed `Language` model, `TranslationService` singleton, `t()` helper, menus injected via `InjectMenus` middleware.
- **API** — JSON envelope (`success` / `message` / `errors`) with centralized exception rendering in `bootstrap/app.php` for `api/*` routes; Sanctum auth; documented via Postman collection in `docs/api/`.

## Conventions
- Namespacing: `App\Modules\{Module}\...` (PSR-4 autoload of `App\` → `app/`).
- Global helpers loaded from `app/Modules/Core/Helpers/functions.php` (composer `autoload.files`).
- Service singletons registered in `AppServiceProvider`: `url-helper`, `translation`, `letter-template-service`.

## Entry Points
- `bootstrap/app.php` — app config, middleware (`InjectMenus`, `auth` alias), API exception rendering.
- `bootstrap/providers.php` — `AppServiceProvider`, `Filament\AdminPanelProvider`.
- `composer dev` — runs `php artisan serve` + `queue:listen` + `pail` + `npm run dev` concurrently.
- `composer setup` — install, env, key, migrate, npm build.

## Docs
Project documentation lives in `docs/` (architecture, routing, logs, letters, images, translations, API + Postman collection).

> ⚠️ `docs/architecture.md` currently states "No custom route loader is used" / "Laravel standard routing", which contradicts the actual module route loader + fallback dispatcher. Treat this file as the source of truth for routing.
