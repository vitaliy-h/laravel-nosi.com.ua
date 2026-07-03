<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->bigInteger('order_number')->unique()->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_status')->default(\App\Modules\Shop\Enums\PaymentStatusEnum::UNPAID);
            $table->string('payment_type')->default(\App\Modules\Shop\Enums\PaymentTypeEnum::NONE);
            $table->datetime('payment_date')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_surname')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_phone')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};
