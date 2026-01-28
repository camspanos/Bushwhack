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
        Schema::table('users', function (Blueprint $table) {
            $table->string('city')->nullable()->after('email');
            $table->string('state')->nullable()->after('city');
            $table->foreignId('country_id')->nullable()->after('state')->constrained('countries')->nullOnDelete();
            $table->boolean('metric')->default(false)->after('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['city', 'state', 'country_id', 'metric']);
        });
    }
};
