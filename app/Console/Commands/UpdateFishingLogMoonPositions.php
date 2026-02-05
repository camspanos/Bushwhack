<?php

namespace App\Console\Commands;

use App\Models\FishingLog;
use App\Services\MoonPositionCalculator;
use Illuminate\Console\Command;

class UpdateFishingLogMoonPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fishing-logs:update-moon-positions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update moon positions for all existing fishing logs based on their date, time, and location';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update moon positions for all fishing logs...');

        // Only get logs that have a location with coordinates
        $fishingLogs = FishingLog::with('location')->get();
        $totalLogs = $fishingLogs->count();
        $updatedCount = 0;
        $skippedCount = 0;

        $this->info("Found {$totalLogs} fishing logs to process.");

        $progressBar = $this->output->createProgressBar($totalLogs);
        $progressBar->start();

        foreach ($fishingLogs as $log) {
            // Skip if no date or no location with coordinates
            if (!$log->date || !$log->location || !$log->location->latitude || !$log->location->longitude) {
                $skippedCount++;
                $progressBar->advance();
                continue;
            }

            $moonData = MoonPositionCalculator::calculate(
                $log->date,
                $log->time,
                $log->location->latitude,
                $log->location->longitude
            );

            if ($moonData['altitude'] !== null) {
                $log->moon_altitude = $moonData['altitude'];
                $log->moon_position = $moonData['position'];
                $log->save();
                $updatedCount++;
            } else {
                $skippedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Successfully updated {$updatedCount} fishing logs with moon positions.");
        $this->info("Skipped {$skippedCount} fishing logs (missing date, location, or coordinates).");

        return Command::SUCCESS;
    }
}

