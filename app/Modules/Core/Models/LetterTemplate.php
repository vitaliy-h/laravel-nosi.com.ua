<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\LetterEnum;
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
