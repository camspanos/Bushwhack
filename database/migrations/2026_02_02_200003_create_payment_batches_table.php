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
        Schema::create('payment_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->date('run_date');
            $table->integer('total_processed')->default(0);
            $table->integer('total_successful')->default(0);
            $table->integer('total_failed')->default(0);
            $table->integer('total_amount_processed')->default(0);
            $table->integer('total_amount_successful')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('run_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_batches');
    }
};

