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
            $table->dropForeign(['fish_species_id']);
            $table->dropColumn('fish_species_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('fish_species_id')->nullable()->after('user_fish_id');
            $table->foreign('fish_species_id')->references('id')->on('fish_species')->onDelete('set null');
        });
    }
};
