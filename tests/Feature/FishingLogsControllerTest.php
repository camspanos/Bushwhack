<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\FishingLog;
use App\Models\User;
use App\Models\UserFish;
use App\Models\UserFly;
use App\Models\UserFriend;
use App\Models\UserLocation;
use App\Models\UserRod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FishingLogsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_access_fishing_logs(): void
    {
        $response = $this->getJson('/fishing-logs');

        $response->assertUnauthorized();
    }

    public function test_user_can_list_their_fishing_logs(): void
    {
        FishingLog::factory()->forUser($this->user)->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/fishing-logs');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_cannot_see_other_users_fishing_logs(): void
    {
        $otherUser = User::factory()->create();
        FishingLog::factory()->forUser($otherUser)->count(3)->create();
        FishingLog::factory()->forUser($this->user)->count(1)->create();

        $response = $this->actingAs($this->user)->getJson('/fishing-logs');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_fishing_log(): void
    {
        $response = $this->actingAs($this->user)->postJson('/fishing-logs', [
            'date' => '2024-06-15',
            'quantity' => 5,
            'max_size' => 18.5,
            'style' => 'dry fly',
            'moon_phase' => 'Full Moon',
            'notes' => 'Great day on the water!',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('fishing_logs', [
            'user_id' => $this->user->id,
            'date' => '2024-06-15',
            'quantity' => 5,
        ]);
    }

    public function test_user_can_create_fishing_log_with_relations(): void
    {
        $country = Country::factory()->create();
        $location = UserLocation::factory()->forUser($this->user)->create(['country_id' => $country->id]);
        $fish = UserFish::factory()->forUser($this->user)->create();
        $fly = UserFly::factory()->forUser($this->user)->create();
        $rod = UserRod::factory()->forUser($this->user)->create();
        $friend = UserFriend::factory()->forUser($this->user)->create();

        $response = $this->actingAs($this->user)->postJson('/fishing-logs', [
            'date' => '2024-06-15',
            'user_location_id' => $location->id,
            'user_fish_id' => $fish->id,
            'user_fly_id' => $fly->id,
            'user_rod_id' => $rod->id,
            'quantity' => 5,
            'friend_ids' => [$friend->id],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('fishing_logs', [
            'user_id' => $this->user->id,
            'user_location_id' => $location->id,
            'user_fish_id' => $fish->id,
        ]);
        $this->assertDatabaseHas('fishing_log_user_friend', [
            'fishing_log_id' => $response->json('fishing_log.id'),
            'user_friend_id' => $friend->id,
        ]);
    }

    public function test_create_fishing_log_requires_date(): void
    {
        $response = $this->actingAs($this->user)->postJson('/fishing-logs', [
            'quantity' => 5,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['date']);
    }

    public function test_user_can_update_fishing_log(): void
    {
        $fishingLog = FishingLog::factory()->forUser($this->user)->create([
            'quantity' => 5,
            'notes' => 'Original notes',
        ]);

        $response = $this->actingAs($this->user)->putJson("/fishing-logs/{$fishingLog->id}", [
            'date' => $fishingLog->date->format('Y-m-d'),
            'quantity' => 10,
            'notes' => 'Updated notes',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('fishing_logs', [
            'id' => $fishingLog->id,
            'quantity' => 10,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_user_cannot_update_other_users_fishing_log(): void
    {
        $otherUser = User::factory()->create();
        $fishingLog = FishingLog::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($this->user)->putJson("/fishing-logs/{$fishingLog->id}", [
            'date' => $fishingLog->date->format('Y-m-d'),
            'quantity' => 10,
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_fishing_log(): void
    {
        $fishingLog = FishingLog::factory()->forUser($this->user)->create();

        $response = $this->actingAs($this->user)->deleteJson("/fishing-logs/{$fishingLog->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('fishing_logs', ['id' => $fishingLog->id]);
    }

    public function test_user_cannot_delete_other_users_fishing_log(): void
    {
        $otherUser = User::factory()->create();
        $fishingLog = FishingLog::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($this->user)->deleteJson("/fishing-logs/{$fishingLog->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('fishing_logs', ['id' => $fishingLog->id]);
    }

    public function test_fishing_logs_are_paginated(): void
    {
        FishingLog::factory()->forUser($this->user)->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/fishing-logs?per_page=10');

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('last_page', 2);
    }

    public function test_fishing_logs_are_ordered_by_date_descending(): void
    {
        FishingLog::factory()->forUser($this->user)->onDate('2024-01-01')->create();
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->create();
        FishingLog::factory()->forUser($this->user)->onDate('2024-03-10')->create();

        $response = $this->actingAs($this->user)->getJson('/fishing-logs');

        $response->assertOk();
        // Extract just the date portion (dates come back as ISO 8601 format)
        $dates = collect($response->json('data'))->pluck('date')->map(function ($date) {
            return substr($date, 0, 10); // Get YYYY-MM-DD portion
        })->toArray();
        $this->assertEquals(['2024-06-15', '2024-03-10', '2024-01-01'], $dates);
    }

    public function test_create_fishing_log_detects_new_species(): void
    {
        $fish = UserFish::factory()->forUser($this->user)->create(['species' => 'Rainbow Trout']);

        $response = $this->actingAs($this->user)->postJson('/fishing-logs', [
            'date' => '2024-06-15',
            'user_fish_id' => $fish->id,
            'quantity' => 1,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('is_new_species', true);
    }

    public function test_create_fishing_log_detects_personal_best(): void
    {
        $fish = UserFish::factory()->forUser($this->user)->create(['species' => 'Rainbow Trout']);

        // Create first catch
        FishingLog::factory()->forUser($this->user)->create([
            'user_fish_id' => $fish->id,
            'max_size' => 15.0,
        ]);

        // Create second catch with bigger fish
        $response = $this->actingAs($this->user)->postJson('/fishing-logs', [
            'date' => '2024-06-15',
            'user_fish_id' => $fish->id,
            'quantity' => 1,
            'max_size' => 20.0,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('is_personal_best', true);
        $response->assertJsonPath('previous_best_size', '15.00');
    }

    public function test_create_fishing_log_with_weather_conditions(): void
    {
        $response = $this->actingAs($this->user)->postJson('/fishing-logs', [
            'date' => '2024-06-15',
            'quantity' => 5,
            'weather' => [
                'temperature' => 'mild',
                'cloud' => 'Partly Cloudy',
                'wind' => 'Light',
                'precipitation' => 'None',
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_weather', [
            'user_id' => $this->user->id,
            'temperature' => 'mild',
            'cloud' => 'Partly Cloudy',
        ]);
    }

    public function test_create_fishing_log_with_water_conditions(): void
    {
        $response = $this->actingAs($this->user)->postJson('/fishing-logs', [
            'date' => '2024-06-15',
            'quantity' => 5,
            'water_condition' => [
                'temperature' => 'cool',
                'clarity' => 'Clear',
                'level' => 'Normal',
                'speed' => 'Moderate',
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_water_conditions', [
            'user_id' => $this->user->id,
            'temperature' => 'cool',
            'clarity' => 'Clear',
        ]);
    }

    public function test_available_years_returns_years_from_logs(): void
    {
        FishingLog::factory()->forUser($this->user)->onDate('2024-06-15')->create();
        FishingLog::factory()->forUser($this->user)->onDate('2023-03-10')->create();

        $response = $this->actingAs($this->user)->getJson('/fishing-logs/available-years');

        $response->assertOk();
        $years = $response->json();
        $this->assertContains('2024', $years);
        $this->assertContains('2023', $years);
    }

    public function test_calculate_time_of_day(): void
    {
        $response = $this->actingAs($this->user)->postJson('/fishing-logs/calculate-time-of-day', [
            'time' => '12:00',
            'date' => '2024-06-15',
        ]);

        $response->assertOk();
        $response->assertJsonPath('time_of_day', 'Midday');
    }

    public function test_calculate_time_of_day_with_location(): void
    {
        $country = Country::factory()->create();
        $location = UserLocation::factory()->forUser($this->user)->colorado()->create(['country_id' => $country->id]);

        $response = $this->actingAs($this->user)->postJson('/fishing-logs/calculate-time-of-day', [
            'time' => '12:00',
            'date' => '2024-06-15',
            'location_id' => $location->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('time_of_day', 'Midday');
    }
}

