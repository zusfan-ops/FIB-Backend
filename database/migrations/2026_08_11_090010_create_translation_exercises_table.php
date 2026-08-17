<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('source_text');
            $table->string('source_lang')->default('ja');
            $table->string('target_lang')->default('id');
            $table->text('my_translation')->nullable();
            $table->text('best_translation')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'done'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_exercises');
    }
};
