<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\NeedsAttentionWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

/**
 * Admin dashboard with a global period filter.
 *
 * Extends the stock Filament dashboard, so the slug and the route name
 * (`filament.admin.pages.dashboard`) stay the same and the navigation item in
 * AdminPanelProvider keeps working.
 */
class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getNeedsAttentionContentComponent(),
            $this->getFiltersFormContentComponent(),
            $this->getWidgetsContentComponent(),
        ]);
    }

    /**
     * Kept out of getWidgets() so it sits above the period filter — it is a
     * to-do list, not a statistic, and the period must not apply to it.
     *
     * With nothing pending the grid is dropped whole: an empty widget still
     * takes a row of its own here, and fi-page-content puts a gap around it.
     */
    public function getNeedsAttentionContentComponent(): Component
    {
        return Grid::make(1)
            ->visible(fn (): bool => NeedsAttentionWidget::hasAnything())
            ->schema(fn (): array => $this->getWidgetsSchemaComponents([NeedsAttentionWidget::class]));
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return array_values(array_filter(
            parent::getWidgets(),
            fn (string|WidgetConfiguration $widget): bool => $widget !== NeedsAttentionWidget::class,
        ));
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('period')
                ->label(__('admin.dashboard.filters.period'))
                ->options([
                    'today'  => __('admin.dashboard.periods.today'),
                    '7'      => __('admin.dashboard.periods.last_7_days'),
                    '30'     => __('admin.dashboard.periods.last_30_days'),
                    '90'     => __('admin.dashboard.periods.last_90_days'),
                    'custom' => __('admin.dashboard.periods.custom'),
                ])
                ->default('30')
                ->selectablePlaceholder(false)
                ->live(),
            DatePicker::make('from')
                ->label(__('admin.dashboard.filters.from'))
                ->visible(fn (Get $get): bool => $get('period') === 'custom')
                ->maxDate(now()),
            DatePicker::make('to')
                ->label(__('admin.dashboard.filters.to'))
                ->visible(fn (Get $get): bool => $get('period') === 'custom')
                ->maxDate(now()),
        ]);
    }
}
