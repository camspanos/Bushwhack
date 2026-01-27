<?php

namespace App\Console\Commands;

use App\Models\FishingLog;
use App\Services\TimeOfDayCalculator;
use Illuminate\Console\Command;

class BackfillTimeOfDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fishing-logs:backfill-time-of-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill time_of_day for existing fishing logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Backfilling time_of_day for existing fishing logs...');

        $logs = FishingLog::with('location')->get();
        $updated = 0;

        foreach ($logs as $log) {
            $latitude = $log->location?->latitude;
            $longitude = $log->location?->longitude;

            $timeOfDay = TimeOfDayCalculator::calculate(
                $log->time,
                $log->date,
                $latitude,
                $longitude
            );

            if ($timeOfDay !== $log->time_of_day) {
                $log->time_of_day = $timeOfDay;
                $log->saveQuietly(); // Save without triggering observers
                $updated++;
            }
        }

        $this->info("Updated {$updated} fishing logs.");
        
        return Command::SUCCESS;
    }
}

