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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Monthly, Annual, Lifetime
            $table->string('slug')->unique(); // monthly, annual, lifetime
            $table->text('description')->nullable();
            $table->integer('price'); // Price in cents
            $table->string('billing_interval'); // month, year, lifetime
            $table->integer('billing_interval_count')->default(1); // e.g., 1 month, 1 year
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

