<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\FishingLog;
use App\Models\User;
use App\Models\UserFish;
use App\Models\UserFly;
use App\Models\UserFriend;
use App\Models\UserLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_dashboard_shows_total_catches(): void
    {
        FishingLog::factory()->forUser($this->user)->thisYear()->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->thisYear()->create(['quantity' => 3]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats.totalCatches')
            ->where('stats.totalCatches', '8') // Returns as string from DB sum
        );
    }

    public function test_dashboard_shows_total_trips(): void
    {
        FishingLog::factory()->forUser($this->user)->onDate(now()->format('Y-m-d'))->create();
        FishingLog::factory()->forUser($this->user)->onDate(now()->subDays(1)->format('Y-m-d'))->create();
        FishingLog::factory()->forUser($this->user)->onDate(now()->subDays(2)->format('Y-m-d'))->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats.totalTrips')
            ->where('stats.totalTrips', 3)
        );
    }

    public function test_dashboard_shows_total_locations(): void
    {
        $country = Country::factory()->create();
        UserLocation::factory()->forUser($this->user)->count(3)->create(['country_id' => $country->id]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats.totalLocations')
            ->where('stats.totalLocations', 3)
        );
    }

    public function test_dashboard_shows_top_fish(): void
    {
        $fish = UserFish::factory()->forUser($this->user)->create(['species' => 'Rainbow Trout']);
        FishingLog::factory()->forUser($this->user)->thisYear()->create([
            'user_fish_id' => $fish->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats.topFish')
        );
    }

    public function test_dashboard_shows_biggest_catch(): void
    {
        $fish = UserFish::factory()->forUser($this->user)->create(['species' => 'Brown Trout']);
        FishingLog::factory()->forUser($this->user)->thisYear()->create([
            'user_fish_id' => $fish->id,
            'max_size' => 24.5,
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats.biggestCatch')
        );
    }

    public function test_dashboard_filters_by_year(): void
    {
        // Make user premium so they can filter by year
        $this->user->update(['is_premium' => true]);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs in different years
        FishingLog::factory()->forUser($this->user)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Request current year data
        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => (string) $currentYear]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.totalCatches', '5') // Returns as string from DB sum
        );
    }

    public function test_dashboard_shows_lifetime_data(): void
    {
        // Make user premium so they can filter by year
        $this->user->update(['is_premium' => true]);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        FishingLog::factory()->forUser($this->user)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => 'lifetime']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.totalCatches', '15') // Returns as string from DB sum
        );
    }

    public function test_dashboard_shows_available_years(): void
    {
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->create();
        FishingLog::factory()->forUser($this->user)->onDate('2023-06-15')->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('availableYears')
        );
    }

    public function test_dashboard_data_is_cached(): void
    {
        Cache::flush();

        FishingLog::factory()->forUser($this->user)->thisYear()->create(['quantity' => 5]);

        // First request - should cache
        $this->actingAs($this->user)->get(route('dashboard'));

        $currentYear = now()->year;
        $cacheKey = "dashboard_{$this->user->id}_{$currentYear}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_dashboard_shows_fly_statistics(): void
    {
        $fly = UserFly::factory()->forUser($this->user)->dry()->create(['name' => 'Adams']);
        FishingLog::factory()->forUser($this->user)->thisYear()->create([
            'user_fly_id' => $fly->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('mostSuccessfulFly')
        );
    }

    public function test_dashboard_shows_streak_stats(): void
    {
        // Create a 3-day streak
        FishingLog::factory()->forUser($this->user)->onDate(now()->subDays(2)->format('Y-m-d'))->successful()->create();
        FishingLog::factory()->forUser($this->user)->onDate(now()->subDays(1)->format('Y-m-d'))->successful()->create();
        FishingLog::factory()->forUser($this->user)->onDate(now()->format('Y-m-d'))->successful()->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('streakStats.currentStreak')
            ->has('streakStats.longestStreak')
        );
    }

    public function test_dashboard_shows_year_stats(): void
    {
        FishingLog::factory()->forUser($this->user)->thisYear()->successful()->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->thisYear()->skunked()->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('yearStats.daysFished')
            ->has('yearStats.daysWithFish')
            ->has('yearStats.daysSkunked')
        );
    }

    public function test_dashboard_shows_friends_count(): void
    {
        $friend1 = UserFriend::factory()->forUser($this->user)->create();
        $friend2 = UserFriend::factory()->forUser($this->user)->create();

        $log = FishingLog::factory()->forUser($this->user)->thisYear()->create();
        $log->friends()->attach([$friend1->id, $friend2->id]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats.totalFriends')
            ->where('stats.totalFriends', 2)
        );
    }

    // ==================== Premium/Freemium Tests ====================

    public function test_free_user_can_only_see_current_year_data(): void
    {
        // User is free by default (is_premium = false)
        $this->assertFalse($this->user->is_premium);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs in current year and last year
        FishingLog::factory()->forUser($this->user)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Free user should only see current year data
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.totalCatches', '5') // Only current year catches
        );
    }

    public function test_free_user_year_filter_request_is_ignored(): void
    {
        // User is free by default
        $this->assertFalse($this->user->is_premium);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs in different years
        FishingLog::factory()->forUser($this->user)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Free user tries to filter by last year - should be ignored
        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => (string) $lastYear]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            // Should still show current year data, not last year
            ->where('stats.totalCatches', '5')
        );
    }

    public function test_free_user_lifetime_filter_is_ignored(): void
    {
        // User is free by default
        $this->assertFalse($this->user->is_premium);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs in different years
        FishingLog::factory()->forUser($this->user)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Free user tries to filter by lifetime - should be ignored
        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => 'lifetime']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            // Should still show current year data only
            ->where('stats.totalCatches', '5')
        );
    }

    public function test_premium_user_can_filter_by_any_year(): void
    {
        // Make user premium
        $this->user->update(['is_premium' => true]);
        $this->assertTrue($this->user->fresh()->is_premium);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs in different years
        FishingLog::factory()->forUser($this->user)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Premium user can filter by last year
        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => (string) $lastYear]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.totalCatches', '10') // Last year catches
        );
    }

    public function test_premium_user_can_view_lifetime_data(): void
    {
        // Make user premium
        $this->user->update(['is_premium' => true]);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs in different years
        FishingLog::factory()->forUser($this->user)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Premium user can view lifetime data
        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => 'lifetime']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.totalCatches', '15') // All catches combined
        );
    }

    public function test_user_can_filter_by_year_method(): void
    {
        // Free user cannot filter by year
        $this->assertFalse($this->user->canFilterByYear());

        // Premium user can filter by year
        $this->user->update(['is_premium' => true]);
        $this->assertTrue($this->user->fresh()->canFilterByYear());
    }

    public function test_free_user_sees_all_available_years_in_dropdown(): void
    {
        // User is free by default
        $this->assertFalse($this->user->is_premium);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs in different years
        FishingLog::factory()->forUser($this->user)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($this->user)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        // Free users can see all years in dropdown (but can't filter by them)
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('availableYears')
            ->where('availableYears', [(string) $currentYear, (string) $lastYear])
        );
    }

    // ==================== Public Dashboard Tests (Premium/Freemium) ====================

    public function test_user_must_be_following_to_view_public_dashboard(): void
    {
        $otherUser = User::factory()->create(['allow_followers' => true]);

        // Create some logs for the other user
        FishingLog::factory()->forUser($otherUser)->thisYear()->create(['quantity' => 5]);

        // Try to view without following - should be forbidden
        $response = $this->actingAs($this->user)->get("/users/{$otherUser->id}/dashboard");

        $response->assertForbidden();
    }

    public function test_user_can_view_public_dashboard_when_following(): void
    {
        // Create user with id 1 (anyone can follow)
        $userOne = User::factory()->create(['id' => 1, 'allow_followers' => true]);

        // Create some logs for user one
        FishingLog::factory()->forUser($userOne)->thisYear()->create(['quantity' => 5]);

        // Follow the user
        $this->user->follow($userOne);

        $response = $this->actingAs($this->user)->get("/users/{$userOne->id}/dashboard");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicDashboard')
            ->has('stats.totalCatches')
        );
    }

    public function test_free_user_viewing_public_dashboard_only_sees_current_year(): void
    {
        // Create another user
        $otherUser = User::factory()->create(['allow_followers' => true]);

        // Make our user premium so they can follow
        $this->user->update(['is_premium' => true]);
        $this->user->follow($otherUser);

        // Now make user free again
        $this->user->update(['is_premium' => false]);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs for the other user in different years
        FishingLog::factory()->forUser($otherUser)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($otherUser)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Free user viewing public dashboard should only see current year
        $response = $this->actingAs($this->user)->get("/users/{$otherUser->id}/dashboard");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicDashboard')
            ->where('stats.totalCatches', '5') // Only current year
        );
    }

    public function test_premium_user_can_filter_public_dashboard_by_year(): void
    {
        // Make our user premium
        $this->user->update(['is_premium' => true]);

        // Create another user
        $otherUser = User::factory()->create(['allow_followers' => true]);
        $this->user->follow($otherUser);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs for the other user in different years
        FishingLog::factory()->forUser($otherUser)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($otherUser)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Premium user can filter by last year
        $response = $this->actingAs($this->user)->get("/users/{$otherUser->id}/dashboard?year={$lastYear}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicDashboard')
            ->where('stats.totalCatches', '10') // Last year data
        );
    }

    public function test_free_user_can_view_all_years_for_user_id_1(): void
    {
        // Create user with id 1 (special exception - anyone can view all years)
        $userOne = User::factory()->create(['id' => 1, 'allow_followers' => true]);

        // Free user follows user_id 1
        $this->user->follow($userOne);
        $this->assertFalse($this->user->is_premium);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs for user one in different years
        FishingLog::factory()->forUser($userOne)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($userOne)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Free user can view lifetime data for user_id 1
        $response = $this->actingAs($this->user)->get("/users/{$userOne->id}/dashboard?year=lifetime");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicDashboard')
            ->where('stats.totalCatches', '15') // All years combined
        );
    }

    public function test_free_user_can_filter_by_year_for_user_id_1(): void
    {
        // Create user with id 1 (special exception)
        $userOne = User::factory()->create(['id' => 1, 'allow_followers' => true]);

        // Free user follows user_id 1
        $this->user->follow($userOne);
        $this->assertFalse($this->user->is_premium);

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create logs for user one in different years
        FishingLog::factory()->forUser($userOne)->onDate("{$currentYear}-06-15")->create(['quantity' => 5]);
        FishingLog::factory()->forUser($userOne)->onDate("{$lastYear}-06-15")->create(['quantity' => 10]);

        // Free user can filter by last year for user_id 1
        $response = $this->actingAs($this->user)->get("/users/{$userOne->id}/dashboard?year={$lastYear}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicDashboard')
            ->where('stats.totalCatches', '10') // Last year only
        );
    }
}
