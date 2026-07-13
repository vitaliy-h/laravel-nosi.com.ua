<?php

namespace App\Filament\Pages\DebugTools;

use App\Filament\Support\ArtisanCommandRunner;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class DebugTools extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-command-line';

    public static function getNavigationLabel(): string
    {
        return __('admin.debug_tools.navigation_label');
    }

    public function getTitle(): string
    {
        return __('admin.debug_tools.navigation_label');
    }

    public ?string $lastCommand = null;

    public ?string $output = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tools')
                    ->tabs([
                        Tab::make(__('admin.debug_tools.artisan_tab'))
                            ->icon('heroicon-o-command-line')
                            ->schema($this->getArtisanTabSchema()),
                    ]),
            ]);
    }

    public function viewOutputAction(): Action
    {
        return Action::make('viewOutput')
            ->label(__('admin.debug_tools.output'))
            ->modalHeading(fn (): string => __('admin.debug_tools.output_modal_heading', ['command' => $this->lastCommand]))
            ->modalContent(fn () => view('filament.pages.debug-tools.output', ['output' => $this->output]))
            ->modalWidth('2xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('admin.actions.close'));
    }

    /**
     * @return array<Section>
     */
    protected function getArtisanTabSchema(): array
    {
        return collect(ArtisanCommandRunner::COMMANDS)
            ->map(function (array $meta, string $key): Section {
                $runAction = Action::make("run-$key")
                    ->label(__('admin.actions.run'))
                    ->size('sm')
                    ->action(fn () => $this->runCommand($key));

                if ($meta['destructive']) {
                    $runAction
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.debug_tools.confirm_heading', ['command' => $key]))
                        ->modalDescription(__('admin.debug_tools.confirm_description'));
                } else {
                    $runAction->color('gray');
                }

                return Section::make($key)
                    ->description(__("admin.debug_tools.commands.$key"))
                    ->compact()
                    ->headerActions([$runAction]);
            })
            ->values()
            ->all();
    }

    public function runCommand(string $command): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(ArtisanCommandRunner::isAllowed($command), 422);

        $this->lastCommand = $command;
        $this->output = app(ArtisanCommandRunner::class)->run($command, auth()->user());

        Notification::make()
            ->title(__('admin.debug_tools.executed_notification', ['command' => $command]))
            ->success()
            ->send();

        $this->replaceMountedAction('viewOutput');
    }
}
