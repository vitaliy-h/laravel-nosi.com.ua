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
            $this->call(\App\Modules\News\Database\Seeders\NewsSeeder::class);
        }

        if (config('modules.shop')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $this->call(\App\Modules\Shop\Database\Seeders\ShopCategorySeeder::class);
            $this->call(\App\Modules\Shop\Database\Seeders\ShopBrandSeeder::class);
            $this->call(\App\Modules\Shop\Database\Seeders\ShopProductSeeder::class);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->call(\App\Modules\Shop\Database\Seeders\ShopOrderSeeder::class);
        }

        if (config('modules.geo')) {
            $this->call(\App\Modules\Geo\Database\Seeders\GeoTablesSeeder::class);
        }
    }
}
