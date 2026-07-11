# Logs System

## Overview

The Logs system provides a centralized audit trail for all
changes made within the application.

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
  "model_type": "App\Models\Post",
  "model_id": 123,
  "old_values": {},
  "new_values": {},
  "changed_fields": [],
  "source": "admin|api|console",
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
    'enabled' => true,

    'excluded_models' => [],

    'excluded_tables' => [],

    'ignored_attributes' => [
        'updated_at',
        'created_at',
    ],

    'only_track_changes' => [
        'dictionary',
    ],
];
```

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

When the parent model is deleted:

1. Each relation listed in `$cascadeDeletes` is iterated
2. Every child model is deleted via `->delete()` (Eloquent, not DB cascade)
3. Eloquent fires `deleting` event on each child → LogObserver logs the deletion
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

admin, api, console, client

---

[Back to main readme](./README.md)
