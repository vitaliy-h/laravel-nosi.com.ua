<?php

namespace App\Modules\Core\Traits\Models;

trait CascadesDeletes
{
    public function getCascadeDeletes(): array
    {
        return $this->cascadeDeletes ?? [];
    }
}
