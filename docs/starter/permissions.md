# Roles and permissions

Access control is `spatie/laravel-permission` (`config/permission.php` is the package's
stock config — nothing project-specific was changed there). Two things are worth knowing:
what the seeder creates, and where the checks actually happen.

---

## What the seeder creates

`database/seeders/RolesAndPermissionsSeeder` is idempotent (`firstOrCreate` +
`syncPermissions`), so it is safe to re-run after adding a permission.

**Permissions** (guard `web`):

| Permission | Meaning |
|---|---|
| `admin` | may open the admin panel |
| `user` | a plain site account |

**Roles** (guard `web`):

| Role | Permissions |
|---|---|
| `admin` | all of them |
| `user` | `user` |

The seeder runs first in `DatabaseSeeder`, before pages and module data, because
everything else assumes the roles exist.

---

## Where access is decided

| Place | Check |
|---|---|
| Admin panel entry | `User::canAccessPanel()` — the `admin` permission |
| Filament resources | the panel's own authorization, on top of panel access |
| `UserPolicy` | registered in `AppServiceProvider`; guards view/update/delete/restore/forceDelete on `User` |

A user gets the `admin` permission either through the `admin` role or directly. Assign it
from the admin panel (`UserResource` has a roles select) or from tinker:

```php
User::firstWhere('email', 'someone@example.com')->assignRole('admin');
```

---

## Adding a permission

1. Add its name to the `$permissions` array in `RolesAndPermissionsSeeder`, and to the
   roles that should have it.
2. Re-run `php artisan db:seed --class=RolesAndPermissionsSeeder`.
3. Check it where it matters — `$user->can('your-permission')`, a Filament resource's
   authorization method, or a policy.

The package caches permissions; the seeder already calls
`PermissionRegistrar::forgetCachedPermissions()` at the start, but a manual change made
straight in the database needs that cache cleared too.

---

## Tests

`InteractsWithRolesAndPermissions` (`tests/Concerns/`) gives every test
`seedRolesAndPermissions()` and `actingAsAdmin()`. Use those instead of creating roles by
hand — see `docs/starter/testing.md`.

---

[Back to main readme](../../README.md)
