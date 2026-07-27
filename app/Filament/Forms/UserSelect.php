<?php

namespace App\Filament\Forms;

use App\Modules\Core\Models\User;
use Filament\Forms\Components\Select;

/**
 * User select for forms: searchable in the dropdown by name, surname, email and
 * phone; options are shown as "Surname Name". The caller adds columnSpan/
 * required etc. Pairs with App\Filament\Filters\UserSelectFilter (for tables).
 */
class UserSelect
{
    public static function make(string $name = 'user_id'): Select
    {
        return Select::make($name)
            ->label(__('admin.fields.user_id'))
            ->relationship('user', 'name')
            ->getOptionLabelFromRecordUsing(fn (User $record): string => $record->select_label)
            ->searchable(['name', 'surname', 'email', 'phone'])
            ->preload();
    }
}
