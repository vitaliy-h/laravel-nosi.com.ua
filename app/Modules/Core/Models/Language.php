<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\Localization;
use App\Modules\Core\Services\TranslationService;
use App\Modules\Core\Traits\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasVisibility;

    protected $fillable = [
        'title',
        'localization',
        'dictionary',
        'rank',
        'display',
    ];

    protected $casts = [
        'dictionary' => 'array',
        'display' => 'boolean',
        'localization' => Localization::class,
    ];

    protected static function booted(): void
    {
        static::saved(function (Language $language) {
            app(TranslationService::class)
                ->clearCache($language->localization);
        });

        static::deleted(function (Language $language) {
            app(TranslationService::class)
                ->clearCache($language->localization);
        });
    }
}
