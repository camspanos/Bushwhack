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
        Schema::table('equipment', function (Blueprint $table) {
            // Rename tippet to rod_length
            $table->renameColumn('tippet', 'rod_length');
        });

        // Reorder the column to be after rod_weight
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('rod_length')->nullable()->after('rod_weight')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Rename back to tippet
            $table->renameColumn('rod_length', 'tippet');
        });

        // Move back to original position (after line)
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('tippet')->nullable()->after('line')->change();
        });
    }
};
