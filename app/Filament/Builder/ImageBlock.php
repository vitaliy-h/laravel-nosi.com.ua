<?php

namespace App\Filament\Builder;

use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ImageBlock
{
    public static function make(
        Closure|string|null $directory = null
    ): Block
    {
        return Block::make('image')

            ->label('Image')

            ->schema([

                FileUpload::make('image')
                    ->image()
                    ->directory($directory)
                    ->visibility('public')
                    ->required()
                    ->columnSpan(4),

                TextInput::make('title')
                    ->columnSpan(4),

                Select::make('style')
                    ->options([
                        'default' => 'Default',
                    ])
                    ->default('default')
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->columnSpan(4),

            ])

            ->columns(12);
    }
}
