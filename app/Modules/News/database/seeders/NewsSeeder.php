<?php

namespace App\Modules\News\database\seeders;

use App\Modules\News\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        News::truncate();
        Storage::disk('public')->deleteDirectory('news');

        for ($i = 1; $i <= 10; $i++) {
            $sourceFile = public_path("demo/assets/img/product/product-{$i}.webp");
            $filename = Str::uuid() . '.webp';
            $dir = 'news/' . $i . '/img/';
            Storage::disk('public')->makeDirectory($dir);
            Storage::disk('public')->put(
                $dir . $filename,
                File::get($sourceFile)
            );

            News::create([
                'title'            => "Test news {$i}",
                'slug'             => Str::slug("Test news {$i}"),
                'short_text'       => "Short description for news {$i}",
                'display'          => rand(0, 1),
                'published_at'     => now()->subDays(rand(0, 30)),
                'meta_title'       => "Test news {$i}",
                'meta_description' => "Short description for news {$i}",
                'img'              => $dir . $filename,
                'content'          => [
                    [
                        'type' => 'text',
                        'data' => [
                            'style'   => 'default',
                            'title'   => null,
                            'content' => "<p>Full content of test news {$i}. Lorem ipsum dolor sit amet...</p>",
                        ],
                    ],
                ],
            ]);
        }
    }
}
