<?php

namespace Tests\Feature\Filament\InlineEdit\Fixtures;

use App\Filament\Tables\Columns\InlineEditColumn;
use App\Modules\Shop\Models\Product;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

/**
 * A bare table outside any resource: the InlineEditColumn modes no resource
 * uses yet (a json-array multiselect, a date), and the "no resource" branch
 * of the edit gate.
 */
class InlineEditTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query())
            ->columns([
                InlineEditColumn::make('imgs')
                    ->multiple()
                    ->options(['a.jpg' => 'A', 'b.jpg' => 'B', 'c.jpg' => 'C']),
                InlineEditColumn::make('created_at')
                    ->editAsDate()
                    ->rules(['required', 'date'])
                    ->date(),
            ]);
    }

    public function render(): string
    {
        return '<div>{{ $this->table }}</div>';
    }
}
