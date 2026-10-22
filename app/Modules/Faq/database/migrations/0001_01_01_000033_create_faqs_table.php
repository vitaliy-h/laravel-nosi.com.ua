<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('display')->default(true);
            $table->integer('rank')->nullable();
            $table->string('title')->nullable();
            $table->text('text')->nullable();
        });

        // Demo questions ship with the table, so the block is never empty on a fresh install.
        $now = now();
        $rows = [
            ['Як підібрати розмір шкарпеток?', '<p>Орієнтуйтесь на розмір взуття: 36–38 — розмір 23–25, 39–42 — 25–27, 43–46 — 27–29. Таблиця розмірів є на сторінці кожного товару.</p>'],
            ['Що означає DEN у колготках?', '<p>DEN — щільність нитки. 8–20 DEN — тонкі літні, 30–40 — універсальні на щодень, 70 і більше — щільні та теплі для холодної пори.</p>'],
            ['Скільки коштує і як довго йде доставка?', '<p>Відправляємо щодня, доставка Новою поштою займає 1–2 дні. Від 800 ₴ — безкоштовно, інакше — за тарифами перевізника.</p>'],
            ['Чи можна обміняти або повернути товар?', '<p>Так, протягом 14 днів, якщо виріб не був у використанні та збережено упаковку. Білизна відповідно до законодавства обміну не підлягає.</p>'],
            ['Як прати, щоб шкарпетки довше служили?', '<p>Вивертайте на виворіт, періть при 30–40 °C у делікатному режимі, не використовуйте відбілювачі та не сушіть на батареї.</p>'],
        ];

        $rank = count($rows);

        DB::table('faqs')->insert(array_map(function (array $row) use (&$rank, $now) {
            return [
                'title'      => $row[0],
                'text'       => $row[1],
                'rank'       => $rank--,
                'display'    => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rows));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
