<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kamus Kanji & Kosakata (global, dipakai bersama semua mahasiswa)
        Schema::create('dictionary_entries', function (Blueprint $table) {
            $table->id();
            $table->string('term'); // Kanji atau kosakata (cth: 図書館, 勉強する)
            $table->string('reading_on')->nullable(); // Cara baca On'yomi / furigana katakana
            $table->string('reading_kun')->nullable(); // Cara baca Kun'yomi / furigana hiragana
            $table->text('meaning'); // Arti dalam Bahasa Indonesia
            $table->text('example_sentence')->nullable();
            $table->text('example_translation')->nullable();
            $table->string('category', 20)->default('kosakata'); // kanji, kosakata
            $table->string('jlpt_level', 2)->default('N5'); // N5..N1
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['jlpt_level', 'category']);
            $table->index('term');
        });

        // 2. Riwayat Simulasi Ujian JLPT (skor & durasi per percobaan)
        Schema::create('jlpt_mock_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('level', 2); // N5..N1
            $table->unsignedSmallInteger('total_questions');
            $table->unsignedSmallInteger('correct_count');
            $table->unsignedTinyInteger('score'); // persentase 0-100
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jlpt_mock_results');
        Schema::dropIfExists('dictionary_entries');
    }
};
