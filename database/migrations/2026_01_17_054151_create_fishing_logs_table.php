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
        Schema::create('fishing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fish_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fly_id')->nullable()->constrained('flies')->nullOnDelete();
            $table->foreignId('friend_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->integer('quantity')->nullable();
            $table->decimal('max_size', 5, 2)->nullable();
            $table->string('style')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_logs');
    }
};
