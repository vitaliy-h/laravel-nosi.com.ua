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
        Schema::create('log_letters', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('source')->nullable();
            $table->string('status');
            $table->string('template_key')->nullable();
            $table->string('receiver');
            $table->string('subject')->nullable();
            $table->string('sender')->nullable();
            $table->string('sender_name')->nullable();
            $table->text('error_message')->nullable();
            $table->longText('body')->nullable();
            $table->longText('content')->nullable();
            $table->json('attachments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_letters');
    }
};
