<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_letters', function (Blueprint $table) {
            $table->id();

            $table->string('template_key')->nullable();

            $table->string('receiver');

            $table->string('subject')->nullable();

            $table->string('sender')->nullable();
            $table->string('sender_name')->nullable();

            $table->longText('body')->nullable();

            $table->longText('content')->nullable();

            $table->string('status');

            $table->text('error_message')->nullable();

            $table->string('source')->nullable();

            $table->json('attachments')->nullable();

            $table->timestamps();
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
