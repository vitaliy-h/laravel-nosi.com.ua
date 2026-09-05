# Debug Tools — running artisan from the admin panel

`app/Filament/Pages/DebugTools/DebugTools.php` gives a Super Admin a page in the admin
panel that runs a fixed list of artisan commands and shows their output in a modal. It is
the most dangerous screen in the project, so the rules around it are strict.

---

## Who can open it

```php
public static function canAccess(): bool
{
    return auth()->user()?->isSuperAdmin() ?? false;
}
```

Only the `super_admin` role. A regular admin does not see the navigation item and cannot
mount the Livewire component (403). `runCommand()` re-checks on every call, so the page
cannot be driven from outside the UI either.

Every run is written to the application log as a `warning` with the action, the resolved
command, its parameters, the acting user's id and email, and the exit code
(`ArtisanCommandRunner::run()`).

---

## What it can run

The whitelist lives in `app/Filament/Support/ArtisanCommandRunner::COMMANDS`. Anything not
in it is refused with 422 — the page never passes free-form input to artisan.

| Action | Destructive | Available in production |
|---|---|---|
| `migrate` | no | yes |
| `migrate:status` | no | yes |
| `cache:clear`, `config:clear`, `route:clear`, `view:clear`, `optimize:clear` | no | yes |
| `queue:restart` | no | yes |
| `migrate:rollback` | **yes** | no |
| `migrate:fresh` | **yes** | no |
| `migrate:fresh --seed` | **yes** | no |
| `db:seed` | **yes** | no |

**Destructive actions are staging-only.** `ArtisanCommandRunner::isAvailable()` returns
`false` for them once `app()->isProduction()`, and that is enforced twice:

- the page does not render their sections at all, so a production admin never sees the
  buttons;
- `run()` throws even if the action id is passed by hand.

Outside production they are rendered in red and behind a confirmation modal.

⚠️ "Production" here means `APP_ENV=production`. A live site running with a staging
`APP_ENV` gets the full, destructive list — set `APP_ENV` correctly on deploy.

---

## Adding a command

Add an entry to `COMMANDS` with `destructive` set honestly, and a label under
`admin.debug_tools.commands.{key}` in the translations. Use the array key as the action id;
add `command`/`params` only when the id differs from what artisan should receive
(`'migrate:fresh --seed'` is the example — the id carries the flag for readability, the
`params` do the work).

Ask one question before adding: *would running this by accident on a live site be
recoverable?* If not, mark it `destructive` and it stays out of production automatically.

---

## Tests

- `tests/Unit/Filament/ArtisanCommandRunnerTest.php` — the whitelist and the production
  gate, both directions.
- `tests/Feature/Admin/SuperAdminTest.php` — a super admin can run a whitelisted command,
  a non-whitelisted one gives 422, a regular admin gets 403.

---

[Back to Filament docs](README.md)
