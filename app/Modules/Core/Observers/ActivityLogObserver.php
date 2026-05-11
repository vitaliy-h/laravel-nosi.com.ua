<?php

namespace App\Modules\Core\Observers;

use App\Modules\Core\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    public function created(Model $model): void
    {
        app(ActivityLogService::class)
            ->logCreated($model);
    }

    public function updated(Model $model): void
    {
        app(ActivityLogService::class)
            ->logUpdated($model);
    }

    public function deleted(Model $model): void
    {
        app(ActivityLogService::class)
            ->logDeleted($model);
    }
}
