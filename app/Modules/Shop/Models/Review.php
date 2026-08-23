<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Models\User;
use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    use HasVisibility;

    protected $table = 'shop_reviews';

    protected $fillable = [
        'display',
        'product_id',
        'user_id',
        'rating',
        'name',
        'email',
        'text',
        'ip',
        // Admins may backdate a review from the Filament form.
        'created_at',
    ];

    protected $casts = [
        'display' => 'boolean',
        'rating' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Display name of the review author: the account name wins over the name
     * typed into the public form.
     */
    public function getAuthorNameAttribute(): ?string
    {
        return $this->user?->name ?: $this->name;
    }

    protected static function newFactory(): ReviewFactory
    {
        return ReviewFactory::new();
    }
}
