<?php

namespace App\Filament\Builder;

use App\Filament\Support\BuilderContent;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ImageBlock
{
    public static function make(): Block
    {
        return Block::make('image')

            ->label('Image')

            ->schema([

                FileUpload::make('image')
                    ->image()
                    ->directory(BuilderContent::directoryClosure('img'))
                    ->getUploadedFileNameForStorageUsing(BuilderContent::fileNameClosure())
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
