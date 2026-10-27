<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The set the line was ordered in, copied from the cart line.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('shop_order_products', 'pack_quantity')) {
            return;
        }

        Schema::table('shop_order_products', function (Blueprint $table) {
            $table->unsignedInteger('pack_quantity')->default(1)->after('shop_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('shop_order_products', function (Blueprint $table) {
            $table->dropColumn('pack_quantity');
        });
    }
};
