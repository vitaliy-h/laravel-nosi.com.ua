<?php

namespace App\Modules\Subscriber\Models;

use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory;

    protected $table = 'subscribers';

    protected $fillable = [
        'email',
        'ip',
        'integrated_at',
        'error',
    ];

    protected $casts = [
        'integrated_at' => 'datetime',
    ];
}
