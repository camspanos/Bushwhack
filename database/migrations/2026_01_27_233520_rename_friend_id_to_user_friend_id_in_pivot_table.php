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
        // Drop the foreign key constraint first (using explicit constraint name)
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
            $table->dropForeign('fishing_log_user_friend_user_friend_id_foreign');
        });

        // Rename the column back
        Schema::table('fishing_log_user_friend', function (Blueprint $table) {
            $table->renameColumn('user_friend_id', 'friend_id');
        });

        // Re-add the foreign key constraint with the old column name
        Schema::table('fishing_log_user_friend', function (Blueprint $table) {
            $table->foreign('friend_id', 'fishing_log_friend_friend_id_foreign')->references('id')->on('user_friends')->cascadeOnDelete();
        });
    }
};
