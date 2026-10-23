<?php

namespace App\Modules\Review\Models;

use App\Modules\Core\Traits\Models\HasVisibility;
use App\Modules\User\Models\User;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A review of the shop as a whole (not of a product) — the home page block.
 */
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    use HasVisibility;

    protected $fillable = [
        'display',
        'user_id',
        'rating',
        'name',
        'city',
        'email',
        'text',
        'ip',
        // Admins may backdate a review from the Filament form.
        'created_at',
    ];

    protected $casts = [
        'display' => 'boolean',
        'rating'  => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Display name of the review author: the name typed into the form; the
     * account name only fills in for a review saved without one.
     */
    protected function authorName(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->name ?: $this->user?->name,
        );
    }

    protected static function newFactory(): ReviewFactory
    {
        return ReviewFactory::new();
    }
}
