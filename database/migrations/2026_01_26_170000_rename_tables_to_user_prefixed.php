<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration renames the fish, flies, friends, and rods tables to have a 'user_' prefix
     * for better clarity and consistency in the database schema.
     */
    public function up(): void
    {
        // Rename the tables
        Schema::rename('fish', 'user_fish');
        Schema::rename('flies', 'user_flies');
        Schema::rename('friends', 'user_friends');
        Schema::rename('rods', 'user_rods');
        
        // Rename the pivot table
        Schema::rename('fishing_log_friend', 'fishing_log_user_friend');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the table renames
        Schema::rename('user_fish', 'fish');
        Schema::rename('user_flies', 'flies');
        Schema::rename('user_friends', 'friends');
        Schema::rename('user_rods', 'rods');
        
        // Reverse the pivot table rename
        Schema::rename('fishing_log_user_friend', 'fishing_log_friend');
    }
};

