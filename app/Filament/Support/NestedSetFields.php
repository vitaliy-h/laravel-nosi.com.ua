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
                ->afterStateUpdated(fn (callable $set) => $set('_position_id', 'end_of_list'))
                ->searchable()
                ->preload()
                ->nullable()
                ->columnSpan($columnSpan),

            Select::make('_position_id')
                ->options(function (callable $get, $record) use ($modelClass, $labelField) {
                    $parentId = $get('parent_id');

                    $siblings = $modelClass::query()
                        ->when(
                            $parentId,
                            fn ($q) => $q->where('parent_id', $parentId),
                            fn ($q) => $q->whereNull('parent_id'),
                        )
                        ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                        ->defaultOrder()
                        ->get()
                        ->mapWithKeys(fn ($node) => [
                            $node->id => $node->{$labelField},
                        ])
                        ->toArray();

                    return $siblings + ['end_of_list' => __('admin.fields._position_end_of_list')] ;
                })
                ->afterStateHydrated(function ($state, callable $set, $record) {
                    if (!$state) {
                        if ($record) {
                            $nextSibling = $record->nextSiblings()->defaultOrder()->first();
                            $set('_position_id', $nextSibling ? (string) $nextSibling->id : 'end_of_list');
                        } else {
                            $set('_position_id', 'end_of_list');
                        }
                    }
                })
                ->selectablePlaceholder(false)
                ->preload()
                ->live()
                ->default('end_of_list')
                ->key(fn (callable $get) => 'position_id_' . ($get('parent_id') ?? 'root'))
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

        if (!$positionId || $positionId === 'end_of_list') {
            $parentId = $record->fresh()->parent_id;
            $lastSibling = $record::query()
                ->when(
                    $parentId,
                    fn ($q) => $q->where('parent_id', $parentId),
                    fn ($q) => $q->whereIsRoot(),
                )
                ->defaultOrder()
                ->where('id', '!=', $record->id)
                ->get()
                ->last();

            if ($lastSibling) {
                $record->fresh()->afterNode($lastSibling)->save();
            }

            return;
        }

        if ((int) $positionId === $record->id) {
            return;
        }

        $sibling = $record::find($positionId);
        if ($sibling) {
            $record->fresh()->beforeNode($sibling)->save();
        }
    }
}
