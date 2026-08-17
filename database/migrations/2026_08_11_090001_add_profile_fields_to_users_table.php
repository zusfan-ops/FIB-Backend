<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jlpt_level')->nullable()->after('name');
            $table->string('university')->nullable()->after('jlpt_level');
            $table->text('bio')->nullable()->after('university');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jlpt_level', 'university', 'bio']);
        });
    }
};
