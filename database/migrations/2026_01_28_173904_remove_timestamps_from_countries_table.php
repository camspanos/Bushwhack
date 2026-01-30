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
        // Drop columns individually for SQLite compatibility
        if (Schema::hasColumn('countries', 'created_at')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->dropColumn('created_at');
            });
        }
        if (Schema::hasColumn('countries', 'updated_at')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->timestamps();
        });
    }
};
