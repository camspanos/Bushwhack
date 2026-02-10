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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 50); // Emoji or icon class
            $table->text('description');
            $table->string('category'); // quantity, size, time, weather, moon, location, species, streak, social, seasonal, rod, fly, weight, combo
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->string('requirement_type'); // count, max, min, exists, streak, percentage, time_range, date_range, combo
            $table->string('requirement_field'); // total_caught, max_size, species_count, etc.
            $table->string('requirement_operator')->nullable(); // >=, <=, =, >, <, between
            $table->decimal('requirement_value', 10, 2)->nullable(); // Primary threshold value
            $table->decimal('requirement_value2', 10, 2)->nullable(); // Secondary value for ranges
            $table->json('requirement_extra')->nullable(); // Additional requirements (e.g., specific species, weather conditions)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['rarity', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
