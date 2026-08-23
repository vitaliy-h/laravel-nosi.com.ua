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
            $table->timestamps();
            $table->boolean('display')->default(true);
            $table->integer('rank')->nullable();
            $table->foreignId('category_id')->constrained('shop_categories')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained('shop_brands')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('price2', 10, 2)->nullable();
            // Denormalized review aggregates, maintained by ReviewObserver.
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->string('article')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('img')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('imgs')->nullable();
            $table->longText('text')->nullable();
            $table->index('rating');
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
