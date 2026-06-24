<?php

namespace App\Filament\Support;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Kalnoy\Nestedset\NodeTrait;

class NestedSetFields
{
    /**
     * Returns [parent_id Select, _position_id Select] for any model using NodeTrait.
     */
    public static function make(
        string $modelClass,
        int $columnSpan = 6,
        string $labelField = 'title',
    ): array {
        return [
            Select::make('parent_id')
                ->options(
                    fn () => $modelClass::defaultOrder()
                        ->withDepth()
                        ->get()
                        ->toFlatTree()
                        ->mapWithKeys(fn ($node) => [
                            $node->id => str_repeat('— ', $node->depth) . $node->{$labelField},
                        ])
                )
                ->disableOptionWhen(function ($value, $record) {
                    if (!$record) {
                        return false;
                    }

                    return $value == $record->id
                        || $record->descendants->pluck('id')->contains($value);
                })
                ->live()
                ->searchable()
                ->preload()
                ->nullable()
                ->columnSpan($columnSpan),

            Select::make('_position_id')
                ->options(function (callable $get, $record) use ($modelClass, $labelField) {
                    $parentId = $get('parent_id');

                    return $modelClass::query()
                        ->when(
                            $parentId,
                            fn ($q) => $q->where('parent_id', $parentId),
                            fn ($q) => $q->whereNull('parent_id'),
                        )
                        ->defaultOrder()
                        ->get()
                        ->mapWithKeys(fn ($node) => [
                            $node->id => $node->{$labelField},
                        ]);
                })
                ->afterStateHydrated(function ($state, callable $set, $record) {
                    if ($record && !$state) {
                        $set('_position_id', $record->id);
                    }
                })
                ->searchable()
                ->preload()
                ->live()
                ->nullable()
                ->columnSpan($columnSpan),
        ];
    }

    /**
     * CreateAction pre-wired with position handling. Drop-in for CreateAction::make()
     * in toolbarActions() of ManageRecords pages that use NestedSetFields.
     */
    public static function createAction(): CreateAction
    {
        return CreateAction::make()
            ->after(function ($record, array $data) {
                static::applyPosition($record, $data);
            });
    }

    /**
     * Move $record before the sibling indicated by $data['_position_id'].
     * No-op when _position_id is absent or equals the record itself.
     */
    public static function applyPosition(mixed $record, array $data): void
    {
        $positionId = $data['_position_id'] ?? null;

        if (!$positionId || (int) $positionId === $record->id) {
            return;
        }

        $sibling = $record::find($positionId);
        if ($sibling) {
            $record->fresh()->beforeNode($sibling)->save();
        }
    }
}
