<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\LogEventEnum;
use App\Modules\Core\Enums\LogSourceEnum;
use App\Modules\User\Models\User;
use Database\Factories\LogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Log extends Model
{
    /** @use HasFactory<LogFactory> */
    use HasFactory;

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
    ];

    protected $casts = [
        'old_values'     => 'array',
        'new_values'     => 'array',
        'changed_fields' => 'array',
        'event'          => LogEventEnum::class,
        'source'         => LogSourceEnum::class,
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
