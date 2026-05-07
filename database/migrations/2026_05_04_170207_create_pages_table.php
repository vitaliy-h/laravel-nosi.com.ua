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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('text')->nullable(); // RichEditor HTML
            $table->string('module')->index()->default(\App\Modules\Core\Enums\PageModule::TEXT);
            $table->integer('rank')->default(0);
            $table->boolean('display')->default(true);
            $table->boolean('display_menu')->default(false);
            $table->boolean('display_menu_footer')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
