<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('page_start')->nullable();
            $table->unsignedInteger('page_end')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['book_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
