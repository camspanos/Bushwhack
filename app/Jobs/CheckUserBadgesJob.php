<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckUserBadgesJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BadgeService $badgeService): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning("CheckUserBadgesJob: User {$this->userId} not found");
            return;
        }

        try {
            // Use syncBadges to both award new badges and revoke ones no longer qualified for
            $result = $badgeService->syncBadges($user);

            if (count($result['earned']) > 0) {
                Log::info("CheckUserBadgesJob: Awarded " . count($result['earned']) . " badges to user {$this->userId}");
            }

            if (count($result['revoked']) > 0) {
                Log::info("CheckUserBadgesJob: Revoked " . count($result['revoked']) . " badges from user {$this->userId}");
            }
        } catch (\Exception $e) {
            Log::error("CheckUserBadgesJob: Error checking badges for user {$this->userId}: " . $e->getMessage());
            throw $e;
        }
    }
}
