<?php

namespace App\Modules\Core\Traits\Models;

trait HasVisibility
{
    private function visibility($query, $visible = null)
    {
        if ($visible !== null) {
            return $query->where('display', (bool)$visible);
        }
        //
        return $query;
    }

    public function scopeVisibleAll($query)
    {
        return $this->visibility($query, null);
    }

    public function scopeVisible($query)
    {
        return $this->visibility($query, true);
    }

    public function scopeInvisible($query)
    {
        return $this->visibility($query, false);
    }
}
