<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nim')) {
                $table->string('nim')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('nim');
            }
            if (!Schema::hasColumn('users', 'semester')) {
                $table->string('semester')->nullable()->after('study_program');
            }
            if (!Schema::hasColumn('users', 'angkatan')) {
                $table->string('angkatan')->nullable()->after('semester');
            }
        });

        if (!Schema::hasTable('direct_messages')) {
            Schema::create('direct_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
                $table->text('message')->nullable();
                $table->longText('attachment_url')->nullable();
                $table->string('attachment_type')->nullable(); // 'image', 'file', etc.
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['sender_id', 'receiver_id', 'created_at']);
                $table->index(['receiver_id', 'is_read']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_messages');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'phone_number', 'semester', 'angkatan']);
        });
    }
};
