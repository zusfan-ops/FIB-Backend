<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('author_jp')->nullable();
            $table->enum('genre', ['novel', 'cerpen', 'puisi', 'esai', 'manga', 'lainnya'])->default('novel');
            $table->string('original_language')->default('jepang');
            $table->unsignedInteger('total_pages')->nullable();
            $table->unsignedInteger('current_page')->default(0);
            $table->enum('status', ['to_read', 'reading', 'completed'])->default('to_read');
            $table->string('cover_color', 9)->default('#E8604C');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
