<?php

namespace App\Modules\Core\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'surname', 'email', 'phone', 'password', 'ip', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super_admin') || $this->can('admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function socialProfiles(): HasMany
    {
        return $this->hasMany(SocialProfile::class);
    }

    public function cart(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Modules\ShopCart\Models\Cart::class);
    }

    public function getFormattedNameAttribute()
    {
        return $this->name;
    }

    /**
     * User label for dropdowns/lists: "Surname Name", or email / id when they
     * are missing. Shared by Filament selects and filters.
     */
    public function getSelectLabelAttribute(): string
    {
        return trim(($this->surname ?? '').' '.($this->name ?? ''))
            ?: ($this->email ?? (string) $this->getKey());
    }
}
