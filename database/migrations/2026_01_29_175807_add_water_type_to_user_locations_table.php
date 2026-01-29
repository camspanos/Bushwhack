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
        if (!Schema::hasColumn('user_locations', 'water_type')) {
            Schema::table('user_locations', function (Blueprint $table) {
                $table->string('water_type')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->dropColumn('water_type');
        });
    }
};
