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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // charge, refund
            $table->integer('amount'); // Amount in cents
            $table->string('currency', 3)->default('USD');
            $table->string('status'); // pending, succeeded, failed, refunded
            $table->string('payment_method')->nullable(); // card, paypal, etc.
            $table->string('external_id')->nullable(); // Payment gateway transaction ID
            $table->string('external_payment_method_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

