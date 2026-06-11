<?php

namespace App\Filament\Builder;

use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class GalleryBlock
{
    public static function make(
        Closure|string|null $basePath = null
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
                    ->directory(function ($component, $record) use ($basePath) {
                        $base = $basePath instanceof Closure
                            ? $basePath($record)
                            : ($basePath ?? 'pages');
                        $parts = explode('.', $component->getStatePath());
                        $blockUuid = collect($parts)->first(fn($p) => Str::isUuid($p)) ?? Str::uuid();
                        return $base . '/' . $blockUuid . '/imgs';
                    })
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        $imageKey = (string) Str::uuid();
                        $stem = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                        $ext = strtolower($file->getClientOriginalExtension());
                        return $imageKey . '/' . $stem . '.' . $ext;
                    })
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
