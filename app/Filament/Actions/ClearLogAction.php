<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * Header action shared by every journal page: wipes the whole journal.
 * Deletes with a single query — journals grow large, models are never loaded.
 */
class ClearLogAction
{
    /**
     * @param  class-string<Model>  $model
     */
    public static function make(string $model): Action
    {
        return Action::make('clear_log')
            ->label(__('admin.actions.clear_log'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('admin.messages.clear_log_heading'))
            ->modalDescription(__('admin.messages.clear_log_description'))
            ->action(function () use ($model): void {

                $count = $model::query()->delete();

                Notification::make()
                    ->title(__('admin.messages.clear_log_done', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }
}
