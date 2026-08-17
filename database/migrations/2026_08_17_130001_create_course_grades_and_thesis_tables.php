<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kalkulator IPK: nilai per mata kuliah per semester
        Schema::create('course_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('course_name'); // Nama mata kuliah (cth: Nihonshi, Dokkai Chukyu)
            $table->unsignedTinyInteger('credits')->default(2); // SKS
            $table->string('semester', 50); // cth: "Ganjil 2025/2026" atau "Semester 3"
            $table->string('grade_letter', 2); // A, AB, B, BC, C, D, E
            $table->decimal('grade_point', 3, 2); // 4.00 .. 0.00, dihitung dari grade_letter
            $table->timestamps();

            $table->index(['user_id', 'semester']);
        });

        // 2. Tracker Skripsi/Tugas Akhir: profil (1 per mahasiswa)
        Schema::create('thesis_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('title')->nullable(); // Judul skripsi
            $table->string('advisor_1')->nullable(); // Dosen Pembimbing 1
            $table->string('advisor_2')->nullable(); // Dosen Pembimbing 2
            $table->date('target_defense_date')->nullable(); // Target tanggal sidang
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Tracker Skripsi: milestone/progress per bab
        Schema::create('thesis_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // cth: "Bab 1 - Pendahuluan"
            $table->string('status', 20)->default('todo'); // todo, doing, done
            $table->unsignedInteger('order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thesis_milestones');
        Schema::dropIfExists('thesis_profiles');
        Schema::dropIfExists('course_grades');
    }
};
