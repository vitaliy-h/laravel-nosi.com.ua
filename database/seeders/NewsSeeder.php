<?php

namespace Database\Seeders;

use App\Modules\News\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            News::updateOrCreate([
                'slug' => Str::slug("Test news {$i}")
            ],[
                'title' => "Test news {$i}",
                'short_text' => "Short description for news {$i}",
                'text' => "Full content of test news {$i}. Lorem ipsum dolor sit amet...",
                'display' => rand(0, 1),
                'published_at' => now()->subDays(rand(0, 30)),
                'meta_title' => "Test news {$i}",
                'meta_description' => "Short description for news {$i}",
            ]);
        }
    }
}
