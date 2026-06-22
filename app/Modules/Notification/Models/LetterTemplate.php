<?php

namespace App\Modules\Notification\Models;

use App\Modules\Notification\Enums\LetterEnum;
use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    protected $fillable = [
        'key',
        'sender',
        'sender_name',
        'receivers',
        'subject',
        'body',
    ];

    protected $casts = [
        'key' => LetterEnum::class,
    ];

    public function getReceiversFormattedAttribute(): array
    {
        return collect(explode(',', $this->receivers))
            ->map(fn ($email) => trim($email))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
