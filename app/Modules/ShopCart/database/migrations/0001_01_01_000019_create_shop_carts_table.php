<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->decimal('total_amount', 10, 2)->default(0);
            // Promocode: no FK here — shop_promocodes (000028) is created after
            // this table, and the code is denormalised on purpose so a deleted
            // promocode still leaves the cart readable.
            $table->decimal('subtotal_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('promocode_id')->nullable()->index();
            $table->string('promocode_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_carts');
    }
};
