<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasVisibility;

    protected $fillable = [
        'title',
        'slug',
        'text',
        'module',
        'rank',
        'display',
        'display_menu',
        'display_menu_footer',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'display' => 'boolean',
        'display_menu' => 'boolean',
        'display_menu_footer' => 'boolean',
        'module' => PageModule::class,
    ];
}
