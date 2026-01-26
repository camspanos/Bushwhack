<?php

namespace Database\Seeders;

use App\Models\FishSpecies;
use Illuminate\Database\Seeder;

class FishSpeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $species = [
            // Freshwater Trout
            ['species' => 'Rainbow Trout', 'water_type' => 'freshwater'],
            ['species' => 'Brown Trout', 'water_type' => 'freshwater'],
            ['species' => 'Brook Trout', 'water_type' => 'freshwater'],
            ['species' => 'Cutthroat Trout', 'water_type' => 'freshwater'],
            ['species' => 'Lake Trout', 'water_type' => 'freshwater'],
            ['species' => 'Bull Trout', 'water_type' => 'freshwater'],
            ['species' => 'Golden Trout', 'water_type' => 'freshwater'],
            ['species' => 'Dolly Varden', 'water_type' => 'freshwater'],
            ['species' => 'Arctic Char', 'water_type' => 'freshwater'],
            ['species' => 'Grayling', 'water_type' => 'freshwater'],
            ['species' => 'Steelhead', 'water_type' => 'freshwater'],

            // Freshwater Bass
            ['species' => 'Largemouth Bass', 'water_type' => 'freshwater'],
            ['species' => 'Smallmouth Bass', 'water_type' => 'freshwater'],
            ['species' => 'Spotted Bass', 'water_type' => 'freshwater'],
            ['species' => 'Striped Bass', 'water_type' => 'freshwater'],
            ['species' => 'White Bass', 'water_type' => 'freshwater'],
            ['species' => 'Hybrid Striped Bass', 'water_type' => 'freshwater'],
            ['species' => 'Rock Bass', 'water_type' => 'freshwater'],

            // Freshwater Pike & Muskie
            ['species' => 'Northern Pike', 'water_type' => 'freshwater'],
            ['species' => 'Muskie', 'water_type' => 'freshwater'],
            ['species' => 'Tiger Muskie', 'water_type' => 'freshwater'],
            ['species' => 'Chain Pickerel', 'water_type' => 'freshwater'],

            // Freshwater Walleye & Perch
            ['species' => 'Walleye', 'water_type' => 'freshwater'],
            ['species' => 'Sauger', 'water_type' => 'freshwater'],
            ['species' => 'Yellow Perch', 'water_type' => 'freshwater'],
            ['species' => 'White Perch', 'water_type' => 'freshwater'],

            // Freshwater Panfish
            ['species' => 'Bluegill', 'water_type' => 'freshwater'],
            ['species' => 'Redear Sunfish', 'water_type' => 'freshwater'],
            ['species' => 'Pumpkinseed', 'water_type' => 'freshwater'],
            ['species' => 'Green Sunfish', 'water_type' => 'freshwater'],
            ['species' => 'Warmouth', 'water_type' => 'freshwater'],
            ['species' => 'Black Crappie', 'water_type' => 'freshwater'],
            ['species' => 'White Crappie', 'water_type' => 'freshwater'],

            // Freshwater Catfish
            ['species' => 'Channel Catfish', 'water_type' => 'freshwater'],
            ['species' => 'Blue Catfish', 'water_type' => 'freshwater'],
            ['species' => 'Flathead Catfish', 'water_type' => 'freshwater'],
            ['species' => 'Bullhead Catfish', 'water_type' => 'freshwater'],

            // Freshwater Salmon
            ['species' => 'Chinook Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Coho Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Sockeye Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Atlantic Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Pink Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Chum Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Kokanee Salmon', 'water_type' => 'freshwater'],

            // Other Freshwater
            ['species' => 'Carp', 'water_type' => 'freshwater'],
            ['species' => 'Grass Carp', 'water_type' => 'freshwater'],
            ['species' => 'Gar', 'water_type' => 'freshwater'],
            ['species' => 'Bowfin', 'water_type' => 'freshwater'],
            ['species' => 'Burbot', 'water_type' => 'freshwater'],
            ['species' => 'Shad', 'water_type' => 'freshwater'],
            ['species' => 'Whitefish', 'water_type' => 'freshwater'],
            ['species' => 'Cisco', 'water_type' => 'freshwater'],
            ['species' => 'Drum', 'water_type' => 'freshwater'],

            // Saltwater Inshore
            ['species' => 'Redfish', 'water_type' => 'saltwater'],
            ['species' => 'Snook', 'water_type' => 'saltwater'],
            ['species' => 'Tarpon', 'water_type' => 'saltwater'],
            ['species' => 'Bonefish', 'water_type' => 'saltwater'],
            ['species' => 'Permit', 'water_type' => 'saltwater'],
            ['species' => 'Speckled Trout', 'water_type' => 'saltwater'],
            ['species' => 'Spotted Seatrout', 'water_type' => 'saltwater'],
            ['species' => 'Flounder', 'water_type' => 'saltwater'],
            ['species' => 'Striped Bass', 'water_type' => 'saltwater'],
            ['species' => 'Bluefish', 'water_type' => 'saltwater'],
            ['species' => 'Weakfish', 'water_type' => 'saltwater'],
            ['species' => 'Black Drum', 'water_type' => 'saltwater'],
            ['species' => 'Sheepshead', 'water_type' => 'saltwater'],
            ['species' => 'Pompano', 'water_type' => 'saltwater'],
            ['species' => 'Jack Crevalle', 'water_type' => 'saltwater'],
            ['species' => 'Ladyfish', 'water_type' => 'saltwater'],

            // Saltwater Offshore
            ['species' => 'Mahi Mahi', 'water_type' => 'saltwater'],
            ['species' => 'Yellowfin Tuna', 'water_type' => 'saltwater'],
            ['species' => 'Bluefin Tuna', 'water_type' => 'saltwater'],
            ['species' => 'Blackfin Tuna', 'water_type' => 'saltwater'],
            ['species' => 'Albacore Tuna', 'water_type' => 'saltwater'],
            ['species' => 'Skipjack Tuna', 'water_type' => 'saltwater'],
            ['species' => 'Blue Marlin', 'water_type' => 'saltwater'],
            ['species' => 'White Marlin', 'water_type' => 'saltwater'],
            ['species' => 'Sailfish', 'water_type' => 'saltwater'],
            ['species' => 'Swordfish', 'water_type' => 'saltwater'],
            ['species' => 'Wahoo', 'water_type' => 'saltwater'],
            ['species' => 'King Mackerel', 'water_type' => 'saltwater'],
            ['species' => 'Spanish Mackerel', 'water_type' => 'saltwater'],
            ['species' => 'Cero Mackerel', 'water_type' => 'saltwater'],
            ['species' => 'Barracuda', 'water_type' => 'saltwater'],
            ['species' => 'Cobia', 'water_type' => 'saltwater'],
            ['species' => 'Amberjack', 'water_type' => 'saltwater'],
            ['species' => 'Yellowtail', 'water_type' => 'saltwater'],

            // Saltwater Bottom Fish
            ['species' => 'Red Snapper', 'water_type' => 'saltwater'],
            ['species' => 'Mangrove Snapper', 'water_type' => 'saltwater'],
            ['species' => 'Lane Snapper', 'water_type' => 'saltwater'],
            ['species' => 'Mutton Snapper', 'water_type' => 'saltwater'],
            ['species' => 'Yellowtail Snapper', 'water_type' => 'saltwater'],
            ['species' => 'Vermilion Snapper', 'water_type' => 'saltwater'],
            ['species' => 'Red Grouper', 'water_type' => 'saltwater'],
            ['species' => 'Gag Grouper', 'water_type' => 'saltwater'],
            ['species' => 'Black Grouper', 'water_type' => 'saltwater'],
            ['species' => 'Goliath Grouper', 'water_type' => 'saltwater'],
            ['species' => 'Scamp Grouper', 'water_type' => 'saltwater'],
            ['species' => 'Triggerfish', 'water_type' => 'saltwater'],
            ['species' => 'Hogfish', 'water_type' => 'saltwater'],
            ['species' => 'Tilefish', 'water_type' => 'saltwater'],
            ['species' => 'Sea Bass', 'water_type' => 'saltwater'],
            ['species' => 'Halibut', 'water_type' => 'saltwater'],
            ['species' => 'Lingcod', 'water_type' => 'saltwater'],
            ['species' => 'Rockfish', 'water_type' => 'saltwater'],

            // Saltwater Sharks & Rays
            ['species' => 'Blacktip Shark', 'water_type' => 'saltwater'],
            ['species' => 'Bull Shark', 'water_type' => 'saltwater'],
            ['species' => 'Hammerhead Shark', 'water_type' => 'saltwater'],
            ['species' => 'Mako Shark', 'water_type' => 'saltwater'],
            ['species' => 'Thresher Shark', 'water_type' => 'saltwater'],
            ['species' => 'Stingray', 'water_type' => 'saltwater'],
        ];

        foreach ($species as $fish) {
            FishSpecies::updateOrCreate(
                ['species' => $fish['species']],
                $fish
            );
        }
    }
}
