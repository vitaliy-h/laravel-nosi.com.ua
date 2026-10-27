<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A product is sold in up to three sets («1 шт.», «5 шт.», «10 шт.») with a
 * price each — `packs`, `[{quantity, price}]`. `price` stays as the price of
 * the smallest set (Product keeps it in step) for the catalogue filter and
 * sorting. `is_pack` only switches the unit label «шт.» → «уп.».
 *
 * The old crossed-out price `price2` and the sale block it fed are gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('shop_products', 'packs')) {
            return;
        }

        Schema::table('shop_products', function (Blueprint $table) {
            $table->json('packs')->nullable()->after('price');
            $table->boolean('is_pack')->default(false)->after('packs');
        });

        DB::table('shop_products')->whereNotNull('price')->orderBy('id')->each(function ($row): void {
            DB::table('shop_products')->where('id', $row->id)->update([
                'packs' => json_encode([['quantity' => 1, 'price' => (float) $row->price]]),
            ]);
        });

        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('price2');
        });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->decimal('price2', 10, 2)->nullable()->after('price');
            $table->dropColumn(['packs', 'is_pack']);
        });
    }
};
