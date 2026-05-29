<?php

namespace App\Modules\Core\Observers;

use App\Modules\Core\Services\LogService;
use Illuminate\Database\Eloquent\Model;

class LogObserver
{
    public function created(Model $model): void
    {
        app(LogService::class)
            ->logCreated($model);
    }

    public function updated(Model $model): void
    {
        app(LogService::class)
            ->logUpdated($model);
    }

    public function deleting(Model $model): void
    {
        app(LogService::class)
            ->logDeleted($model);
    }
}
