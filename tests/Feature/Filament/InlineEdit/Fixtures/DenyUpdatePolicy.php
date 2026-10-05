<?php

namespace Tests\Feature\Filament\InlineEdit\Fixtures;

/**
 * Lets the admin see records but never edit them — for the edit gate of InlineEditColumn.
 */
class DenyUpdatePolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function update(): bool
    {
        return false;
    }
}
