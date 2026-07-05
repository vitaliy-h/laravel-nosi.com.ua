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
        Schema::create('geo_regions', function (Blueprint $table) {
            $table->id();
            $table->boolean('display')->default(1);
            $table->integer('rank')->default(0);
            $table->foreignId('geo_country_id')->nullable()->constrained('geo_countries')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('code')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_regions');
    }
};
