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
        if (!Schema::hasTable('user_water_conditions')) {
            Schema::create('user_water_conditions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('temperature')->nullable();
                $table->string('clarity')->nullable();
                $table->string('level')->nullable();
                $table->string('speed')->nullable();
                $table->string('surface_condition')->nullable();
                $table->string('tide')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_water_conditions');
    }
};

