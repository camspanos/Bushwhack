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
        Schema::table('fishing_logs', function (Blueprint $table) {
            // Store moon altitude in degrees (-90 to 90)
            $table->decimal('moon_altitude', 6, 2)->nullable()->after('moon_phase');
            // Store human-readable moon position label
            $table->string('moon_position', 50)->nullable()->after('moon_altitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_logs', function (Blueprint $table) {
            $table->dropColumn(['moon_altitude', 'moon_position']);
        });
    }
};
