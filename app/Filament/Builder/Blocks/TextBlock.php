<?php

namespace App\Filament\Builder\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class TextBlock
{
    public static function make(): Block
    {
        return Block::make('text')
            ->label(__('admin.blocks.text'))
            // ->icon('heroicon-o-bars-3-bottom-left') ??
            ->schema([

                TextInput::make('title')
                    ->columnSpan(8),

                Select::make('style')
                    ->options([
                        'default' => __('admin.blocks.style_default'),
                        // 'gray' => 'Gray Background',
                    ])
                    ->default('default')
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->columnSpan(4),

                RichEditor::make('content')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'bulletList',
                        'orderedList',
                        'link',
                        'blockquote',
                    ])
                    ->columnSpanFull(),

            ])
            ->columns(12);
        /* ??
        ->preview(fn (?array $state): string =>
            $state['title']
            ?? str(strip_tags($state['content'] ?? ''))
            ->limit(40)
        );
        */
    }
}
