<?php

namespace App\Providers;

use App\Filament\Generators\AdminResourceClassGenerator;
use App\Filament\Generators\AdminResourceManageRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceManageRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ResourceClassGenerator::class, AdminResourceClassGenerator::class);
        $this->app->bind(ResourceManageRecordsPageClassGenerator::class, AdminResourceManageRecordsPageClassGenerator::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->translateFilamentLabels();
    }

    //Automatic labels in forms, tables, pop-ups, etc.
    private function translateFilamentLabels(): void
    {
        $configure = function ($component) {
            $name = $component->getName();

            if (!$name) {
                return;
            }

            $name = str($name)->afterLast('.')->toString();

            $key = "admin.fields.$name";

            $translation = __($key);

            if ($translation === $key) {
                return;
            }

            $defaultLabel = (string)str($name)
                ->replace(['_', '-'], ' ')
                ->ucfirst();

            if ($component->getLabel() === $defaultLabel) {
                $component->label($translation);
            }
        };

        Field::configureUsing($configure);

        Column::configureUsing($configure);

        BaseFilter::configureUsing($configure);

        TextEntry::configureUsing($configure);
    }
}
