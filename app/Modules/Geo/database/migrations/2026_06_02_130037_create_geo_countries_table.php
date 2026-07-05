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
        Schema::create('geo_countries', function (Blueprint $table) {
            $table->id();
            $table->boolean('display')->default(1);
            $table->integer('rank')->default(0);
            $table->string('title')->nullable();
            $table->string('iso2')->unique()->nullable();
            $table->string('iso3')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_countries');
    }
};
