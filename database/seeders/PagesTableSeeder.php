<?php

namespace Database\Seeders;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;
use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Main Page',
                'slug' => '/',
                'text' => '<h5>Main Page</h5>',
                'module' => PageModule::INDEX,
                'display' => true,
                'display_menu' => false,
                'display_menu_footer' => false,
                'rank' => 100,
                'meta_title' => 'Main Page',
                'meta_description' => 'Main Page',
            ],
            [
                'title' => 'News',
                'slug' => '/news',
                'text' => '<h5>News</h5>',
                'module' => PageModule::NEWS,
                'display' => true,
                'display_menu' => true,
                'display_menu_footer' => false,
                'rank' => 99,
                'meta_title' => 'News',
                'meta_description' => 'News',
            ],
            [
                'title' => 'Feedback',
                'slug' => '/feedback',
                'text' => '<h5>News</h5>',
                'module' => PageModule::FEEDBACK,
                'display' => true,
                'display_menu' => false,
                'display_menu_footer' => true,
                'rank' => 98,
                'meta_title' => 'Feedback',
                'meta_description' => 'Feedback',
            ],
            [
                'title' => 'Text Page',
                'slug' => '/text-page-1',
                'text' => '<h5>Text page</h5>',
                'module' => PageModule::TEXT,
                'display' => true,
                'display_menu' => true,
                'display_menu_footer' => true,
                'rank' => 97,
                'meta_title' => 'Text Page',
                'meta_description' => 'Text Page',
            ],
            [
                'title' => 'Text Page 2',
                'slug' => '/text-page-2',
                'text' => '<h5>Text page</h5>',
                'module' => PageModule::TEXT,
                'display' => true,
                'display_menu' => true,
                'display_menu_footer' => true,
                'rank' => 96,
                'meta_title' => 'Text Page',
                'meta_description' => 'Text Page',
            ]
        ];

        foreach ($pages as $page) {
            $pageExist = Page::where('slug', $page['slug'])->first();
            if (!$pageExist) {
                $pageExist = new Page();
            }
            $pageExist->fill($page);
            $pageExist->save();
        }
    }
}
