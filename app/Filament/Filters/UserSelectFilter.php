<?php

namespace App\Filament\Filters;

use App\Modules\Core\Models\User;
use Filament\Tables\Filters\SelectFilter;

/**
 * Table filter by user with dropdown search by name, surname, email and phone;
 * options are shown as "Surname Name". Pairs with App\Filament\Forms\UserSelect.
 */
class UserSelectFilter
{
    public static function make(string $name = 'user_id'): SelectFilter
    {
        return SelectFilter::make($name)
            ->label(__('admin.fields.user_id'))
            ->relationship('user', 'name')
            ->getOptionLabelFromRecordUsing(fn (User $record): string => $record->select_label)
            ->searchable(['name', 'surname', 'email', 'phone'])
            ->preload();
    }
}
