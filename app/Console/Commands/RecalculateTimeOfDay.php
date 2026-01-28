<?php

namespace App\Console\Commands;

use App\Models\FishingLog;
use App\Services\TimeOfDayCalculator;
use Illuminate\Console\Command;

class RecalculateTimeOfDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fishing-logs:recalculate-time-of-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate time_of_day for all fishing logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating time_of_day for all fishing logs...');

        $logs = FishingLog::with('location')->whereNotNull('time')->get();
        $count = 0;
        $total = $logs->count();

        $this->info("Found {$total} fishing logs with time values.");

        foreach ($logs as $log) {
            $latitude = null;
            $longitude = null;

            // Get coordinates from location if available
            if ($log->user_location_id && $log->location) {
                $latitude = $log->location->latitude;
                $longitude = $log->location->longitude;
            }

            // Get raw time value (HH:MM:SS format from database)
            $timeValue = $log->getRawOriginal('time');

            $newTimeOfDay = TimeOfDayCalculator::calculate(
                $timeValue,
                $log->date,
                $latitude,
                $longitude
            );

            if ($log->time_of_day !== $newTimeOfDay) {
                $log->time_of_day = $newTimeOfDay;
                $log->saveQuietly(); // Save without triggering observers
                $count++;
                $this->info("  ✓ Updated log #{$log->id}: {$log->time} -> {$newTimeOfDay}");
            }
        }

        $this->info("Updated {$count} out of {$total} fishing logs.");
        return Command::SUCCESS;
    }
}
