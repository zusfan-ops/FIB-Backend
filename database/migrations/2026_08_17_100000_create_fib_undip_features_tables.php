<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Jadwal Kuliah Mahasiswa FIB UNDIP
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject'); // Mata Kuliah (cth: Sastra Jepang Modern, Nihonshi, Dokkai)
            $table->string('code')->nullable(); // Kode MK / Kelas (cth: MK-FIB-301)
            $table->string('lecturer')->nullable(); // Dosen Pengampu
            $table->string('room')->nullable(); // Ruang Kuliah (cth: Gedung A Lt. 3 R.302 FIB UNDIP)
            $table->unsignedTinyInteger('day_of_week'); // 1 = Senin, 2 = Selasa, ..., 7 = Minggu
            $table->time('start_time'); // cth: 08:00
            $table->time('end_time'); // cth: 09:40
            $table->unsignedTinyInteger('credits')->default(2); // SKS (cth: 2, 3, 4)
            $table->unsignedSmallInteger('reminder_minutes')->default(120); // Pengingat (default: 120 menit = 2 jam sebelum mulai)
            $table->string('color', 20)->default('#4F6EF7');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'day_of_week']);
        });

        // 2. Agenda / Catatan Diary Kampus
        Schema::create('campus_diaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->date('entry_date');
            $table->string('mood', 30)->default('semangat'); // semangat, fokus, santai, produktif, lelah
            $table->string('category', 50)->default('kuliah'); // kuliah, bimbingan, organisasi, belajar, refleksi
            $table->json('tags')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'entry_date']);
        });

        // 3. Foto & Album Kampus (Timeline Publik & Dokumentasi FIB UNDIP)
        Schema::create('campus_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // Judul Kegiatan (cth: Festival Budaya Bunkasai FIB 2026)
            $table->text('description')->nullable(); // Keterangan/Deskripsi foto
            $table->text('photo_url'); // URL foto atau base64/path
            $table->date('event_date')->nullable();
            $table->string('location')->nullable(); // cth: Teater Lingkar FIB UNDIP, Halaman Gedung D
            $table->string('category', 50)->default('kegiatan'); // bunkasai, seminar, kuliah, organisasi, praktikum
            $table->unsignedInteger('likes_count')->default(0);
            $table->boolean('is_public')->default(true); // bisa dilihat oleh mahasiswa lain di timeline
            $table->timestamps();

            $table->index(['is_public', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_photos');
        Schema::dropIfExists('campus_diaries');
        Schema::dropIfExists('class_schedules');
    }
};
