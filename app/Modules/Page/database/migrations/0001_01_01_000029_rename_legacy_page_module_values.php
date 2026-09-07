<?php

use App\Modules\Page\Enums\PageModule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PageModule cases were renamed to match the module folder names
 * (`Str::snake(basename($module))`, see ModuleServiceProvider::registerWebRoute),
 * but `pages.mofirdule` kept the old strings. Any page left on a legacy value blows
 * up on the enum cast the moment the provider resolves it, so every install that
 * predates the rename fails to boot until the rows are moved over.
 */
return new class extends Migration
{
    private const RENAMES = [
        'text_page' => PageModule::TEXT,
        'auth'      => PageModule::AUTH,
    ];

    public function up(): void
    {
        $this->rename(self::RENAMES);
    }

    public function down(): void
    {
        $this->rename(array_flip(array_map(fn (PageModule $case) => $case->value, self::RENAMES)));
    }

    /**
     * @param  array<string, PageModule|string>  $renames
     */
    private function rename(array $renames): void
    {
        if (!Schema::hasTable('pages')) {
            return;
        }

        foreach ($renames as $from => $to) {
            DB::table('pages')
                ->where('module', $from)
                ->update(['module' => $to instanceof PageModule ? $to->value : $to]);
        }
    }
};
