<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->integer('max_points')->default(100)->after('join_code');
            $table->timestamp('started_at')->nullable()->after('max_points');
            $table->timestamp('paused_at')->nullable()->after('started_at');
            $table->integer('accumulated_time')->default(0)->after('paused_at'); // In seconds
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->timestamp('finished_at')->nullable()->after('points');
            $table->integer('duration_seconds')->nullable()->after('finished_at');
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['max_points', 'started_at', 'paused_at', 'accumulated_time']);
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['finished_at', 'duration_seconds']);
        });
    }
};
