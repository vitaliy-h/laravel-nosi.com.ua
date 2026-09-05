<?php

namespace App\Filament\Traits;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

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
}
