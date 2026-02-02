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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');
            $table->string('status'); // active, cancelled, expired, past_due
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable(); // null for lifetime
            $table->timestamp('next_billing_date')->nullable(); // null for lifetime
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->string('external_id')->nullable(); // For payment gateway reference
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('next_billing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

