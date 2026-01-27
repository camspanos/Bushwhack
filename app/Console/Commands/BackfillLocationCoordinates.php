<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Services\GeocodingService;
use Illuminate\Console\Command;

class BackfillLocationCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:backfill-coordinates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill latitude and longitude for existing locations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Backfilling coordinates for existing locations...');

        // Get locations that don't have coordinates but have city/state/country
        $locations = Location::whereNull('latitude')
            ->whereNull('longitude')
            ->where(function ($query) {
                $query->whereNotNull('city')
                    ->orWhereNotNull('state')
                    ->orWhereNotNull('country');
            })
            ->get();

        if ($locations->isEmpty()) {
            $this->info('No locations need geocoding.');
            return Command::SUCCESS;
        }

        $this->info("Found {$locations->count()} locations to geocode.");
        $bar = $this->output->createProgressBar($locations->count());
        $bar->start();

        $updated = 0;
        $failed = 0;

        foreach ($locations as $location) {
            $coordinates = GeocodingService::getCoordinates(
                $location->city,
                $location->state,
                $location->country
            );

            if ($coordinates['latitude'] !== null && $coordinates['longitude'] !== null) {
                $location->latitude = $coordinates['latitude'];
                $location->longitude = $coordinates['longitude'];
                $location->saveQuietly(); // Save without triggering observers
                $updated++;
            } else {
                $failed++;
            }

            $bar->advance();
            
            // Rate limiting: sleep for 1 second between requests to respect Nominatim's usage policy
            sleep(1);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Successfully geocoded {$updated} locations.");
        
        if ($failed > 0) {
            $this->warn("Failed to geocode {$failed} locations.");
        }
        
        return Command::SUCCESS;
    }
}

