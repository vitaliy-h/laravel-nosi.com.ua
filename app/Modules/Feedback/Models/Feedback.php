<?php

namespace App\Modules\Feedback\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'processed',
        'ip',
        'name',
        'email',
        'message',
    ];
}
