<?php

namespace App\Filament\Actions;

use Filament\Actions\EditAction;


class BaseEditAction
{
    public static function make(): EditAction
    {
        return EditAction::make()
            ->iconButton()
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->extraModalFooterActions(fn(EditAction $action, $record) => [

                $action->makeModalSubmitAction(
                    'save',
                    arguments: ['close' => false],
                )->label(__('admin.actions.save')),

                $action->makeModalSubmitAction(
                    'saveAndClose',
                    arguments: ['close' => true],
                )->label(__('admin.actions.saveClose')),

                ViewLogsAction::make()
                    ->record($record)
                    ->outlined(),
            ])
            ->action(function (array $data, array $arguments, $record, $action): void {
                $record->update($data);
                $record = $record->fresh();

                $livewire = $action->getLivewire();

                $mountedActionIndex = array_key_first($livewire->mountedActions ?? []);

                $livewire->mountedActions[$mountedActionIndex]['data'] = $record->attributesToArray();
                if (!($arguments['close'] ?? true)) {
                    $action->callAfter();
                    $action->sendSuccessNotification();
                    $action->halt();
                    return;
                }
            });
    }
}
