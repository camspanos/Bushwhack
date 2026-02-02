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
        Schema::create('payment_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('amount'); // Amount in cents
            $table->string('status'); // pending, succeeded, failed
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index(['payment_batch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_batch_items');
    }
};

