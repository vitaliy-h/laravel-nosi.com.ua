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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('display')->default(true);
            $table->integer('rank')->nullable();
            $table->string('kicker')->nullable();
            $table->string('title')->nullable();
            $table->string('button_text')->nullable();
            $table->string('url')->nullable();
            $table->string('img')->nullable();
            $table->text('text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
