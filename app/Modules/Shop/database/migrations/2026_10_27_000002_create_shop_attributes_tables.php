<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product parameters (size, season, material…): a parameter has a fixed list
 * of values, a product gets any number of them. Every parameter works the
 * same way — a multiple choice, filterable in the catalogue by `code`
 * (`?season[]=3`).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shop_attributes')) {
            return;
        }

        Schema::create('shop_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('display')->default(true);
            // Shown in the catalogue sidebar.
            $table->boolean('is_filterable')->default(true);
            $table->integer('rank')->nullable();
            $table->string('title')->nullable();
            $table->string('code')->unique();
        });

        Schema::create('shop_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('attribute_id')->constrained('shop_attributes')->cascadeOnDelete();
            $table->integer('rank')->nullable();
            $table->string('title')->nullable();
        });

        Schema::create('shop_attribute_option_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('shop_attribute_options')->cascadeOnDelete();
            $table->primary(['product_id', 'option_id']);
            $table->index('option_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_attribute_option_product');
        Schema::dropIfExists('shop_attribute_options');
        Schema::dropIfExists('shop_attributes');
    }
};
