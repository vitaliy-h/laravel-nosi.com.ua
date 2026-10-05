<?php

namespace App\Filament\Traits;

use App\Filament\Tables\Columns\InlineEditColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

trait HasSeoFields
{
    protected static function getSeoSection(bool $hasIndexable = false): Section
    {
        $schema = [
            Toggle::make('generate_seo')
                ->columnSpan(3)
                ->inline(false)
                ->default(fn (string $operation) => $operation === 'create')
                ->dehydrated(),

            TextInput::make('slug')
                ->columnSpan(9)
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('meta_title')
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('meta_description')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull(),
        ];

        if ($hasIndexable) {
            $schema[] = Toggle::make('is_indexable')
                ->columnSpan(12)
                ->default(true);
        }

        return Section::make('SEO')
            ->schema($schema)
            ->columns(12)
            ->collapsed()
            ->collapsible()
            ->columnSpanFull();
    }

    /**
     * The table's `slug` column, editable in place with the same rules as the
     * SEO section's field. Changing it moves the record's public URL.
     */
    protected static function getSlugColumn(): InlineEditColumn
    {
        return InlineEditColumn::make('slug')
            ->searchable()
            ->rules(fn (Model $record): array => [
                'max:255',
                Rule::unique($record::class, 'slug')->ignore($record),
            ]);
    }
}
