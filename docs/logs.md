# Activity Logs System

## Overview

The Activity Logs system provides a centralized audit trail for all
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

- ActivityLogService -- main service responsible for writing logs
- ActivityLog model -- database representation
- ActivityLogSourceResolver -- detects source (admin/api/console)
- config/activity-log.php -- configuration rules

------------------------------------------------------------------------

## Logging Flow

All logging is handled automatically via observers or service calls:

### 1. Created

ActivityLogService::logCreated(\$model);

Stores: - new values only - changed fields

------------------------------------------------------------------------

### 2. Updated

ActivityLogService::logUpdated(\$model);

Stores: - old values - new values - changed fields

------------------------------------------------------------------------

### 3. Deleted

ActivityLogService::logDeleted(\$model);

Stores: - old values only - changed fields

------------------------------------------------------------------------

## Field Filtering Rules

The system supports field-level logging policies via config.

### config/activity-log.php

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
