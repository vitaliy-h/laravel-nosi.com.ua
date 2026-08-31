<?php

use App\Modules\Core\Models\AuthLog;
use App\Modules\Core\Models\Log;

return [

    'enabled' => env('LOG_ENABLED', true),

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
