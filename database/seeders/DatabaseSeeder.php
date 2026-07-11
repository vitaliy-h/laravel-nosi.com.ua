<?php

namespace Database\Seeders;

use App\Modules\Core\Support\ModuleGate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(LetterTemplateSeeder::class);
        $this->call(SmsTemplateSeeder::class);

        $this->callModuleSeeders();
    }

    /**
     * Each module owning demo data exposes a {Module}Seeder that calls its
     * own sub-seeders in the required order. Disabled modules (and modules
     * whose dependency is disabled, e.g. ShopOrders/ShopCart without Shop)
     * are skipped via ModuleGate so a fully disabled module's seeder never
     * touches its (possibly deleted) tables.
     */
    private function callModuleSeeders(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $module) {
            $moduleName = basename($module);

            if (!ModuleGate::isEnabled(Str::snake($moduleName))) {
                continue;
            }

            $seederClass = "App\\Modules\\{$moduleName}\\database\\seeders\\{$moduleName}Seeder";

            if (class_exists($seederClass)) {
                $this->call($seederClass);
            }
        }
    }
}
