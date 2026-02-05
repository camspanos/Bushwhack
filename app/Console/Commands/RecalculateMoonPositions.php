<?php

namespace App\Console\Commands;

use App\Models\FishingLog;
use App\Services\MoonPositionCalculator;
use Illuminate\Console\Command;

class RecalculateMoonPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fishing-logs:recalculate-moon-positions 
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate moon positions for all fishing logs using the updated Solunar Theory calculator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made');
        }

        // Get all fishing logs with a location that has coordinates
        $fishingLogs = FishingLog::with('location')
            ->whereNotNull('user_location_id')
            ->get();

        $this->info("Found {$fishingLogs->count()} fishing logs with locations");

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($fishingLogs->count());
        $bar->start();

        foreach ($fishingLogs as $log) {
            $bar->advance();

            // Skip if location doesn't have coordinates
            if (!$log->location || !$log->location->latitude || !$log->location->longitude) {
                $skipped++;
                continue;
            }

            try {
                // Calculate new moon position
                $moonData = MoonPositionCalculator::calculate(
                    $log->date,
                    $log->time,
                    $log->location->latitude,
                    $log->location->longitude
                );

                if ($moonData['position']) {
                    $oldPosition = $log->moon_position;
                    $newPosition = $moonData['position'];
                    $newAltitude = $moonData['altitude'];

                    if (!$dryRun) {
                        $log->update([
                            'moon_position' => $newPosition,
                            'moon_altitude' => $newAltitude,
                        ]);
                    }

                    $updated++;

                    // Show changes in verbose mode
                    if ($this->output->isVerbose()) {
                        $this->newLine();
                        $this->line("  Log #{$log->id}: {$oldPosition} → {$newPosition} (alt: {$newAltitude}°)");
                    }
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                if ($this->output->isVerbose()) {
                    $this->newLine();
                    $this->error("  Error on log #{$log->id}: {$e->getMessage()}");
                }
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Summary:");
        $this->line("  Updated: {$updated}");
        $this->line("  Skipped: {$skipped} (no coordinates or couldn't calculate)");
        $this->line("  Errors: {$errors}");

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}

