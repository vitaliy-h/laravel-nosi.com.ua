<?php

namespace App\Filament\Builder;

use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class GalleryBlock
{
    public static function make(
        Closure|string|null $directory = null
    ): Block
    {
        return Block::make('gallery')

            ->label('Gallery')

            ->schema([

                TextInput::make('title')
                    ->columnSpan(8),

                Select::make('style')
                    ->options([
                        'default' => 'Default',
                    ])
                    ->default('default')
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->columnSpan(4),

                FileUpload::make('images')
                    ->image()
                    ->directory($directory)
                    ->visibility('public')
                    ->multiple()
                    ->reorderable()
                    ->panelLayout('grid')
                    ->imagePreviewHeight('180')
                    ->columnSpan(12),

            ])

            ->columns(12);
    }
}
