<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Likes per user
        Schema::create('campus_photo_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_photo_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'campus_photo_id']);
        });

        // 2. Comments
        Schema::create('campus_photo_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_photo_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->timestamps();

            $table->index(['campus_photo_id', 'created_at']);
        });

        // 3. Add comments_count to campus_photos if not exists
        if (! Schema::hasColumn('campus_photos', 'comments_count')) {
            Schema::table('campus_photos', function (Blueprint $table) {
                $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_photo_comments');
        Schema::dropIfExists('campus_photo_likes');

        if (Schema::hasColumn('campus_photos', 'comments_count')) {
            Schema::table('campus_photos', function (Blueprint $table) {
                $table->dropColumn('comments_count');
            });
        }
    }
};
