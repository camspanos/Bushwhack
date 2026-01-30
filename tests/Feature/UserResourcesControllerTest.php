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

class UserResourcesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ==================== UserFish Tests ====================

    public function test_guests_cannot_access_fish(): void
    {
        $response = $this->getJson('/fish');
        $response->assertUnauthorized();
    }

    public function test_user_can_list_their_fish(): void
    {
        UserFish::factory()->forUser($this->user)->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/fish');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_fish(): void
    {
        $response = $this->actingAs($this->user)->postJson('/fish', [
            'species' => 'Rainbow Trout',
            'water_type' => 'freshwater',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_fish', [
            'user_id' => $this->user->id,
            'species' => 'Rainbow Trout',
        ]);
    }

    public function test_create_fish_requires_species(): void
    {
        $response = $this->actingAs($this->user)->postJson('/fish', [
            'water_type' => 'freshwater',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['species']);
    }

    public function test_cannot_create_duplicate_fish(): void
    {
        UserFish::factory()->forUser($this->user)->create([
            'species' => 'Rainbow Trout',
            'water_type' => 'freshwater',
        ]);

        $response = $this->actingAs($this->user)->postJson('/fish', [
            'species' => 'Rainbow Trout',
            'water_type' => 'freshwater',
        ]);

        $response->assertStatus(409);
    }

    // ==================== UserFly Tests ====================

    public function test_guests_cannot_access_flies(): void
    {
        $response = $this->getJson('/flies');
        $response->assertUnauthorized();
    }

    public function test_user_can_list_their_flies(): void
    {
        UserFly::factory()->forUser($this->user)->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/flies');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_fly(): void
    {
        $response = $this->actingAs($this->user)->postJson('/flies', [
            'name' => 'Adams',
            'color' => 'gray',
            'size' => '14',
            'type' => 'dry',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_flies', [
            'user_id' => $this->user->id,
            'name' => 'Adams',
        ]);
    }

    public function test_create_fly_requires_name(): void
    {
        $response = $this->actingAs($this->user)->postJson('/flies', [
            'color' => 'gray',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    // ==================== UserRod Tests ====================

    public function test_guests_cannot_access_rods(): void
    {
        $response = $this->getJson('/rods');
        $response->assertUnauthorized();
    }

    public function test_user_can_list_their_rods(): void
    {
        UserRod::factory()->forUser($this->user)->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/rods');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_rod(): void
    {
        $response = $this->actingAs($this->user)->postJson('/rods', [
            'rod_name' => 'Orvis Helios',
            'rod_weight' => '5wt',
            'rod_length' => '9ft',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_rods', [
            'user_id' => $this->user->id,
            'rod_name' => 'Orvis Helios',
        ]);
    }

    public function test_create_rod_requires_name(): void
    {
        $response = $this->actingAs($this->user)->postJson('/rods', [
            'rod_weight' => '5wt',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['rod_name']);
    }

    // ==================== UserLocation Tests ====================

    public function test_guests_cannot_access_locations(): void
    {
        $response = $this->getJson('/locations');
        $response->assertUnauthorized();
    }

    public function test_user_can_list_their_locations(): void
    {
        $country = Country::factory()->create();
        UserLocation::factory()->forUser($this->user)->count(3)->create(['country_id' => $country->id]);

        $response = $this->actingAs($this->user)->getJson('/locations');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_location(): void
    {
        $country = Country::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/locations', [
            'name' => 'South Platte River',
            'city' => 'Deckers',
            'state' => 'Colorado',
            'country_id' => $country->id,
            'water_type' => 'river',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_locations', [
            'user_id' => $this->user->id,
            'name' => 'South Platte River',
        ]);
    }

    public function test_create_location_requires_name(): void
    {
        $response = $this->actingAs($this->user)->postJson('/locations', [
            'city' => 'Deckers',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_delete_location(): void
    {
        $country = Country::factory()->create();
        $location = UserLocation::factory()->forUser($this->user)->create(['country_id' => $country->id]);

        $response = $this->actingAs($this->user)->deleteJson("/locations/{$location->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('user_locations', ['id' => $location->id]);
    }

    public function test_user_cannot_delete_other_users_location(): void
    {
        $otherUser = User::factory()->create();
        $country = Country::factory()->create();
        $location = UserLocation::factory()->forUser($otherUser)->create(['country_id' => $country->id]);

        $response = $this->actingAs($this->user)->deleteJson("/locations/{$location->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('user_locations', ['id' => $location->id]);
    }

    // ==================== UserFriend Tests ====================

    public function test_guests_cannot_access_friends(): void
    {
        $response = $this->getJson('/friends');
        $response->assertUnauthorized();
    }

    public function test_user_can_list_their_friends(): void
    {
        UserFriend::factory()->forUser($this->user)->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/friends');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_friend(): void
    {
        $response = $this->actingAs($this->user)->postJson('/friends', [
            'name' => 'John Doe',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_friends', [
            'user_id' => $this->user->id,
            'name' => 'John Doe',
        ]);
    }

    // ==================== Statistics Tests ====================

    public function test_fish_statistics_returns_data(): void
    {
        $fish = UserFish::factory()->forUser($this->user)->create(['species' => 'Rainbow Trout']);
        FishingLog::factory()->forUser($this->user)->thisYear()->create([
            'user_fish_id' => $fish->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->user)->getJson('/fish/stats/all');

        $response->assertOk();
        $response->assertJsonFragment(['species' => 'Rainbow Trout']);
    }

    public function test_fly_statistics_returns_data(): void
    {
        $fly = UserFly::factory()->forUser($this->user)->create(['name' => 'Adams']);
        FishingLog::factory()->forUser($this->user)->thisYear()->create([
            'user_fly_id' => $fly->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->user)->getJson('/flies/stats/all');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Adams']);
    }

    public function test_rod_statistics_returns_data(): void
    {
        $rod = UserRod::factory()->forUser($this->user)->create(['rod_name' => 'Orvis Helios']);
        FishingLog::factory()->forUser($this->user)->thisYear()->create([
            'user_rod_id' => $rod->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->user)->getJson('/rods/stats/all');

        $response->assertOk();
        $response->assertJsonFragment(['rod_name' => 'Orvis Helios']);
    }

    public function test_location_statistics_returns_data(): void
    {
        $country = Country::factory()->create();
        $location = UserLocation::factory()->forUser($this->user)->create([
            'name' => 'South Platte',
            'country_id' => $country->id,
        ]);
        FishingLog::factory()->forUser($this->user)->thisYear()->create([
            'user_location_id' => $location->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->user)->getJson('/locations/stats/all');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'South Platte']);
    }

    public function test_countries_endpoint_returns_data(): void
    {
        Country::factory()->create(['name' => 'United States', 'code' => 'US']);

        $response = $this->actingAs($this->user)->getJson('/countries');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'United States']);
    }

    // ==================== Following Tests (Premium/Freemium) ====================

    public function test_free_user_can_follow_user_id_1(): void
    {
        // Create user with id 1 (the special user that anyone can follow)
        $userOne = User::factory()->create(['id' => 1, 'allow_followers' => true]);

        // Our test user is free by default
        $this->assertFalse($this->user->is_premium);

        // Free user can follow user_id 1
        $this->assertTrue($this->user->canFollow($userOne));

        $response = $this->actingAs($this->user)->postJson("/users/{$userOne->id}/follow");

        $response->assertOk();
        $this->assertTrue($this->user->fresh()->isFollowing($userOne));
    }

    public function test_free_user_cannot_follow_other_users(): void
    {
        // Create another user (not id 1)
        $otherUser = User::factory()->create(['allow_followers' => true]);

        // Our test user is free by default
        $this->assertFalse($this->user->is_premium);

        // Free user cannot follow other users
        $this->assertFalse($this->user->canFollow($otherUser));

        $response = $this->actingAs($this->user)->postJson("/users/{$otherUser->id}/follow");

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'Free users can only follow the default user. Upgrade to premium to follow other users.',
        ]);
        $this->assertFalse($this->user->fresh()->isFollowing($otherUser));
    }

    public function test_premium_user_can_follow_any_user(): void
    {
        // Make our user premium
        $this->user->update(['is_premium' => true]);

        // Create another user
        $otherUser = User::factory()->create(['allow_followers' => true]);

        // Premium user can follow anyone
        $this->assertTrue($this->user->canFollow($otherUser));

        $response = $this->actingAs($this->user)->postJson("/users/{$otherUser->id}/follow");

        $response->assertOk();
        $this->assertTrue($this->user->fresh()->isFollowing($otherUser));
    }

    public function test_user_cannot_follow_user_who_disallows_followers(): void
    {
        // Make our user premium
        $this->user->update(['is_premium' => true]);

        // Create user who doesn't allow followers
        $privateUser = User::factory()->create(['allow_followers' => false]);

        $response = $this->actingAs($this->user)->postJson("/users/{$privateUser->id}/follow");

        // The follow method silently fails when allow_followers is false
        $response->assertOk();
        $this->assertFalse($this->user->fresh()->isFollowing($privateUser));
    }

    public function test_user_can_unfollow(): void
    {
        // Create user with id 1
        $userOne = User::factory()->create(['id' => 1, 'allow_followers' => true]);

        // Follow the user first
        $this->user->follow($userOne);
        $this->assertTrue($this->user->isFollowing($userOne));

        // Unfollow - route is /users/{user}/unfollow
        $response = $this->actingAs($this->user)->deleteJson("/users/{$userOne->id}/unfollow");

        $response->assertOk();
        $this->assertFalse($this->user->fresh()->isFollowing($userOne));
    }

    public function test_user_cannot_follow_themselves(): void
    {
        // Make user premium and allow followers
        $this->user->update(['is_premium' => true, 'allow_followers' => true]);

        // Try to follow self - the follow method should silently fail
        $this->user->follow($this->user);

        $this->assertFalse($this->user->isFollowing($this->user));
    }

    public function test_can_follow_method_returns_correct_values(): void
    {
        $userOne = User::factory()->create(['id' => 1, 'allow_followers' => true]);
        $otherUser = User::factory()->create(['allow_followers' => true]);

        // Free user
        $this->assertFalse($this->user->is_premium);
        $this->assertTrue($this->user->canFollow($userOne)); // Can follow user_id 1
        $this->assertFalse($this->user->canFollow($otherUser)); // Cannot follow others

        // Premium user
        $this->user->update(['is_premium' => true]);
        $this->assertTrue($this->user->fresh()->canFollow($userOne)); // Can follow user_id 1
        $this->assertTrue($this->user->fresh()->canFollow($otherUser)); // Can follow anyone
    }

    public function test_is_premium_method(): void
    {
        // Free user
        $this->assertFalse($this->user->isPremium());

        // Premium user
        $this->user->update(['is_premium' => true]);
        $this->assertTrue($this->user->fresh()->isPremium());
    }

    public function test_following_list_shows_followed_users(): void
    {
        $userOne = User::factory()->create(['id' => 1, 'allow_followers' => true, 'name' => 'User One']);

        // Follow the user
        $this->user->follow($userOne);

        $response = $this->actingAs($this->user)->get('/following');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Following')
            ->has('following', 1)
            ->where('following.0.name', 'User One')
        );
    }

    public function test_search_users_returns_can_follow_status(): void
    {
        // Create users to search
        $userOne = User::factory()->create(['id' => 1, 'allow_followers' => true, 'name' => 'Searchable User']);
        $otherUser = User::factory()->create(['allow_followers' => true, 'name' => 'Searchable Other']);

        // Free user search - route is /users/search
        $response = $this->actingAs($this->user)->getJson('/users/search?query=Searchable');

        $response->assertOk();
        $users = collect($response->json());

        // Find user_id 1 in results
        $userOneResult = $users->firstWhere('id', $userOne->id);
        $otherUserResult = $users->firstWhere('id', $otherUser->id);

        $this->assertTrue($userOneResult['can_follow']); // Free user can follow user_id 1
        $this->assertFalse($otherUserResult['can_follow']); // Free user cannot follow others
    }

    public function test_premium_user_search_shows_all_users_as_followable(): void
    {
        // Make user premium
        $this->user->update(['is_premium' => true]);

        // Create users to search
        $otherUser1 = User::factory()->create(['allow_followers' => true, 'name' => 'Searchable One']);
        $otherUser2 = User::factory()->create(['allow_followers' => true, 'name' => 'Searchable Two']);

        $response = $this->actingAs($this->user)->getJson('/users/search?query=Searchable');

        $response->assertOk();
        $users = collect($response->json());

        // All users should be followable for premium user
        foreach ($users as $user) {
            $this->assertTrue($user['can_follow']);
        }
    }
}
