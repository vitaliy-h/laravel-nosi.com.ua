# Logs System

## Overview

The Logs system provides a centralized audit trail for all
changes made within the application.

There are two independent journals:

- **change log** (`logs`) — what was created, updated or deleted (this page, sections below)
- **authentication log** (`log_auth`) — who signed in, when, from where and with what result
  (see [Authentication log](#authentication-log))

It automatically tracks:

- Created records
- Updated records
- Deleted records

Changes are stored regardless of source:

- Admin Panel
- API
- Console
- Background jobs

------------------------------------------------------------------------

## Storage Structure

Each log entry contains:

``` json
{
  "user_id": 1,
  "event": "created|updated|deleted",
  "model_type": "App\Modules\News\Models\News",
  "model_id": 123,
  "old_values": {},
  "new_values": {},
  "changed_fields": [],
  "source": "admin_panel|api|console|system|client",
  "ip_address": "127.0.0.1"
}
```

------------------------------------------------------------------------

## Architecture

### Core components

- LogService -- main service responsible for writing logs
- Log model -- database representation
- LogSourceResolver -- detects source (admin/api/console)
- config/log.php -- configuration rules

------------------------------------------------------------------------

## Logging Flow

All logging is handled automatically via observers or service calls:

### 1. Created

LogService::logCreated(\$model);

Stores: - new values only - changed fields

------------------------------------------------------------------------

### 2. Updated

LogService::logUpdated(\$model);

Stores: - old values - new values - changed fields

------------------------------------------------------------------------

### 3. Deleted

LogService::logDeleted(\$model);

Stores: - old values only - changed fields

------------------------------------------------------------------------

## Field Filtering Rules

The system supports field-level logging policies via config.

### config/log.php

``` php
return [
    'enabled' => env('LOG_ENABLED', true),

    // never logged — logging a log row would recurse
    'excluded_models' => [
        Log::class,
        AuthLog::class,
    ],

    'excluded_tables' => [
        'cache',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'migrations',
    ],

    'ignored_attributes' => [
        'updated_at',
        'created_at',
    ],

    // how long log records are kept before `logs:prune` removes them
    'retention_days' => env('LOG_RETENTION_DAYS', 365),

    // fields that only track "changed", without values. Use this for big fields.
    'only_track_changes' => [
        'dictionary',
    ],
];
```

`LOG_ENABLED` and `LOG_RETENTION_DAYS` are the two env knobs.

Additionally, you can exclude specific model fields from log by adding `logExcept` attribute:

``` php
$logExcept = ['except_filed'];
```

------------------------------------------------------------------------

## Only Track Changes Mode

Some fields should NOT store values. For example `languages.dictionary` field.

Instead of:

old/new values

We store only:

changed_fields only


------------------------------------------------------------------------

## Cascade Delete Logging

By default, Eloquent observers do NOT fire for models deleted via database-level `CASCADE DELETE` constraints. To ensure related models are logged when a parent is deleted, use the `CascadesDeletes` trait.

### Setup

Add the trait to the parent model and declare which relations should cascade via Eloquent:

``` php
use App\Modules\Core\Traits\Models\CascadesDeletes;

class Order extends Model
{
    use CascadesDeletes;

    protected array $cascadeDeletes = ['items', 'shipments'];
}
```

### How it works

The trait boots itself (`bootCascadesDeletes()` registers a `deleting` hook on the model),
so adding the trait is all that is needed — nothing has to be wired in a service provider.

When the parent model is deleted:

1. Each relation listed in `$cascadeDeletes` is iterated
2. Every child model is deleted via `->delete()` (Eloquent, not DB cascade)
3. Eloquent fires `deleting` event on each child → `Core\Listeners\LogListener`
   logs the deletion. The whole cascade runs inside `LogService::batch()`, so those
   journal rows are collected in memory and written with a single `INSERT` instead of
   one per child
4. If a child also uses `CascadesDeletes`, the cascade continues recursively
5. Finally, the parent itself is logged as deleted

### Important

- Remove or avoid `onDelete('cascade')` on FK constraints for relations listed in `$cascadeDeletes`, otherwise DB will delete children silently before Eloquent processes them
- Relations must be defined as Eloquent relationship methods on the model

### Currently configured

| Model    | Cascades               |
|----------|-------------------------|
| `Order`  | `items` (OrderProduct)  |
| `Cart` | `items`                 |

------------------------------------------------------------------------

## Admin Panel Integration

View Logs button available on every edit modal.

------------------------------------------------------------------------

## Event Types

created, updated, deleted

------------------------------------------------------------------------

## Sources

`App\Modules\Core\Enums\LogSourceEnum` — five cases:

| Case | Value |
|---|---|
| `ADMIN_PANEL` | `admin_panel` |
| `API` | `api` |
| `CONSOLE` | `console` |
| `SYSTEM` | `system` |
| `CLIENT` | `client` |


------------------------------------------------------------------------

<a id="authentication-log"></a>

## Authentication log (`log_auth`)

A separate journal with its own table, its own records and its own filters.
The change log above is untouched by it.

### What is recorded

| Field | Notes |
|---|---|
| `created_at` | date and time |
| `user_id` | nullable — the login may not belong to any user |
| `login` | the submitted login, always stored, even when no such user exists |
| `source` | `AuthLogSourceEnum` |
| `action` | `AuthLogActionEnum` |
| `result` | `AuthLogResultEnum` |
| `ip_address` | |
| `user_agent`, `browser`, `platform` | raw string plus what `UserAgentParser` could read out of it |

Passwords are never written to the journal in any form.

### Sources (`AuthLogSourceEnum`)

`website`, `admin_panel`, `api` (the mobile app talks to the same endpoints as
any other API client).

Deliberately not `LogSourceEnum`: the change log's enum carries `console` and
`system`, which no sign-in attempt can ever have.

### Actions (`AuthLogActionEnum`)

`login_password`, `social`, `password_reset_request`, `password_change`.

Magic-link sign-in is not implemented in the project, so it has no case here.

### Results (`AuthLogResultEnum`)

`success`, `failure`, `blocked`.

### Where records come from

Writing goes through `AuthLogService` — explicit calls, no event listeners
(`LoginAction` checks the password by hand and fires no `Failed` event, while
Filament fires `Login` on the same guard as the website, which would double
every record):

| Place | Events |
|---|---|
| `UserAuth\Actions\LoginAction` | website and API sign-in: success / failure / blocked |
| `Filament\Pages\Auth\Login` | admin panel sign-in — it never goes through `LoginAction` |
| `UserAuth\Http\Controllers\Web\OAuthController` | social sign-in: success / provider failure |
| `UserAuth\Actions\SendPasswordResetAction` | password reset request (failure = unknown email) |
| `UserAuth\Actions\ResetPasswordAction` | password change through the reset link |
| `Profile\Actions\UpdateUserPasswordAction` | password change from the profile |

`AuthLogSourceResolver` resolves the source. Unlike `LogSourceResolver` it
checks the HTTP request first, because authentication always happens inside a
request and `runningInConsole()` is true under tests. The Filament login page
passes `AuthLogSourceEnum::ADMIN_PANEL` explicitly — Livewire drives it outside
a panel request, so nothing else could tell.

### config/auth_log.php

``` php
return [
    'enabled' => env('AUTH_LOG_ENABLED', true),
    'cooldown_seconds' => env('AUTH_COOLDOWN_SECONDS', 5),
];
```

------------------------------------------------------------------------

## Sign-in cooldown

After a wrong password the next sign-in attempt for the same **login + IP**
pair is rejected for `auth_log.cooldown_seconds` seconds (5 by default). The
rejected attempt is written to the journal with result `blocked`.

- `AuthCooldownService` keeps the state in `RateLimiter`, key
  `auth-cooldown:` + sha1 of the lowercased login and the IP
- the IP is part of the key on purpose: a stranger must not be able to lock
  somebody else's account out, and logins that match no user are covered too
- a successful sign-in clears the key
- `AuthCooldownException` carries the remaining seconds and is turned into a
  normal validation error on the login field, so website, API and admin panel
  all answer in their usual format
- `cooldown_seconds = 0` switches the cooldown off completely

### In tests

The cooldown is **on** in tests. A test that makes several sign-in attempts
for the same login in a row either disables it:

``` php
config(['auth_log.cooldown_seconds' => 0]);
```

or travels past the window with `$this->travel(6)->seconds()`.

------------------------------------------------------------------------

## Retention and cleanup

- Every journal page (change log, authentication log, letters, SMS) has a red
  **Clear journal** header button. It asks for confirmation and wipes the whole
  journal with a single query — irreversible.
- `php artisan logs:prune` deletes records older than
  `config('log.retention_days')` (365 days by default, `LOG_RETENTION_DAYS`)
  from all four journals, in chunks. `--dry-run` only reports the counts.
- The command is scheduled daily at 03:00 in `routes/console.php`.

------------------------------------------------------------------------

---

[Back to main readme](../../README.md)
