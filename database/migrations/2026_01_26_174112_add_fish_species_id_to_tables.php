<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Fish;
use App\Models\FishSpecies;
use App\Models\FishingLog;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add fish_species_id to user_fish table
        Schema::table('user_fish', function (Blueprint $table) {
            $table->foreignId('fish_species_id')->nullable()->after('id')->constrained('fish_species')->nullOnDelete();
        });

        // Add fish_species_id to fishing_logs table
        Schema::table('fishing_logs', function (Blueprint $table) {
            $table->foreignId('fish_species_id')->nullable()->after('fish_id')->constrained('fish_species')->nullOnDelete();
        });

        // Migrate existing data: match user_fish species to fish_species
        $userFish = Fish::all();
        foreach ($userFish as $fish) {
            $fishSpecies = FishSpecies::where('species', $fish->species)
                ->where('water_type', $fish->water_type)
                ->first();

            if ($fishSpecies) {
                $fish->update(['fish_species_id' => $fishSpecies->id]);

                // Update all fishing logs that reference this user_fish
                FishingLog::where('fish_id', $fish->id)
                    ->update(['fish_species_id' => $fishSpecies->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_fish', function (Blueprint $table) {
            $table->dropForeign(['fish_species_id']);
            $table->dropColumn('fish_species_id');
        });

        Schema::table('fishing_logs', function (Blueprint $table) {
            $table->dropForeign(['fish_species_id']);
            $table->dropColumn('fish_species_id');
        });
    }
};
