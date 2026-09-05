<?php

namespace App\Filament\Traits;

use App\Filament\Actions\BaseEditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

trait HasDefaultTable
{
    protected static function applyDefaultTable(Table $table): Table
    {
        return $table
            ->recordActions([
                BaseEditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeColumns)

            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
