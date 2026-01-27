<?php

namespace App\Console\Commands;

use App\Models\FishingLog;
use Illuminate\Console\Command;

class UpdateFishingLogMoonPhases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fishing-logs:update-moon-phases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update moon phases for all existing fishing logs based on their dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update moon phases for all fishing logs...');

        $fishingLogs = FishingLog::all();
        $totalLogs = $fishingLogs->count();
        $updatedCount = 0;

        $this->info("Found {$totalLogs} fishing logs to process.");

        $progressBar = $this->output->createProgressBar($totalLogs);
        $progressBar->start();

        foreach ($fishingLogs as $log) {
            if ($log->date) {
                $moonPhase = $this->calculateMoonPhase($log->date);
                $log->moon_phase = $moonPhase;
                $log->save();
                $updatedCount++;
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Successfully updated {$updatedCount} fishing logs with moon phases.");

        return Command::SUCCESS;
    }

    /**
     * Calculate moon phase based on date
     * This matches the JavaScript implementation in the frontend
     *
     * @param \Carbon\Carbon $date
     * @return string
     */
    private function calculateMoonPhase($date): string
    {
        // Known new moon: January 6, 2000, 18:14 UTC
        $knownNewMoon = strtotime('2000-01-06 18:14:00');
        $targetDate = $date->timestamp;

        // Lunar cycle is approximately 29.53 days
        $lunarCycle = 29.53058867;

        // Calculate days since known new moon
        $daysSinceNewMoon = ($targetDate - $knownNewMoon) / (60 * 60 * 24);

        // Calculate position in current lunar cycle (0-29.53)
        $phase = fmod(fmod($daysSinceNewMoon, $lunarCycle) + $lunarCycle, $lunarCycle);

        // Determine moon phase name
        if ($phase < 1.84566) return 'New Moon';
        if ($phase < 7.38264) return 'Waxing Crescent';
        if ($phase < 9.22830) return 'First Quarter';
        if ($phase < 14.76528) return 'Waxing Gibbous';
        if ($phase < 16.61094) return 'Full Moon';
        if ($phase < 22.14792) return 'Waning Gibbous';
        if ($phase < 23.99358) return 'Last Quarter';
        if ($phase < 29.53059) return 'Waning Crescent';

        return 'New Moon';
    }
}

