<?php

namespace App\Filament\Generators;

use App\Filament\Actions\BaseEditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Filament\Tables\Enums\RecordActionsPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Nette\PhpGenerator\Literal;

class AdminResourceClassGenerator extends ResourceClassGenerator
{
    public function generateTableMethodBody(?string $model = null, array $exceptColumns = []): string
    {
        $this->importUnlessPartial(BulkActionGroup::class);

        $recordTitleAttributeOutput = '';

        if (filled($recordTitleAttribute = $this->getRecordTitleAttribute())) {
            $recordTitleAttributeOutput = new Literal(<<<'PHP'

                ->recordTitleAttribute(?)
            PHP, [$recordTitleAttribute]);
        }

        if (filled($headerActionsOutput = $this->outputTableHeaderActions())) {
            $headerActionsOutput = <<<PHP

                ->headerActions([
                    {$headerActionsOutput}
                ])
            PHP;
        }

        $modifyQueryOutput = '';

        if ($this->isSoftDeletable() && $this->hasTableModifyQueryForSoftDeletes()) {
            $this->namespace->addUse(Builder::class);
            $this->namespace->addUse(SoftDeletingScope::class);

            $modifyQueryOutput = <<<PHP

                ->modifyQueryUsing(fn ({$this->simplifyFqn(Builder::class)} \$query) => \$query
                    ->withoutGlobalScopes([
                        {$this->simplifyFqn(SoftDeletingScope::class)}::class,
                    ]))
            PHP;
        }

        return <<<PHP
            return \$table{$recordTitleAttributeOutput}
                ->columns([
                    {$this->outputTableColumns($model, $exceptColumns)}
                ])
                ->filters([
                    {$this->outputTableFilters()}
                ]){$headerActionsOutput}
                ->recordActions([
                    {$this->outputTableActions()}
                ], position: {$this->simplifyFqn(RecordActionsPosition::class)}::BeforeColumns)
                ->toolbarActions([
                {$this->simplifyFqn(CreateAction::class)}::make(),
                    {$this->simplifyFqn(BulkActionGroup::class)}::make([
                        {$this->outputTableMethodBulkActions()}
                    ]),
                ]){$modifyQueryOutput};
            PHP;
    }

    public function outputTableActions(): string
    {
        return implode(PHP_EOL . '        ', array_map(
            fn(string $action) => "{$this->simplifyFqn($action)}::make()->iconButton(),",
            $this->getTableActions(),
        ));
    }

    public function getTableActions(): array
    {
        $actions = [];

        if ($this->hasViewOperation()) {
            $actions[] = ViewAction::class;
        }

        $actions[] = BaseEditAction::class;

        if ($this->hasAssociateTableActions()) {
            $actions[] = DissociateAction::class;
        }

        if ($this->hasAttachTableActions()) {
            $actions[] = DetachAction::class;
        }

        if ($this->hasDeleteTableActions()) {
            $actions[] = DeleteAction::class;

            if ($this->isSoftDeletable()) {
                $actions[] = ForceDeleteAction::class;
                $actions[] = RestoreAction::class;
            }
        }

        foreach ($actions as $action) {
            $this->importUnlessPartial($action);
        }

        return $actions;
    }
}
