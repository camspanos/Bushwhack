<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration renames the locations table to user_locations
     * for better clarity and consistency with other user-specific tables.
     */
    public function up(): void
    {
        Schema::rename('locations', 'user_locations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('user_locations', 'locations');
    }
};
