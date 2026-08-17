<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deck_id')->constrained()->cascadeOnDelete();
            $table->string('front')->comment('kanji / kata');
            $table->json('readings')->nullable()->comment('{"on":"...","kun":"..."}');
            $table->string('meaning')->nullable();
            $table->text('example_sentence')->nullable();
            $table->text('example_translation')->nullable();
            $table->json('tags')->nullable();
            $table->string('source')->default('manual');
            $table->unsignedBigInteger('clip_id')->nullable();
            $table->unsignedInteger('repetition')->default(0);
            $table->unsignedInteger('interval')->default(0);
            $table->float('ease_factor')->default(2.5);
            $table->unsignedInteger('lapses')->default(0);
            $table->timestamp('due_date')->useCurrent();
            $table->enum('state', ['new', 'learning', 'review'])->default('new');
            $table->timestamps();

            $table->index(['user_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
