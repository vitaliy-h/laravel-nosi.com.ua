<?php

namespace App\Modules\Profile\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;

class ProfileEditPageViewModel extends BaseViewModel
{
    public function __construct(
        public readonly ProfileUserViewModel $user,
        public readonly string $active_section = 'edit',
    ) {}
}
