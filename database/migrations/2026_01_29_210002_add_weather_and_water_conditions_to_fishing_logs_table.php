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
            if (!Schema::hasColumn('fishing_logs', 'user_weather_id')) {
                $table->foreignId('user_weather_id')
                    ->nullable()
                    ->after('user_fly_id')
                    ->constrained('user_weather')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('fishing_logs', 'user_water_condition_id')) {
                $table->foreignId('user_water_condition_id')
                    ->nullable()
                    ->after('user_weather_id')
                    ->constrained('user_water_conditions')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_logs', function (Blueprint $table) {
            $table->dropForeign(['user_weather_id']);
            $table->dropForeign(['user_water_condition_id']);
            $table->dropColumn(['user_weather_id', 'user_water_condition_id']);
        });
    }
};

