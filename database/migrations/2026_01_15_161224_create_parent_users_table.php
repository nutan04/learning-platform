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
        
    Schema::create('parent_users', function (Blueprint $table) {
  $table->uuid('id')->primary();
  $table->string('mobile_number')->unique();
  $table->boolean('is_verified')->default(false);
  $table->timestamp('last_login_at')->nullable();
  $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_users');
    }
};
