<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The set the line was taken in («5 шт.»): one product in two sets is two
 * lines, so a line is the product plus its pack quantity. `quantity` is how
 * many such sets.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('shop_cart_products', 'pack_quantity')) {
            return;
        }

        Schema::table('shop_cart_products', function (Blueprint $table) {
            $table->unsignedInteger('pack_quantity')->default(1)->after('shop_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('shop_cart_products', function (Blueprint $table) {
            $table->dropColumn('pack_quantity');
        });
    }
};
