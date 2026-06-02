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
        $this->call(NewsSeeder::class);
        $this->call(LanguageSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->call(ShopCategorySeeder::class);
        $this->call(ShopBrandSeeder::class);
        $this->call(ShopProductSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->call(LetterTemplateSeeder::class);
        $this->call(GeoTablesSeeder::class);
    }
}
