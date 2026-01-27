<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration renames foreign key columns in the fishing_logs table
     * to match the naming convention of their referenced tables which now
     * have a 'user_' prefix.
     */
    public function up(): void
    {
        Schema::table('fishing_logs', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['location_id']);
            $table->dropForeign(['equipment_id']);
            $table->dropForeign(['fish_id']);
            $table->dropForeign(['fly_id']);
        });

        Schema::table('fishing_logs', function (Blueprint $table) {
            // Rename the columns
            $table->renameColumn('location_id', 'user_location_id');
            $table->renameColumn('equipment_id', 'user_rod_id');
            $table->renameColumn('fish_id', 'user_fish_id');
            $table->renameColumn('fly_id', 'user_fly_id');
        });

        Schema::table('fishing_logs', function (Blueprint $table) {
            // Re-create foreign key constraints with new column names
            $table->foreign('user_location_id')
                ->references('id')
                ->on('user_locations')
                ->nullOnDelete();

            $table->foreign('user_rod_id')
                ->references('id')
                ->on('user_rods')
                ->nullOnDelete();

            $table->foreign('user_fish_id')
                ->references('id')
                ->on('user_fish')
                ->nullOnDelete();

            $table->foreign('user_fly_id')
                ->references('id')
                ->on('user_flies')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_logs', function (Blueprint $table) {
            // Drop the new foreign key constraints
            $table->dropForeign(['user_location_id']);
            $table->dropForeign(['user_rod_id']);
            $table->dropForeign(['user_fish_id']);
            $table->dropForeign(['user_fly_id']);
        });

        Schema::table('fishing_logs', function (Blueprint $table) {
            // Rename columns back to original names
            $table->renameColumn('user_location_id', 'location_id');
            $table->renameColumn('user_rod_id', 'equipment_id');
            $table->renameColumn('user_fish_id', 'fish_id');
            $table->renameColumn('user_fly_id', 'fly_id');
        });

        Schema::table('fishing_logs', function (Blueprint $table) {
            // Re-create original foreign key constraints
            $table->foreign('location_id')
                ->references('id')
                ->on('user_locations')
                ->nullOnDelete();

            $table->foreign('equipment_id')
                ->references('id')
                ->on('user_rods')
                ->nullOnDelete();

            $table->foreign('fish_id')
                ->references('id')
                ->on('user_fish')
                ->nullOnDelete();

            $table->foreign('fly_id')
                ->references('id')
                ->on('user_flies')
                ->nullOnDelete();
        });
    }
};
