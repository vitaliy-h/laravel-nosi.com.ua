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
        Schema::create('shop_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            // Moderation: reviews stay hidden until an admin publishes them.
            $table->boolean('display')->default(false);
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('name')->nullable();
            $table->string('city')->nullable();
            $table->string('email')->nullable();
            $table->text('text')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->index(['product_id', 'display']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_product_reviews');
    }
};
