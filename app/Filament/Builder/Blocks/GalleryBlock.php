<?php

namespace App\Filament\Builder\Blocks;

use App\Filament\Builder\BuilderContent;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class GalleryBlock
{
    public static function make(): Block
    {
        return Block::make('gallery')

            ->label(__('admin.blocks.gallery'))

            ->schema([

                TextInput::make('title')
                    ->columnSpan(8),

                Select::make('style')
                    ->options([
                        'default' => __('admin.blocks.style_default'),
                    ])
                    ->default('default')
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->columnSpan(4),

                FileUpload::make('images')
                    ->image()
                    ->directory(BuilderContent::directoryClosure('imgs'))
                    ->getUploadedFileNameForStorageUsing(BuilderContent::fileNameClosure())
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
