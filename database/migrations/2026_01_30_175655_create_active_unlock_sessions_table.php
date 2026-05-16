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
        if (Schema::hasTable('active_unlock_sessions')) {
            return;
        }

        Schema::create('active_unlock_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->timestamps();

            $table->foreign('child_id')
                ->references('id')
                ->on('children')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_unlock_sessions');
    }
};
