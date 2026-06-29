<?php

namespace App\Modules\Core\Traits\Models;

use Illuminate\Support\Str;

trait HasUuid
{
    protected array $uuidFields = ['uuid'];

    public function initializeHasUuid(): void
    {
        foreach ($this->getUuidFields() as $field) {
            if (empty($this->{$field})) {
                $this->{$field} = (string) Str::uuid();
            }
        }
    }

    public function getUuidFields(): array
    {
        return $this->uuidFields ?? ['uuid'];
    }
}
