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
            // Add avg_size after max_size - used when quantity > 1 to track average fish size
            $table->decimal('avg_size', 8, 2)->nullable()->after('max_weight');
            // Add avg_weight after avg_size - used when quantity > 1 to track average fish weight
            $table->decimal('avg_weight', 8, 2)->nullable()->after('avg_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_logs', function (Blueprint $table) {
            $table->dropColumn(['avg_size', 'avg_weight']);
        });
    }
};
