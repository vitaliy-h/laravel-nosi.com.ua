<?php

use App\Modules\ShopCart\Enums\PromocodeTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_promocodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Stored upper-cased by the model mutator so lookups are case-insensitive.
            $table->string('code', 64)->unique();
            $table->string('type')->default(PromocodeTypeEnum::FIXED->value);
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            // Ceiling for percent codes.
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->datetime('starts_at')->nullable();
            $table->datetime('expires_at')->nullable();
            // null = unlimited.
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('display')->default(true);
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_promocodes');
    }
};
