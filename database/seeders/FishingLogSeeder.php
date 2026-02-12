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
use App\Models\UserWeather;
use App\Models\UserWaterCondition;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class FishingLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates fishing logs for user 1001 with sample locations, equipment, weather, water conditions, and catches.
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

        // Get countries for locations
        $countries = [
            'US' => Country::where('code', 'US')->first(),
            'CA' => Country::where('code', 'CA')->first(),
            'MX' => Country::where('code', 'MX')->first(),
            'NZ' => Country::where('code', 'NZ')->first(),
            'AU' => Country::where('code', 'AU')->first(),
            'GB' => Country::where('code', 'GB')->first(),
            'IS' => Country::where('code', 'IS')->first(),
            'AR' => Country::where('code', 'AR')->first(),
            'CL' => Country::where('code', 'CL')->first(),
            'BS' => Country::where('code', 'BS')->first(),
        ];

        // Create sample locations for user 1001 (freshwater and saltwater across multiple countries)
        $locations = [
            // USA - Freshwater (multiple states)
            ['name' => 'Yellowstone River', 'city' => 'Livingston', 'state' => 'Montana', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 45.6587, 'longitude' => -110.5603],
            ['name' => 'Madison River', 'city' => 'Ennis', 'state' => 'Montana', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 45.3494, 'longitude' => -111.7319],
            ['name' => 'Snake River', 'city' => 'Jackson', 'state' => 'Wyoming', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 43.4799, 'longitude' => -110.7624],
            ['name' => 'Green River', 'city' => 'Dutch John', 'state' => 'Utah', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 40.9294, 'longitude' => -109.3988],
            ['name' => 'Deschutes River', 'city' => 'Maupin', 'state' => 'Oregon', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 45.1765, 'longitude' => -121.0795],
            ['name' => 'Henry\'s Fork', 'city' => 'Island Park', 'state' => 'Idaho', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 44.4183, 'longitude' => -111.3713],
            ['name' => 'South Platte River', 'city' => 'Deckers', 'state' => 'Colorado', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 39.2547, 'longitude' => -105.2183],
            ['name' => 'Bighorn River', 'city' => 'Fort Smith', 'state' => 'Montana', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 45.3158, 'longitude' => -107.9311],
            ['name' => 'White River', 'city' => 'Cotter', 'state' => 'Arkansas', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 36.2812, 'longitude' => -92.5268],
            ['name' => 'Au Sable River', 'city' => 'Grayling', 'state' => 'Michigan', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 44.6614, 'longitude' => -84.7147],
            ['name' => 'Salmon River', 'city' => 'Pulaski', 'state' => 'New York', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 43.5667, 'longitude' => -76.1272],
            ['name' => 'Kenai River', 'city' => 'Soldotna', 'state' => 'Alaska', 'country' => 'US', 'water_type' => 'freshwater', 'latitude' => 60.4886, 'longitude' => -151.0583],
            // USA - Saltwater (multiple states)
            ['name' => 'Florida Keys Flats', 'city' => 'Islamorada', 'state' => 'Florida', 'country' => 'US', 'water_type' => 'saltwater', 'latitude' => 24.9243, 'longitude' => -80.6278],
            ['name' => 'Biscayne Bay', 'city' => 'Miami', 'state' => 'Florida', 'country' => 'US', 'water_type' => 'saltwater', 'latitude' => 25.5584, 'longitude' => -80.2139],
            ['name' => 'Mosquito Lagoon', 'city' => 'New Smyrna Beach', 'state' => 'Florida', 'country' => 'US', 'water_type' => 'saltwater', 'latitude' => 28.8947, 'longitude' => -80.8206],
            ['name' => 'Galveston Bay', 'city' => 'Galveston', 'state' => 'Texas', 'country' => 'US', 'water_type' => 'saltwater', 'latitude' => 29.5013, 'longitude' => -94.8306],
            ['name' => 'Montauk Point', 'city' => 'Montauk', 'state' => 'New York', 'country' => 'US', 'water_type' => 'saltwater', 'latitude' => 41.0712, 'longitude' => -71.8573],
            ['name' => 'Cape Cod Flats', 'city' => 'Orleans', 'state' => 'Massachusetts', 'country' => 'US', 'water_type' => 'saltwater', 'latitude' => 41.7898, 'longitude' => -69.9897],
            ['name' => 'San Diego Bay', 'city' => 'San Diego', 'state' => 'California', 'country' => 'US', 'water_type' => 'saltwater', 'latitude' => 32.6867, 'longitude' => -117.1367],
            ['name' => 'Louisiana Marsh', 'city' => 'Venice', 'state' => 'Louisiana', 'country' => 'US', 'water_type' => 'saltwater', 'latitude' => 29.2747, 'longitude' => -89.3528],
            // Canada
            ['name' => 'Bow River', 'city' => 'Calgary', 'state' => 'Alberta', 'country' => 'CA', 'water_type' => 'freshwater', 'latitude' => 51.0447, 'longitude' => -114.0719],
            ['name' => 'Dean River', 'city' => 'Bella Coola', 'state' => 'British Columbia', 'country' => 'CA', 'water_type' => 'freshwater', 'latitude' => 52.3697, 'longitude' => -126.7550],
            ['name' => 'Miramichi River', 'city' => 'Miramichi', 'state' => 'New Brunswick', 'country' => 'CA', 'water_type' => 'freshwater', 'latitude' => 47.0154, 'longitude' => -65.4667],
            // New Zealand
            ['name' => 'Tongariro River', 'city' => 'Turangi', 'state' => 'North Island', 'country' => 'NZ', 'water_type' => 'freshwater', 'latitude' => -38.9903, 'longitude' => 175.8069],
            ['name' => 'Mataura River', 'city' => 'Gore', 'state' => 'South Island', 'country' => 'NZ', 'water_type' => 'freshwater', 'latitude' => -46.0997, 'longitude' => 168.9442],
            // Australia
            ['name' => 'Snowy River', 'city' => 'Jindabyne', 'state' => 'New South Wales', 'country' => 'AU', 'water_type' => 'freshwater', 'latitude' => -36.4167, 'longitude' => 148.6167],
            ['name' => 'Great Barrier Reef', 'city' => 'Cairns', 'state' => 'Queensland', 'country' => 'AU', 'water_type' => 'saltwater', 'latitude' => -16.9186, 'longitude' => 145.7781],
            // United Kingdom
            ['name' => 'River Test', 'city' => 'Stockbridge', 'state' => 'Hampshire', 'country' => 'GB', 'water_type' => 'freshwater', 'latitude' => 51.1133, 'longitude' => -1.4917],
            ['name' => 'River Spey', 'city' => 'Grantown-on-Spey', 'state' => 'Scotland', 'country' => 'GB', 'water_type' => 'freshwater', 'latitude' => 57.3297, 'longitude' => -3.6097],
            // Iceland
            ['name' => 'Laxá í Adaldal', 'city' => 'Húsavík', 'state' => 'Norðurland eystra', 'country' => 'IS', 'water_type' => 'freshwater', 'latitude' => 65.9500, 'longitude' => -17.3333],
            // Argentina/Chile (Patagonia)
            ['name' => 'Rio Grande', 'city' => 'Rio Grande', 'state' => 'Tierra del Fuego', 'country' => 'AR', 'water_type' => 'freshwater', 'latitude' => -53.7878, 'longitude' => -67.7094],
            ['name' => 'Rio Limay', 'city' => 'Bariloche', 'state' => 'Rio Negro', 'country' => 'AR', 'water_type' => 'freshwater', 'latitude' => -41.1335, 'longitude' => -71.3103],
            ['name' => 'Rio Simpson', 'city' => 'Coyhaique', 'state' => 'Aysén', 'country' => 'CL', 'water_type' => 'freshwater', 'latitude' => -45.5752, 'longitude' => -72.0662],
            // Mexico
            ['name' => 'Ascension Bay', 'city' => 'Punta Allen', 'state' => 'Quintana Roo', 'country' => 'MX', 'water_type' => 'saltwater', 'latitude' => 19.7833, 'longitude' => -87.4667],
            // Bahamas
            ['name' => 'Andros Island Flats', 'city' => 'Andros Town', 'state' => 'Andros', 'country' => 'BS', 'water_type' => 'saltwater', 'latitude' => 24.7000, 'longitude' => -77.7667],
        ];

        $userLocations = [];
        foreach ($locations as $location) {
            $countryCode = $location['country'];
            $country = $countries[$countryCode] ?? $countries['US'];
            if (!$country) continue;

            $userLocations[] = UserLocation::firstOrCreate(
                ['user_id' => $user->id, 'name' => $location['name']],
                [
                    'city' => $location['city'],
                    'state' => $location['state'],
                    'water_type' => $location['water_type'],
                    'country_id' => $country->id,
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                ]
            );
        }

        // Create sample rods for user 1001 (various weights and lengths)
        $rods = [
            ['rod_name' => 'Orvis Clearwater', 'rod_weight' => '5', 'rod_length' => '9\'0"', 'reel' => 'Orvis Battenkill', 'line' => 'WF5F'],
            ['rod_name' => 'Sage X', 'rod_weight' => '6', 'rod_length' => '9\'0"', 'reel' => 'Sage Spectrum', 'line' => 'WF6F'],
            ['rod_name' => 'Scott Meridian', 'rod_weight' => '4', 'rod_length' => '8\'6"', 'reel' => 'Ross Reels Evolution', 'line' => 'WF4F'],
            ['rod_name' => 'G. Loomis NRX+', 'rod_weight' => '8', 'rod_length' => '9\'0"', 'reel' => 'Hatch Finatic', 'line' => 'WF8F'],
            ['rod_name' => 'Winston Pure', 'rod_weight' => '3', 'rod_length' => '7\'6"', 'reel' => 'Abel TR', 'line' => 'WF3F'],
            ['rod_name' => 'TFO Axiom II-X', 'rod_weight' => '10', 'rod_length' => '9\'0"', 'reel' => 'Tibor Signature', 'line' => 'WF10F'],
        ];

        $userRods = [];
        foreach ($rods as $rod) {
            $userRods[] = UserRod::firstOrCreate(
                ['user_id' => $user->id, 'rod_name' => $rod['rod_name']],
                $rod
            );
        }

        // Create sample fish species for user 1001 (freshwater and saltwater - extensive list)
        $fishSpecies = [
            // Freshwater - Trout
            ['species' => 'Rainbow Trout', 'water_type' => 'freshwater'],
            ['species' => 'Brown Trout', 'water_type' => 'freshwater'],
            ['species' => 'Cutthroat Trout', 'water_type' => 'freshwater'],
            ['species' => 'Brook Trout', 'water_type' => 'freshwater'],
            ['species' => 'Lake Trout', 'water_type' => 'freshwater'],
            ['species' => 'Golden Trout', 'water_type' => 'freshwater'],
            ['species' => 'Bull Trout', 'water_type' => 'freshwater'],
            ['species' => 'Apache Trout', 'water_type' => 'freshwater'],
            // Freshwater - Salmon
            ['species' => 'Atlantic Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Chinook Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Coho Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Sockeye Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Pink Salmon', 'water_type' => 'freshwater'],
            ['species' => 'Steelhead', 'water_type' => 'freshwater'],
            // Freshwater - Other
            ['species' => 'Largemouth Bass', 'water_type' => 'freshwater'],
            ['species' => 'Smallmouth Bass', 'water_type' => 'freshwater'],
            ['species' => 'Northern Pike', 'water_type' => 'freshwater'],
            ['species' => 'Musky', 'water_type' => 'freshwater'],
            ['species' => 'Walleye', 'water_type' => 'freshwater'],
            ['species' => 'Carp', 'water_type' => 'freshwater'],
            ['species' => 'Grayling', 'water_type' => 'freshwater'],
            ['species' => 'Bluegill', 'water_type' => 'freshwater'],
            ['species' => 'Crappie', 'water_type' => 'freshwater'],
            // Saltwater - Flats
            ['species' => 'Bonefish', 'water_type' => 'saltwater'],
            ['species' => 'Tarpon', 'water_type' => 'saltwater'],
            ['species' => 'Permit', 'water_type' => 'saltwater'],
            ['species' => 'Snook', 'water_type' => 'saltwater'],
            ['species' => 'Redfish', 'water_type' => 'saltwater'],
            // Saltwater - Offshore/Coastal
            ['species' => 'Striped Bass', 'water_type' => 'saltwater'],
            ['species' => 'False Albacore', 'water_type' => 'saltwater'],
            ['species' => 'Bluefish', 'water_type' => 'saltwater'],
            ['species' => 'Roosterfish', 'water_type' => 'saltwater'],
            ['species' => 'Giant Trevally', 'water_type' => 'saltwater'],
            ['species' => 'Barramundi', 'water_type' => 'saltwater'],
            ['species' => 'Sailfish', 'water_type' => 'saltwater'],
            ['species' => 'Mahi Mahi', 'water_type' => 'saltwater'],
            ['species' => 'Yellowfin Tuna', 'water_type' => 'saltwater'],
            ['species' => 'Sea Trout', 'water_type' => 'saltwater'],
        ];

        $userFish = [];
        foreach ($fishSpecies as $fish) {
            $userFish[] = UserFish::firstOrCreate(
                ['user_id' => $user->id, 'species' => $fish['species']],
                $fish
            );
        }

        // Create sample flies for user 1001 (various sizes and types)
        $flies = [
            // Dry Flies
            ['name' => 'Elk Hair Caddis', 'color' => 'Tan', 'size' => '14', 'type' => 'Dry'],
            ['name' => 'Parachute Adams', 'color' => 'Gray', 'size' => '16', 'type' => 'Dry'],
            ['name' => 'Stimulator', 'color' => 'Orange', 'size' => '12', 'type' => 'Dry'],
            ['name' => 'Blue Wing Olive', 'color' => 'Olive', 'size' => '20', 'type' => 'Dry'],
            ['name' => 'Royal Wulff', 'color' => 'Red/White', 'size' => '14', 'type' => 'Dry'],
            ['name' => 'Pale Morning Dun', 'color' => 'Yellow', 'size' => '18', 'type' => 'Dry'],
            ['name' => 'Griffith\'s Gnat', 'color' => 'Black', 'size' => '22', 'type' => 'Dry'],
            ['name' => 'Chernobyl Ant', 'color' => 'Black/Orange', 'size' => '8', 'type' => 'Dry'],
            ['name' => 'Hopper', 'color' => 'Tan', 'size' => '6', 'type' => 'Dry'],
            // Nymphs
            ['name' => 'Pheasant Tail Nymph', 'color' => 'Brown', 'size' => '18', 'type' => 'Nymph'],
            ['name' => 'Copper John', 'color' => 'Copper', 'size' => '16', 'type' => 'Nymph'],
            ['name' => 'San Juan Worm', 'color' => 'Red', 'size' => '10', 'type' => 'Nymph'],
            ['name' => 'Hare\'s Ear', 'color' => 'Natural', 'size' => '14', 'type' => 'Nymph'],
            ['name' => 'Prince Nymph', 'color' => 'Peacock', 'size' => '12', 'type' => 'Nymph'],
            ['name' => 'Zebra Midge', 'color' => 'Black/Silver', 'size' => '20', 'type' => 'Nymph'],
            ['name' => 'Perdigon', 'color' => 'Purple', 'size' => '16', 'type' => 'Nymph'],
            ['name' => 'Pat\'s Rubber Legs', 'color' => 'Brown', 'size' => '8', 'type' => 'Nymph'],
            // Streamers
            ['name' => 'Woolly Bugger', 'color' => 'Black', 'size' => '8', 'type' => 'Streamer'],
            ['name' => 'Clouser Minnow', 'color' => 'Chartreuse/White', 'size' => '2', 'type' => 'Streamer'],
            ['name' => 'Zonker', 'color' => 'Olive', 'size' => '4', 'type' => 'Streamer'],
            ['name' => 'Muddler Minnow', 'color' => 'Natural', 'size' => '6', 'type' => 'Streamer'],
            ['name' => 'Sculpin', 'color' => 'Brown', 'size' => '4', 'type' => 'Streamer'],
            ['name' => 'Articulated Streamer', 'color' => 'Black/Olive', 'size' => '2', 'type' => 'Streamer'],
            // Saltwater
            ['name' => 'Gotcha', 'color' => 'Pink', 'size' => '4', 'type' => 'Saltwater'],
            ['name' => 'Crazy Charlie', 'color' => 'Tan', 'size' => '6', 'type' => 'Saltwater'],
            ['name' => 'Deceiver', 'color' => 'White', 'size' => '2/0', 'type' => 'Saltwater'],
            ['name' => 'EP Baitfish', 'color' => 'Olive/White', 'size' => '1/0', 'type' => 'Saltwater'],
            ['name' => 'Tarpon Toad', 'color' => 'Purple/Black', 'size' => '3/0', 'type' => 'Saltwater'],
            ['name' => 'Crab Pattern', 'color' => 'Tan', 'size' => '2', 'type' => 'Saltwater'],
            ['name' => 'Surf Candy', 'color' => 'Silver', 'size' => '1', 'type' => 'Saltwater'],
            ['name' => 'Gurgler', 'color' => 'Chartreuse', 'size' => '1/0', 'type' => 'Saltwater'],
        ];

        $userFlies = [];
        foreach ($flies as $fly) {
            $userFlies[] = UserFly::firstOrCreate(
                ['user_id' => $user->id, 'name' => $fly['name'], 'size' => $fly['size']],
                $fly
            );
        }

        // Create sample weather conditions for user 1001
        $weatherConditions = [
            ['temperature' => 65, 'cloud' => 'Sunny', 'wind' => 'Calm', 'precipitation' => 'None', 'barometric_pressure' => 'High'],
            ['temperature' => 72, 'cloud' => 'Partly Cloudy', 'wind' => 'Light', 'precipitation' => 'None', 'barometric_pressure' => 'Stable'],
            ['temperature' => 58, 'cloud' => 'Overcast', 'wind' => 'Moderate', 'precipitation' => 'None', 'barometric_pressure' => 'Falling'],
            ['temperature' => 45, 'cloud' => 'Cloudy', 'wind' => 'Light', 'precipitation' => 'Light Rain', 'barometric_pressure' => 'Low'],
            ['temperature' => 80, 'cloud' => 'Sunny', 'wind' => 'Calm', 'precipitation' => 'None', 'barometric_pressure' => 'High'],
            ['temperature' => 55, 'cloud' => 'Overcast', 'wind' => 'Strong', 'precipitation' => 'Rain', 'barometric_pressure' => 'Falling'],
            ['temperature' => 68, 'cloud' => 'Partly Cloudy', 'wind' => 'Moderate', 'precipitation' => 'None', 'barometric_pressure' => 'Rising'],
            ['temperature' => 42, 'cloud' => 'Cloudy', 'wind' => 'Light', 'precipitation' => 'Snow', 'barometric_pressure' => 'Low'],
        ];

        $userWeathers = [];
        foreach ($weatherConditions as $weather) {
            $userWeathers[] = UserWeather::firstOrCreate(
                ['user_id' => $user->id, 'temperature' => $weather['temperature'], 'cloud' => $weather['cloud']],
                $weather
            );
        }

        // Create sample water conditions for user 1001
        $waterConditions = [
            ['temperature' => 52, 'clarity' => 'Clear', 'level' => 'Normal', 'speed' => 'Moderate', 'surface_condition' => 'Calm', 'tide' => null],
            ['temperature' => 48, 'clarity' => 'Slightly Stained', 'level' => 'Low', 'speed' => 'Slow', 'surface_condition' => 'Calm', 'tide' => null],
            ['temperature' => 56, 'clarity' => 'Clear', 'level' => 'High', 'speed' => 'Fast', 'surface_condition' => 'Choppy', 'tide' => null],
            ['temperature' => 62, 'clarity' => 'Murky', 'level' => 'Normal', 'speed' => 'Moderate', 'surface_condition' => 'Rippled', 'tide' => null],
            ['temperature' => 45, 'clarity' => 'Crystal Clear', 'level' => 'Low', 'speed' => 'Slow', 'surface_condition' => 'Glass', 'tide' => null],
            // Saltwater conditions with tides
            ['temperature' => 78, 'clarity' => 'Clear', 'level' => 'Normal', 'speed' => 'Slow', 'surface_condition' => 'Calm', 'tide' => 'Incoming'],
            ['temperature' => 82, 'clarity' => 'Slightly Stained', 'level' => 'Normal', 'speed' => 'Moderate', 'surface_condition' => 'Rippled', 'tide' => 'Outgoing'],
            ['temperature' => 75, 'clarity' => 'Clear', 'level' => 'High', 'speed' => 'Fast', 'surface_condition' => 'Choppy', 'tide' => 'High'],
        ];

        $userWaterConditions = [];
        foreach ($waterConditions as $water) {
            $userWaterConditions[] = UserWaterCondition::firstOrCreate(
                ['user_id' => $user->id, 'temperature' => $water['temperature'], 'clarity' => $water['clarity']],
                $water
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

        // Moon phases and positions
        $moonPhases = ['New Moon', 'Waxing Crescent', 'First Quarter', 'Waxing Gibbous', 'Full Moon', 'Waning Gibbous', 'Last Quarter', 'Waning Crescent'];
        $moonPositions = ['Rising', 'Overhead', 'Setting', 'Underfoot'];

        // Time of day options
        $timesOfDay = ['Dawn', 'Morning', 'Midday', 'Afternoon', 'Evening', 'Dusk'];

        // Fishing styles
        $styles = ['Dry Fly', 'Nymph', 'Streamer', 'Euro Nymphing', 'Indicator Nymphing', 'Sight Fishing'];

        // Helper function to create a fishing log with all new fields
        $createFishingLog = function ($logData) use ($faker, $user, $userWeathers, $userWaterConditions, $moonPhases, $moonPositions, $timesOfDay, $styles, $userFriends) {
            // Generate time-based data
            $hour = $faker->numberBetween(6, 20);
            $minute = $faker->randomElement([0, 15, 30, 45]);
            $time = sprintf('%02d:%02d', $hour, $minute);

            // Determine time of day based on hour
            if ($hour < 7) {
                $timeOfDay = 'Dawn';
            } elseif ($hour < 11) {
                $timeOfDay = 'Morning';
            } elseif ($hour < 14) {
                $timeOfDay = 'Midday';
            } elseif ($hour < 17) {
                $timeOfDay = 'Afternoon';
            } elseif ($hour < 19) {
                $timeOfDay = 'Evening';
            } else {
                $timeOfDay = 'Dusk';
            }

            // Generate size and weight data
            $maxSize = $logData['max_size'] ?? $faker->randomFloat(2, 8, 28);
            $maxWeight = $maxSize ? round($maxSize * $faker->randomFloat(2, 0.04, 0.08), 2) : null; // Rough weight estimate
            $quantity = $logData['quantity'] ?? $faker->numberBetween(1, 25);

            // Calculate averages (slightly less than max)
            $avgSize = $maxSize && $quantity > 1 ? round($maxSize * $faker->randomFloat(2, 0.6, 0.9), 2) : $maxSize;
            $avgWeight = $maxWeight && $quantity > 1 ? round($maxWeight * $faker->randomFloat(2, 0.6, 0.9), 2) : $maxWeight;

            $fishingLog = FishingLog::create(array_merge([
                'user_id' => $user->id,
                'user_weather_id' => $faker->randomElement($userWeathers)->id,
                'user_water_condition_id' => $faker->randomElement($userWaterConditions)->id,
                'time' => $time,
                'time_of_day' => $timeOfDay,
                'quantity' => $quantity,
                'max_size' => $maxSize,
                'max_weight' => $maxWeight,
                'avg_size' => $avgSize,
                'avg_weight' => $avgWeight,
                'style' => $faker->randomElement($styles),
                'moon_phase' => $faker->randomElement($moonPhases),
                'moon_altitude' => $faker->randomFloat(2, -90, 90), // -90 to 90 degrees
                'moon_position' => $faker->randomElement($moonPositions),
                'notes' => $logData['notes'] ?? $faker->optional(0.7)->sentence(10),
            ], $logData));

            // Attach friends randomly
            if ($faker->boolean(50) && count($userFriends) > 0) {
                $numberOfFriends = $faker->numberBetween(0, min(2, count($userFriends)));
                if ($numberOfFriends > 0) {
                    $selectedFriends = $faker->randomElements($userFriends, $numberOfFriends);
                    $friendIds = array_map(fn($friend) => $friend->id, $selectedFriends);
                    $fishingLog->friends()->attach($friendIds);
                }
            }

            return $fishingLog;
        };

        // Separate freshwater and saltwater locations/fish for realistic matching
        $freshwaterLocations = array_filter($userLocations, fn($loc) => $loc->water_type === 'freshwater');
        $saltwaterLocations = array_filter($userLocations, fn($loc) => $loc->water_type === 'saltwater');
        $freshwaterFish = array_filter($userFish, fn($fish) => $fish->water_type === 'freshwater');
        $saltwaterFish = array_filter($userFish, fn($fish) => $fish->water_type === 'saltwater');
        $freshwaterFlies = array_filter($userFlies, fn($fly) => $fly->type !== 'Saltwater');
        $saltwaterFlies = array_filter($userFlies, fn($fly) => $fly->type === 'Saltwater' || $fly->type === 'Streamer');

        // Re-index arrays
        $freshwaterLocations = array_values($freshwaterLocations);
        $saltwaterLocations = array_values($saltwaterLocations);
        $freshwaterFish = array_values($freshwaterFish);
        $saltwaterFish = array_values($saltwaterFish);
        $freshwaterFlies = array_values($freshwaterFlies);
        $saltwaterFlies = array_values($saltwaterFlies);

        // Create 300+ fishing logs spread across the last 3 years
        $logsCreated = 0;

        // First, create at least 3 logs for each location (105 logs for 35 locations)
        foreach ($userLocations as $location) {
            $isSaltwater = $location->water_type === 'saltwater';
            $fishPool = $isSaltwater ? $saltwaterFish : $freshwaterFish;
            $flyPool = $isSaltwater ? $saltwaterFlies : $freshwaterFlies;

            for ($j = 0; $j < 3; $j++) {
                $date = $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d');
                $createFishingLog([
                    'user_location_id' => $location->id,
                    'user_rod_id' => $faker->randomElement($userRods)->id,
                    'user_fish_id' => $faker->randomElement($fishPool)->id,
                    'user_fly_id' => $faker->randomElement($flyPool)->id,
                    'date' => $date,
                ]);
                $logsCreated++;
            }
        }

        // Then create at least 3 logs for each fish species (114 logs for 38 species)
        foreach ($userFish as $fish) {
            $isSaltwater = $fish->water_type === 'saltwater';
            $locationPool = $isSaltwater ? $saltwaterLocations : $freshwaterLocations;
            $flyPool = $isSaltwater ? $saltwaterFlies : $freshwaterFlies;

            for ($j = 0; $j < 3; $j++) {
                $date = $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d');
                $createFishingLog([
                    'user_location_id' => $faker->randomElement($locationPool)->id,
                    'user_rod_id' => $faker->randomElement($userRods)->id,
                    'user_fish_id' => $fish->id,
                    'user_fly_id' => $faker->randomElement($flyPool)->id,
                    'date' => $date,
                ]);
                $logsCreated++;
            }
        }

        // Create 10 logs where no fish were caught (skunked days)
        $skunkedNotes = [
            'Tough day on the water. No bites.',
            'Water was too high and muddy.',
            'Beautiful day but fish weren\'t biting.',
            'Tried everything in the box. Nothing worked.',
            'Got skunked but enjoyed the scenery.',
            'Wind made casting impossible.',
            'Fish were there but wouldn\'t eat.',
            'Wrong time of year for this spot.',
            'Too much boat traffic today.',
            'Water was too warm, fish were deep.',
        ];
        for ($i = 0; $i < 10; $i++) {
            $date = $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d');
            $createFishingLog([
                'user_location_id' => $faker->randomElement($userLocations)->id,
                'user_rod_id' => $faker->randomElement($userRods)->id,
                'user_fish_id' => null,
                'user_fly_id' => $faker->randomElement($userFlies)->id,
                'date' => $date,
                'quantity' => 0,
                'max_size' => null,
                'notes' => $skunkedNotes[$i],
            ]);
            $logsCreated++;
        }

        // Create trophy fish logs - freshwater (20 logs)
        $trophyFreshwaterNotes = [
            'Personal best! What a fight!',
            'Monster brown on a dry fly!',
            'Biggest fish of the season.',
            'Trophy rainbow - released to fight another day.',
            'Incredible catch on light tippet.',
        ];
        for ($i = 0; $i < 20; $i++) {
            $date = $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d');
            $createFishingLog([
                'user_location_id' => $faker->randomElement($freshwaterLocations)->id,
                'user_rod_id' => $faker->randomElement($userRods)->id,
                'user_fish_id' => $faker->randomElement($freshwaterFish)->id,
                'user_fly_id' => $faker->randomElement($freshwaterFlies)->id,
                'date' => $date,
                'quantity' => $faker->numberBetween(1, 3),
                'max_size' => $faker->randomFloat(2, 22, 32),
                'notes' => $faker->randomElement($trophyFreshwaterNotes),
            ]);
            $logsCreated++;
        }

        // Create trophy fish logs - saltwater (15 logs with bigger sizes)
        $trophySaltwaterNotes = [
            'Giant tarpon - jumped 6 times!',
            'Finally got my permit on fly!',
            'Bonefish of a lifetime.',
            'Tailing redfish in skinny water.',
            'Epic battle with a big snook.',
        ];
        for ($i = 0; $i < 15; $i++) {
            $date = $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d');
            $createFishingLog([
                'user_location_id' => $faker->randomElement($saltwaterLocations)->id,
                'user_rod_id' => $faker->randomElement($userRods)->id,
                'user_fish_id' => $faker->randomElement($saltwaterFish)->id,
                'user_fly_id' => $faker->randomElement($saltwaterFlies)->id,
                'date' => $date,
                'quantity' => $faker->numberBetween(1, 3),
                'max_size' => $faker->randomFloat(2, 28, 60),
                'notes' => $faker->randomElement($trophySaltwaterNotes),
            ]);
            $logsCreated++;
        }

        // Fill with more random freshwater logs (50 more)
        for ($i = 0; $i < 50; $i++) {
            $date = $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d');
            $createFishingLog([
                'user_location_id' => $faker->randomElement($freshwaterLocations)->id,
                'user_rod_id' => $faker->randomElement($userRods)->id,
                'user_fish_id' => $faker->randomElement($freshwaterFish)->id,
                'user_fly_id' => $faker->randomElement($freshwaterFlies)->id,
                'date' => $date,
            ]);
            $logsCreated++;
        }

        // Fill with more random saltwater logs (30 more)
        for ($i = 0; $i < 30; $i++) {
            $date = $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d');
            $createFishingLog([
                'user_location_id' => $faker->randomElement($saltwaterLocations)->id,
                'user_rod_id' => $faker->randomElement($userRods)->id,
                'user_fish_id' => $faker->randomElement($saltwaterFish)->id,
                'user_fly_id' => $faker->randomElement($saltwaterFlies)->id,
                'date' => $date,
            ]);
            $logsCreated++;
        }

        $this->command->info("Successfully created {$logsCreated} fishing logs for user 1001!");
        $this->command->info("Created " . count($userLocations) . " locations across " . count($countries) . " countries");
        $this->command->info("Created " . count($userRods) . " rods (various weights/lengths)");
        $this->command->info("Created " . count($userFish) . " fish species (" . count($freshwaterFish) . " freshwater, " . count($saltwaterFish) . " saltwater)");
        $this->command->info("Created " . count($userFlies) . " flies (various sizes)");
        $this->command->info("Created " . count($userWeathers) . " weather conditions");
        $this->command->info("Created " . count($userWaterConditions) . " water conditions");
        $this->command->info("Created " . count($userFriends) . " friends");
    }
}

