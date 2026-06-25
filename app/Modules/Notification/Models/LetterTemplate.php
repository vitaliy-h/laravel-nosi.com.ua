<?php

namespace App\Modules\Notification\Models;

use App\Modules\Notification\Enums\LetterEnum;
use Database\Factories\LetterTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    /** @use HasFactory<LetterTemplateFactory> */
    use HasFactory;

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
