<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('SM-2: 0-5');
            $table->unsignedInteger('interval');
            $table->float('ease_factor');
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->index(['user_id', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_logs');
    }
};
