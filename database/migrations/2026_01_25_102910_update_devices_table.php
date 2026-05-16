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
         Schema::table('devices', function (Blueprint $table) {

        // rename existing column if needed
        if (Schema::hasColumn('devices', 'uuid')) {
            $table->renameColumn('uuid', 'device_uuid');
        }

        // add missing fields
        $table->string('device_model')->nullable()->after('device_uuid');
        $table->string('os')->nullable()->after('device_model');
        $table->string('os_version')->nullable()->after('os');

        $table->boolean('is_primary')->default(false)->after('is_verified');
        $table->boolean('is_active')->default(true)->after('is_primary');

        $table->timestamp('last_seen_at')->nullable()->after('is_active');

        $table->timestamps();
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
        $table->dropColumn([
            'device_model',
            'os',
            'os_version',
            'is_primary',
            'is_active',
            'last_seen_at',
        ]);
    });
    }
};
