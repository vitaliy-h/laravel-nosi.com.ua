## Quick commands

* `composer install` — install Composer dependencies

* `composer update` — update Composer dependencies

* `npx @anthropic-ai/claude-code` — run Claude Code

* `php artisan migrate` — run migrations.

* `php artisan db:seed` — run seeders.

* `php artisan migrate:fresh --seed` — refresh database. **This will drop all tables!!!** 

* `php artisan optimize:clear`

* `php artisan modules:cache` — cache the module manifest (which modules are
  enabled, their routes/migrations/observers/commands, and the page slug each
  page-driven module is mounted behind) to `bootstrap/cache/modules.php`.
  Run it **after** `migrate` and **before** `route:cache` — see
  [modules.md](modules.md#7-the-module-manifest).

* `php artisan modules:clear` — drop the cached manifest; the app then rebuilds
  it in memory on every boot, which is the local default.

