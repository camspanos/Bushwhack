<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\FishingLog;
use App\Models\User;
use App\Models\UserFish;
use App\Models\UserFly;
use App\Models\UserFriend;
use App\Models\UserLocation;
use App\Models\UserRod;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class FishingLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates fishing logs for user 1001 with sample locations, equipment, and catches.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Check if user 1001 exists
        $user = User::find(1001);
        if (!$user) {
            $this->command->error('User 1001 does not exist. Please create the user first.');
            return;
        }

        $this->command->info('Creating fishing logs for user 1001...');

        // Get US country for locations
        $usCountry = Country::where('code', 'US')->first();

        // Create sample locations for user 1001
        $locations = [
            ['name' => 'Yellowstone River', 'city' => 'Livingston', 'state' => 'Montana', 'latitude' => 45.6587, 'longitude' => -110.5603],
            ['name' => 'Madison River', 'city' => 'Ennis', 'state' => 'Montana', 'latitude' => 45.3494, 'longitude' => -111.7319],
            ['name' => 'Snake River', 'city' => 'Jackson', 'state' => 'Wyoming', 'latitude' => 43.4799, 'longitude' => -110.7624],
            ['name' => 'Green River', 'city' => 'Dutch John', 'state' => 'Utah', 'latitude' => 40.9294, 'longitude' => -109.3988],
            ['name' => 'Deschutes River', 'city' => 'Maupin', 'state' => 'Oregon', 'latitude' => 45.1765, 'longitude' => -121.0795],
        ];

        $userLocations = [];
        foreach ($locations as $location) {
            $userLocations[] = UserLocation::firstOrCreate(
                ['user_id' => $user->id, 'name' => $location['name']],
                [
                    'city' => $location['city'],
                    'state' => $location['state'],
                    'country_id' => $usCountry->id,
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                ]
            );
        }

        // Create sample rods for user 1001
        $rods = [
            ['rod_name' => 'Orvis Clearwater', 'rod_weight' => '5', 'rod_length' => '9\'0"', 'reel' => 'Orvis Battenkill', 'line' => 'WF5F'],
            ['rod_name' => 'Sage X', 'rod_weight' => '6', 'rod_length' => '9\'0"', 'reel' => 'Sage Spectrum', 'line' => 'WF6F'],
            ['rod_name' => 'Scott Meridian', 'rod_weight' => '4', 'rod_length' => '8\'6"', 'reel' => 'Ross Reels Evolution', 'line' => 'WF4F'],
        ];

        $userRods = [];
        foreach ($rods as $rod) {
            $userRods[] = UserRod::firstOrCreate(
                ['user_id' => $user->id, 'rod_name' => $rod['rod_name']],
                $rod
            );
        }

        // Create sample fish species for user 1001
        $fishSpecies = [
            ['species' => 'Rainbow Trout', 'water_type' => 'freshwater'],
            ['species' => 'Brown Trout', 'water_type' => 'freshwater'],
            ['species' => 'Cutthroat Trout', 'water_type' => 'freshwater'],
            ['species' => 'Brook Trout', 'water_type' => 'freshwater'],
            ['species' => 'Lake Trout', 'water_type' => 'freshwater'],
        ];

        $userFish = [];
        foreach ($fishSpecies as $fish) {
            $userFish[] = UserFish::firstOrCreate(
                ['user_id' => $user->id, 'species' => $fish['species']],
                $fish
            );
        }

        // Create sample flies for user 1001
        $flies = [
            ['name' => 'Elk Hair Caddis', 'color' => 'Tan', 'size' => '14', 'type' => 'Dry'],
            ['name' => 'Parachute Adams', 'color' => 'Gray', 'size' => '16', 'type' => 'Dry'],
            ['name' => 'Pheasant Tail Nymph', 'color' => 'Brown', 'size' => '18', 'type' => 'Nymph'],
            ['name' => 'Woolly Bugger', 'color' => 'Black', 'size' => '8', 'type' => 'Streamer'],
            ['name' => 'Copper John', 'color' => 'Copper', 'size' => '16', 'type' => 'Nymph'],
            ['name' => 'Stimulator', 'color' => 'Orange', 'size' => '12', 'type' => 'Dry'],
        ];

        $userFlies = [];
        foreach ($flies as $fly) {
            $userFlies[] = UserFly::firstOrCreate(
                ['user_id' => $user->id, 'name' => $fly['name'], 'size' => $fly['size']],
                $fly
            );
        }

        // Create sample friends for user 1001
        $friendNames = ['John Smith', 'Sarah Johnson', 'Mike Davis'];
        $userFriends = [];
        foreach ($friendNames as $friendName) {
            $userFriends[] = UserFriend::firstOrCreate(
                ['user_id' => $user->id, 'name' => $friendName],
                ['name' => $friendName]
            );
        }

        // Moon phases
        $moonPhases = ['New Moon', 'Waxing Crescent', 'First Quarter', 'Waxing Gibbous', 'Full Moon', 'Waning Gibbous', 'Last Quarter', 'Waning Crescent'];

        // Fishing styles
        $styles = ['Dry Fly', 'Nymph', 'Streamer', 'Euro Nymphing', 'Indicator Nymphing'];

        // Create 50 fishing logs spread across the last 2 years
        // Ensure each location and species gets at least some logs
        $logsCreated = 0;

        // First, create at least 2 logs for each location (10 logs total)
        foreach ($userLocations as $location) {
            for ($j = 0; $j < 2; $j++) {
                $date = $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d');
                $hour = $faker->numberBetween(6, 20);
                $minute = $faker->randomElement([0, 15, 30, 45]);
                $time = sprintf('%02d:%02d', $hour, $minute);

                $fishingLog = FishingLog::create([
                    'user_id' => $user->id,
                    'user_location_id' => $location->id,
                    'user_rod_id' => $faker->randomElement($userRods)->id,
                    'user_fish_id' => $faker->randomElement($userFish)->id,
                    'user_fly_id' => $faker->randomElement($userFlies)->id,
                    'date' => $date,
                    'time' => $time,
                    'quantity' => $faker->numberBetween(1, 25),
                    'max_size' => $faker->randomFloat(2, 8, 28),
                    'style' => $faker->randomElement($styles),
                    'moon_phase' => $faker->randomElement($moonPhases),
                    'notes' => $faker->optional(0.7)->sentence(10),
                ]);

                if ($faker->boolean(50) && count($userFriends) > 0) {
                    $numberOfFriends = $faker->numberBetween(0, min(2, count($userFriends)));
                    if ($numberOfFriends > 0) {
                        $selectedFriends = $faker->randomElements($userFriends, $numberOfFriends);
                        $friendIds = array_map(fn($friend) => $friend->id, $selectedFriends);
                        $fishingLog->friends()->attach($friendIds);
                    }
                }

                $logsCreated++;
            }
        }

        // Then create at least 2 logs for each fish species (10 logs total)
        foreach ($userFish as $fish) {
            for ($j = 0; $j < 2; $j++) {
                $date = $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d');
                $hour = $faker->numberBetween(6, 20);
                $minute = $faker->randomElement([0, 15, 30, 45]);
                $time = sprintf('%02d:%02d', $hour, $minute);

                $fishingLog = FishingLog::create([
                    'user_id' => $user->id,
                    'user_location_id' => $faker->randomElement($userLocations)->id,
                    'user_rod_id' => $faker->randomElement($userRods)->id,
                    'user_fish_id' => $fish->id,
                    'user_fly_id' => $faker->randomElement($userFlies)->id,
                    'date' => $date,
                    'time' => $time,
                    'quantity' => $faker->numberBetween(1, 25),
                    'max_size' => $faker->randomFloat(2, 8, 28),
                    'style' => $faker->randomElement($styles),
                    'moon_phase' => $faker->randomElement($moonPhases),
                    'notes' => $faker->optional(0.7)->sentence(10),
                ]);

                if ($faker->boolean(50) && count($userFriends) > 0) {
                    $numberOfFriends = $faker->numberBetween(0, min(2, count($userFriends)));
                    if ($numberOfFriends > 0) {
                        $selectedFriends = $faker->randomElements($userFriends, $numberOfFriends);
                        $friendIds = array_map(fn($friend) => $friend->id, $selectedFriends);
                        $fishingLog->friends()->attach($friendIds);
                    }
                }

                $logsCreated++;
            }
        }

        // Create 5 logs where no fish were caught (skunked days)
        for ($i = 0; $i < 5; $i++) {
            $date = $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d');
            $hour = $faker->numberBetween(6, 20);
            $minute = $faker->randomElement([0, 15, 30, 45]);
            $time = sprintf('%02d:%02d', $hour, $minute);

            $fishingLog = FishingLog::create([
                'user_id' => $user->id,
                'user_location_id' => $faker->randomElement($userLocations)->id,
                'user_rod_id' => $faker->randomElement($userRods)->id,
                'user_fish_id' => null, // No fish caught
                'user_fly_id' => $faker->randomElement($userFlies)->id,
                'date' => $date,
                'time' => $time,
                'quantity' => 0, // Zero fish caught
                'max_size' => null, // No size since no fish caught
                'style' => $faker->randomElement($styles),
                'moon_phase' => $faker->randomElement($moonPhases),
                'notes' => $faker->randomElement([
                    'Tough day on the water. No bites.',
                    'Water was too high and muddy.',
                    'Beautiful day but fish weren\'t biting.',
                    'Tried everything in the box. Nothing worked.',
                    'Got skunked but enjoyed the scenery.',
                ]),
            ]);

            if ($faker->boolean(50) && count($userFriends) > 0) {
                $numberOfFriends = $faker->numberBetween(0, min(2, count($userFriends)));
                if ($numberOfFriends > 0) {
                    $selectedFriends = $faker->randomElements($userFriends, $numberOfFriends);
                    $friendIds = array_map(fn($friend) => $friend->id, $selectedFriends);
                    $fishingLog->friends()->attach($friendIds);
                }
            }

            $logsCreated++;
        }

        // Fill remaining logs (25 more) with random combinations
        for ($i = 0; $i < 25; $i++) {
            $date = $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d');
            $hour = $faker->numberBetween(6, 20);
            $minute = $faker->randomElement([0, 15, 30, 45]);
            $time = sprintf('%02d:%02d', $hour, $minute);

            $fishingLog = FishingLog::create([
                'user_id' => $user->id,
                'user_location_id' => $faker->randomElement($userLocations)->id,
                'user_rod_id' => $faker->randomElement($userRods)->id,
                'user_fish_id' => $faker->randomElement($userFish)->id,
                'user_fly_id' => $faker->randomElement($userFlies)->id,
                'date' => $date,
                'time' => $time,
                'quantity' => $faker->numberBetween(1, 25),
                'max_size' => $faker->randomFloat(2, 8, 28),
                'style' => $faker->randomElement($styles),
                'moon_phase' => $faker->randomElement($moonPhases),
                'notes' => $faker->optional(0.7)->sentence(10),
            ]);

            if ($faker->boolean(50) && count($userFriends) > 0) {
                $numberOfFriends = $faker->numberBetween(0, min(2, count($userFriends)));
                if ($numberOfFriends > 0) {
                    $selectedFriends = $faker->randomElements($userFriends, $numberOfFriends);
                    $friendIds = array_map(fn($friend) => $friend->id, $selectedFriends);
                    $fishingLog->friends()->attach($friendIds);
                }
            }

            $logsCreated++;
        }

        $this->command->info("Successfully created {$logsCreated} fishing logs for user 1001!");
        $this->command->info("Created " . count($userLocations) . " locations");
        $this->command->info("Created " . count($userRods) . " rods");
        $this->command->info("Created " . count($userFish) . " fish species");
        $this->command->info("Created " . count($userFlies) . " flies");
        $this->command->info("Created " . count($userFriends) . " friends");
    }
}

