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
        Schema::table('user_water_conditions', function (Blueprint $table) {
            if (Schema::hasColumn('user_water_conditions', 'surface') && !Schema::hasColumn('user_water_conditions', 'surface_condition')) {
                $table->renameColumn('surface', 'surface_condition');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_water_conditions', function (Blueprint $table) {
            if (Schema::hasColumn('user_water_conditions', 'surface_condition') && !Schema::hasColumn('user_water_conditions', 'surface')) {
                $table->renameColumn('surface_condition', 'surface');
            }
        });
    }
};
