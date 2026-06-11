<?php

namespace App\Filament\Builder;

use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class ImageBlock
{
    public static function make(
        Closure|string|null $basePath = null
    ): Block
    {
        return Block::make('image')

            ->label('Image')

            ->schema([

                FileUpload::make('image')
                    ->image()
                    ->directory(function ($component, $record) use ($basePath) {
                        $base = $basePath instanceof Closure
                            ? $basePath($record)
                            : ($basePath ?? 'pages');
                        $parts = explode('.', $component->getStatePath());
                        $blockUuid = collect($parts)->first(fn($p) => Str::isUuid($p)) ?? Str::uuid();
                        return $base . '/' . $blockUuid . '/img';
                    })
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        $stem = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                        $ext = strtolower($file->getClientOriginalExtension());
                        return $stem . '.' . $ext;
                    })
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
