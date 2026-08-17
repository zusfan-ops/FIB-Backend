<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('pattern');
            $table->string('meaning')->nullable();
            $table->text('structure')->nullable();
            $table->text('usage')->nullable();
            $table->json('examples')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_bungo')->default(false);
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'pattern']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_patterns');
    }
};
