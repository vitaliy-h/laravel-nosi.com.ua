<?php

use App\Modules\Core\Models\ActivityLog;

return [

    'enabled' => env('ACTIVITY_LOG_ENABLED', true),

    'excluded_models' => [
        ActivityLog::class,
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
    // fields that only track "changed", without values. Use this for big fields.
    'only_track_changes' => [
        'dictionary',
    ],
];
