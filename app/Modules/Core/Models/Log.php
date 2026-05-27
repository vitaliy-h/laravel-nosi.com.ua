<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\LogEventEnum;
use App\Modules\Core\Enums\LogSourceEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Log extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'changed_fields',
        'source',
        'ip_address',
        'batch_uuid',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'event' => LogEventEnum::class,
        'source' => LogSourceEnum::class,
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
