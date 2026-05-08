<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->boolean('display')->default(true);
            $table->foreignId('category_id')->constrained('shop_categories');
            $table->foreignId('brand_id')->nullable()->constrained('shop_brands');
            $table->decimal('price', 10, 2);
            $table->decimal('price2', 10, 2);
            $table->string('article')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->string('img')->nullable();
            $table->longText('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_products');
    }
};
