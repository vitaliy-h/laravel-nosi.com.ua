<?php

namespace App\Policies;

use App\Modules\User\Models\User;

class UserPolicy
{
    /**
     * Super Admin accounts are invisible/untouchable to everyone except other Super Admins.
     * Gate::before already grants Super Admins full access, so these checks only run for non-Super-Admins.
     */
    public function view(User $user, User $model): bool
    {
        return !$model->isSuperAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return !$model->isSuperAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return !$model->isSuperAdmin();
    }

    public function restore(User $user, User $model): bool
    {
        return !$model->isSuperAdmin();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return !$model->isSuperAdmin();
    }
}
