<?php

namespace Tests\Concerns;

use Database\Seeders\LetterTemplateSeeder;
use Database\Seeders\PagesTableSeeder;

trait InteractsWithPages
{
    protected function seedPagesForRouting(): void
    {
        $this->seed(PagesTableSeeder::class);
    }

    protected function seedLetterTemplates(): void
    {
        $this->seed(LetterTemplateSeeder::class);
    }
}
