<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration converts the friends relationship from one-to-many to many-to-many
     * by removing the friend_id column from fishing_logs and creating a pivot table.
     *
     * The pivot table allows a fishing log to have multiple friends and a friend to be
     * associated with multiple fishing logs.
     *
     * @return void
     */
    public function up(): void
    {
        // First, remove the friend_id column from fishing_logs table
        Schema::table('fishing_logs', function (Blueprint $table) {
            $table->dropForeign(['friend_id']);
            $table->dropColumn('friend_id');
        });

        // Create the pivot table
        Schema::create('fishing_log_friend', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishing_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('friend_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['fishing_log_id', 'friend_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method restores the database to its previous state by dropping the pivot table
     * and restoring the friend_id column to the fishing_logs table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_log_friend');

        // Restore the friend_id column
        Schema::table('fishing_logs', function (Blueprint $table) {
            $table->foreignId('friend_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
