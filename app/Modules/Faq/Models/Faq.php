<?php

namespace App\Modules\Faq\Models;

use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

/**
 * A question of the «Корисно знати» block: `title` is the question, `text` the answer.
 */
class Faq extends Model
{
    use HasVisibility;

    protected $fillable = [
        'display',
        'rank',
        'title',
        'text',
    ];

    protected $casts = [
        'display' => 'boolean',
    ];
}
