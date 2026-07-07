<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        if (config('modules.news')) {
            $this->call(\App\Modules\News\database\seeders\NewsSeeder::class);
        }

        if (config('modules.shop')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $this->call(\App\Modules\Shop\database\seeders\ShopCategorySeeder::class);
            $this->call(\App\Modules\Shop\database\seeders\ShopBrandSeeder::class);
            $this->call(\App\Modules\Shop\database\seeders\ShopProductSeeder::class);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->call(\App\Modules\Shop\database\seeders\ShopOrderSeeder::class);
        }

        if (config('modules.geo')) {
            $this->call(\App\Modules\Geo\database\seeders\GeoTablesSeeder::class);
        }
    }
}
