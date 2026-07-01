<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('basket_shop_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_id')->constrained('baskets');
            $table->foreignId('shop_product_id')->constrained('shop_products');
            $table->string('product_name')->nullable();
            $table->string('product_article')->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basket_shop_products');
    }
};
