<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogResultEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\User\Models\User;
use Database\Factories\AuthLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthLog extends Model
{
    /** @use HasFactory<AuthLogFactory> */
    use HasFactory;

    protected $table = 'log_auth';

    protected $fillable = [
        'user_id',
        'login',
        'source',
        'action',
        'result',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
    ];

    protected $casts = [
        'source' => AuthLogSourceEnum::class,
        'action' => AuthLogActionEnum::class,
        'result' => AuthLogResultEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
