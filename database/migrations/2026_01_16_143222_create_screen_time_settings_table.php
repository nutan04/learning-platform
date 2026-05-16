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
        Schema::create('screen_time_settings', function (Blueprint $table) {
            $table->uuid('child_id')->primary();
            $table->integer('daily_unlock_count')->default(3);
            $table->integer('unlock_duration_minutes')->default(30);
            $table->integer('used_unlocks_today')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screen_time_settings');
    }
};
