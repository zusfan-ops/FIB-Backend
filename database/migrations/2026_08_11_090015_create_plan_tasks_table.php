<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('column', ['todo', 'doing', 'done'])->default('todo');
            $table->unsignedInteger('order')->default(0);
            $table->date('due_date')->nullable();
            $table->foreignId('schedule_item_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'column']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_tasks');
    }
};
