<?php

namespace App\Filament\Builder\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TeasersBlock
{
    public static function make(): Block
    {
        return Block::make('teasers')
            ->label('Teasers')
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

                Repeater::make('items')
                    ->schema([

                        TextInput::make('icon')
                            ->helperText('heroicon name')
                            ->columnSpan(4),

                        TextInput::make('title')
                            ->columnSpan(4),

                        Textarea::make('text')
                            ->columnSpan(4),

                    ])
                    ->columns(12)
                    ->columnSpan(12),

            ])
            ->columns(12);
    }
}
