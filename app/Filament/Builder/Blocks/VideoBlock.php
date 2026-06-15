<?php

namespace App\Filament\Builder\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;

class VideoBlock
{
    public static function make(): Block
    {
        return Block::make('video')
            ->label('Video')
            ->schema([
                TextInput::make('url')
                    ->url()
                    ->required(),
            ]);
    }
}
