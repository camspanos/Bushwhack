<?php

namespace Tests\Unit\Services;

use App\Models\FishingLog;
use App\Models\User;
use App\Models\UserFish;
use App\Models\UserFly;
use App\Services\DashboardDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardDataService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardDataService();
        $this->user = User::factory()->create();
    }

    public function test_get_available_years_returns_current_year_when_no_logs(): void
    {
        $years = $this->service->getAvailableYears($this->user->id);

        $this->assertContains((string) now()->year, $years);
    }

    public function test_get_available_years_returns_years_from_logs(): void
    {
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->create();
        FishingLog::factory()->forUser($this->user)->onDate('2023-03-10')->create();

        $years = $this->service->getAvailableYears($this->user->id);

        $this->assertContains('2024', $years);
        $this->assertContains('2023', $years);
        $this->assertContains((string) now()->year, $years);
    }

    public function test_build_base_query_filters_by_year(): void
    {
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->create();
        FishingLog::factory()->forUser($this->user)->onDate('2023-03-10')->create();

        $query = $this->service->buildBaseQuery($this->user->id, '2024');
        $count = $query->count();

        $this->assertEquals(1, $count);
    }

    public function test_build_base_query_returns_all_for_lifetime(): void
    {
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->create();
        FishingLog::factory()->forUser($this->user)->onDate('2023-03-10')->create();

        $query = $this->service->buildBaseQuery($this->user->id, 'lifetime');
        $count = $query->count();

        $this->assertEquals(2, $count);
    }

    public function test_get_year_stats_calculates_correctly(): void
    {
        // Create 3 days of fishing: 2 successful, 1 skunked
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->successful()->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-16')->successful()->create(['quantity' => 3]);
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-17')->skunked()->create();

        $baseQuery = $this->service->buildBaseQuery($this->user->id, '2024');
        $stats = $this->service->getYearStats($baseQuery);

        $this->assertEquals(3, $stats['daysFished']);
        $this->assertEquals(2, $stats['daysWithFish']);
        $this->assertEquals(1, $stats['daysSkunked']);
        $this->assertEquals(5, $stats['mostInDay']);
        $this->assertEqualsWithDelta(66.7, $stats['successRate'], 0.1);
    }

    public function test_get_year_stats_handles_empty_data(): void
    {
        $baseQuery = $this->service->buildBaseQuery($this->user->id, '2024');
        $stats = $this->service->getYearStats($baseQuery);

        $this->assertEquals(0, $stats['daysFished']);
        $this->assertEquals(0, $stats['daysWithFish']);
        $this->assertEquals(0, $stats['daysSkunked']);
        $this->assertEquals(0, $stats['mostInDay']);
        $this->assertEquals(0, $stats['successRate']);
    }

    public function test_get_favorite_weekday_returns_most_common_day(): void
    {
        // Create 3 logs on Monday, 1 on Tuesday
        FishingLog::factory()->forUser($this->user)->onDate('2024-01-08')->create(); // Monday
        FishingLog::factory()->forUser($this->user)->onDate('2024-01-15')->create(); // Monday
        FishingLog::factory()->forUser($this->user)->onDate('2024-01-22')->create(); // Monday
        FishingLog::factory()->forUser($this->user)->onDate('2024-01-09')->create(); // Tuesday

        $baseQuery = $this->service->buildBaseQuery($this->user->id, '2024');
        $favoriteWeekday = $this->service->getFavoriteWeekday($baseQuery);

        $this->assertNotNull($favoriteWeekday);
        $this->assertEquals('Monday', $favoriteWeekday['day']);
        $this->assertEquals(3, $favoriteWeekday['count']);
    }

    public function test_get_favorite_weekday_returns_null_when_no_data(): void
    {
        $baseQuery = $this->service->buildBaseQuery($this->user->id, '2024');
        $favoriteWeekday = $this->service->getFavoriteWeekday($baseQuery);

        $this->assertNull($favoriteWeekday);
    }

    public function test_get_fly_stats_returns_most_successful_fly(): void
    {
        $fly1 = UserFly::factory()->forUser($this->user)->create(['name' => 'Adams']);
        $fly2 = UserFly::factory()->forUser($this->user)->create(['name' => 'Elk Hair Caddis']);

        // Adams catches more fish
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->create([
            'user_fly_id' => $fly1->id,
            'quantity' => 10,
            'max_size' => 15.5,
        ]);
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-16')->create([
            'user_fly_id' => $fly2->id,
            'quantity' => 3,
            'max_size' => 20.0, // Bigger fish
        ]);

        $flyStats = $this->service->getFlyStats($this->user->id, '2024');

        $this->assertNotNull($flyStats['mostSuccessfulFly']);
        $this->assertEquals('Adams', $flyStats['mostSuccessfulFly']->name);
        $this->assertEquals(10, $flyStats['mostSuccessfulFly']->total_caught);

        $this->assertNotNull($flyStats['biggestFishFly']);
        $this->assertEquals('Elk Hair Caddis', $flyStats['biggestFishFly']->name);
        $this->assertEquals(20.0, $flyStats['biggestFishFly']->biggest_size);
    }

    public function test_get_fly_stats_returns_empty_when_no_data(): void
    {
        $flyStats = $this->service->getFlyStats($this->user->id, '2024');

        $this->assertNull($flyStats['mostSuccessfulFly']);
        $this->assertNull($flyStats['biggestFishFly']);
        $this->assertNull($flyStats['mostSuccessfulFlyType']);
        $this->assertNull($flyStats['mostSuccessfulFlyColor']);
    }

    public function test_get_catches_over_time_returns_grouped_data(): void
    {
        FishingLog::factory()->forUser($this->user)->onDate('2024-01-15')->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate('2024-01-16')->create(['quantity' => 3]);
        FishingLog::factory()->forUser($this->user)->onDate('2024-02-10')->create(['quantity' => 7]);

        $baseQuery = $this->service->buildBaseQuery($this->user->id, '2024');
        $catchesOverTime = $this->service->getCatchesOverTime($baseQuery, '2024');

        $this->assertGreaterThan(0, $catchesOverTime->count());
    }

    public function test_get_catches_over_time_handles_empty_data(): void
    {
        $baseQuery = $this->service->buildBaseQuery($this->user->id, '2024');
        $catchesOverTime = $this->service->getCatchesOverTime($baseQuery, '2024');

        $this->assertEquals(0, $catchesOverTime->count());
    }

    public function test_get_streak_stats_calculates_longest_streak(): void
    {
        // Create a 3-day streak
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->successful()->create();
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-16')->successful()->create();
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-17')->successful()->create();
        // Gap
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-20')->successful()->create();

        $streakStats = $this->service->getStreakStats($this->user->id, '2024');

        $this->assertEquals(3, $streakStats['longestStreak']);
    }

    public function test_get_streak_stats_handles_empty_data(): void
    {
        $streakStats = $this->service->getStreakStats($this->user->id, '2024');

        $this->assertEquals(0, $streakStats['currentStreak']);
        $this->assertEquals(0, $streakStats['longestStreak']);
    }

    public function test_get_streak_stats_calculates_current_streak(): void
    {
        // Create a streak ending today
        FishingLog::factory()->forUser($this->user)->onDate(now()->subDays(2)->format('Y-m-d'))->successful()->create();
        FishingLog::factory()->forUser($this->user)->onDate(now()->subDays(1)->format('Y-m-d'))->successful()->create();
        FishingLog::factory()->forUser($this->user)->onDate(now()->format('Y-m-d'))->successful()->create();

        $streakStats = $this->service->getStreakStats($this->user->id, (string) now()->year);

        $this->assertEquals(3, $streakStats['currentStreak']);
        $this->assertEquals(3, $streakStats['longestStreak']);
    }

    public function test_get_fly_stats_aggregates_by_type(): void
    {
        $dryFly1 = UserFly::factory()->forUser($this->user)->dry()->create(['name' => 'Adams']);
        $dryFly2 = UserFly::factory()->forUser($this->user)->dry()->create(['name' => 'Royal Wulff']);
        $nymph = UserFly::factory()->forUser($this->user)->nymph()->create(['name' => 'Pheasant Tail']);

        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->create([
            'user_fly_id' => $dryFly1->id,
            'quantity' => 5,
        ]);
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-16')->create([
            'user_fly_id' => $dryFly2->id,
            'quantity' => 3,
        ]);
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-17')->create([
            'user_fly_id' => $nymph->id,
            'quantity' => 4,
        ]);

        $flyStats = $this->service->getFlyStats($this->user->id, '2024');

        $this->assertNotNull($flyStats['mostSuccessfulFlyType']);
        $this->assertEquals('dry', $flyStats['mostSuccessfulFlyType']->type);
        $this->assertEquals(8, $flyStats['mostSuccessfulFlyType']->total_caught); // 5 + 3
    }
}

