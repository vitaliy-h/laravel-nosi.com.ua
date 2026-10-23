<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Categories get the same «header menu» / «footer menu» toggles as pages. They
 * replace the header items of the dictionary (header.menu_1..3) and the first
 * five root categories the footer «Каталог» column used to show, so both are
 * carried over to keep the site looking the same.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('shop_categories', 'display_menu')) {
            return;
        }

        Schema::table('shop_categories', function (Blueprint $table) {
            $table->boolean('display_menu')->default(false)->after('display');
            $table->boolean('display_menu_footer')->default(false)->after('display_menu');
        });

        // Former header.menu_1..3 links: /shop/{slug}.
        DB::table('shop_categories')
            ->whereIn('slug', ['zhinochi-shkarpetky', 'kolgotky', 'bilyzna'])
            ->update(['display_menu' => true]);

        $footerIds = DB::table('shop_categories')
            ->whereNull('parent_id')
            ->where('display', true)
            ->orderBy('_lft')
            ->limit(5)
            ->pluck('id');

        DB::table('shop_categories')->whereIn('id', $footerIds)->update(['display_menu_footer' => true]);
    }

    public function down(): void
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->dropColumn(['display_menu', 'display_menu_footer']);
        });
    }
};
