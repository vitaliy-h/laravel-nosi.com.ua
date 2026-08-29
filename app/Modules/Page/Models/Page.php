<?php

namespace App\Modules\Page\Models;

use App\Modules\Page\Enums\PageModule;
use App\Modules\Core\Traits\Models\HasBuilderContent;
use App\Modules\Core\Traits\Models\HasSeoFields;
use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;
    use HasBuilderContent;
    use HasVisibility;
    use HasSeoFields;

    protected $fillable = [
        'title',
        'slug',
        'module',
        'rank',
        'display',
        'display_menu',
        'display_menu_footer',
        'meta_title',
        'meta_description',
        'is_indexable',
        'content'
    ];

    protected $casts = [
        'display' => 'boolean',
        'display_menu' => 'boolean',
        'display_menu_footer' => 'boolean',
        'module' => PageModule::class,
        'content'=>'array'
    ];
}
