<?php

use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\Enums\PaymentTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->bigInteger('order_number')->unique()->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->decimal('total_amount', 10, 2)->default(0);
            // Promocode: no FK here — shop_promocodes (000028) is created after
            // this table, and the code is denormalised on purpose so a deleted
            // promocode still leaves the order readable.
            $table->decimal('subtotal_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('promocode_id')->nullable()->index();
            $table->string('promocode_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('payment_status')->default(PaymentStatusEnum::UNPAID);
            $table->string('payment_type')->default(PaymentTypeEnum::NONE);
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
