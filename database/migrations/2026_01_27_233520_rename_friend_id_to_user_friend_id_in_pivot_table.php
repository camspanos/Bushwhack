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
        // Drop the foreign key constraint first
        // Note: The foreign key was created with the original table name 'fishing_log_friend'
        // so we need to use that name, not the renamed table name
        Schema::table('fishing_log_user_friend', function (Blueprint $table) {
            $table->dropForeign('fishing_log_friend_friend_id_foreign');
        });

        // Rename the column
        Schema::table('fishing_log_user_friend', function (Blueprint $table) {
            $table->renameColumn('friend_id', 'user_friend_id');
        });

        // Re-add the foreign key constraint with the new column name
        Schema::table('fishing_log_user_friend', function (Blueprint $table) {
            $table->foreign('user_friend_id')->references('id')->on('user_friends')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        Schema::table('fishing_log_user_friend', function (Blueprint $table) {
            $table->dropForeign(['user_friend_id']);
        });

        // Rename the column back
        Schema::table('fishing_log_user_friend', function (Blueprint $table) {
            $table->renameColumn('user_friend_id', 'friend_id');
        });

        // Re-add the foreign key constraint with the old column name
        Schema::table('fishing_log_user_friend', function (Blueprint $table) {
            $table->foreign('friend_id')->references('id')->on('user_friends')->cascadeOnDelete();
        });
    }
};
