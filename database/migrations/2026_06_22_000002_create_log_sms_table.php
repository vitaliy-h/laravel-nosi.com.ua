<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_sms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('source')->nullable();
            $table->string('status');
            $table->string('provider')->nullable();
            $table->string('template_key')->nullable();
            $table->string('receiver');
            $table->string('subject')->nullable();
            $table->text('error_message')->nullable();
            $table->longText('body')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_sms');
    }
};
