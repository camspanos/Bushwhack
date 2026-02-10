<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFishingLogRequest;
use App\Jobs\CheckUserBadgesJob;
use App\Models\FishingLog;
use App\Models\UserLocation;
use App\Models\UserWaterCondition;
use App\Models\UserWeather;
use App\Services\MoonPositionCalculator;
use App\Services\TimeOfDayCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FishingLogsController extends Controller
{
    /**
     * Display a listing of the authenticated user's fishing logs.
     *
     * Retrieves all fishing log records belonging to the authenticated user,
     * ordered by date (most recent first), with related data loaded.
     * Returns paginated results (15 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $fishingLogs = FishingLog::where('user_id', auth()->id())
            ->with(['location', 'fish', 'fly', 'rod', 'friends', 'weather', 'waterCondition'])
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        return response()->json($fishingLogs);
    }

    /**
     * Store a newly created fishing log in storage.
     *
     * Creates a new fishing log entry for the authenticated user and associates
     * it with the specified friends using a many-to-many relationship through
     * the fishing_log_friend pivot table.
     */
    public function store(StoreFishingLogRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Check if this is a new species for the user
        $isNewSpecies = false;
        $isPersonalBest = false;
        $previousBestSize = null;

        if (!empty($validated['user_fish_id'])) {
            $previousCatch = FishingLog::where('user_id', auth()->id())
                ->where('user_fish_id', $validated['user_fish_id'])
                ->exists();

            $isNewSpecies = !$previousCatch;

            // Check for personal best (only if not a new species and max_size is provided)
            if (!$isNewSpecies && !empty($validated['max_size'])) {
                $previousBest = FishingLog::where('user_id', auth()->id())
                    ->where('user_fish_id', $validated['user_fish_id'])
                    ->whereNotNull('max_size')
                    ->max('max_size');

                if ($previousBest !== null && $validated['max_size'] > $previousBest) {
                    $isPersonalBest = true;
                    $previousBestSize = $previousBest;
                }
            }
        }

        // Create weather record if provided
        $userWeatherId = null;
        if (!empty($validated['weather']) && $this->hasAnyValue($validated['weather'])) {
            $weather = UserWeather::create([
                'user_id' => auth()->id(),
                'temperature' => $validated['weather']['temperature'] ?? null,
                'cloud' => $validated['weather']['cloud'] ?? null,
                'wind' => $validated['weather']['wind'] ?? null,
                'precipitation' => $validated['weather']['precipitation'] ?? null,
                'barometric_pressure' => $validated['weather']['barometric_pressure'] ?? null,
            ]);
            $userWeatherId = $weather->id;
        }

        // Create water condition record if provided
        $userWaterConditionId = null;
        if (!empty($validated['water_condition']) && $this->hasAnyValue($validated['water_condition'])) {
            $waterCondition = UserWaterCondition::create([
                'user_id' => auth()->id(),
                'temperature' => $validated['water_condition']['temperature'] ?? null,
                'clarity' => $validated['water_condition']['clarity'] ?? null,
                'level' => $validated['water_condition']['level'] ?? null,
                'speed' => $validated['water_condition']['speed'] ?? null,
                'surface_condition' => $validated['water_condition']['surface_condition'] ?? null,
                'tide' => $validated['water_condition']['tide'] ?? null,
            ]);
            $userWaterConditionId = $waterCondition->id;
        }

        // Use user-provided moon position, or calculate if not provided
        $moonAltitude = null;
        $moonPosition = $validated['moon_position'] ?? null;

        // Only calculate if user didn't provide a moon position and we have location
        if (empty($moonPosition) && !empty($validated['user_location_id'])) {
            $location = UserLocation::where('id', $validated['user_location_id'])
                ->where('user_id', auth()->id())
                ->first();

            if ($location && $location->latitude && $location->longitude) {
                $moonData = MoonPositionCalculator::calculate(
                    $validated['date'],
                    $validated['time'] ?? null,
                    $location->latitude,
                    $location->longitude
                );
                $moonAltitude = $moonData['altitude'];
                $moonPosition = $moonData['position'];
            }
        }

        // Create the fishing log
        $fishingLog = FishingLog::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'time' => $validated['time'] ?? null,
            'time_of_day' => $validated['time_of_day'] ?? null,
            'user_location_id' => $validated['user_location_id'] ?? null,
            'user_fish_id' => $validated['user_fish_id'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'max_size' => $validated['max_size'] ?? null,
            'max_weight' => $validated['max_weight'] ?? null,
            'user_fly_id' => $validated['user_fly_id'] ?? null,
            'user_rod_id' => $validated['user_rod_id'] ?? null,
            'user_weather_id' => $userWeatherId,
            'user_water_condition_id' => $userWaterConditionId,
            'style' => $validated['style'] ?? null,
            'moon_phase' => $validated['moon_phase'] ?? null,
            'moon_altitude' => $moonAltitude,
            'moon_position' => $moonPosition,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Attach friends if provided
        if (!empty($validated['friend_ids'])) {
            $fishingLog->friends()->attach($validated['friend_ids']);
        }

        // Dispatch badge checking job asynchronously
        CheckUserBadgesJob::dispatch(auth()->id());

        return response()->json([
            'fishing_log' => $fishingLog->load(['friends', 'fish', 'weather', 'waterCondition']),
            'is_new_species' => $isNewSpecies,
            'is_personal_best' => $isPersonalBest,
            'previous_best_size' => $previousBestSize,
        ], 201);
    }

    /**
     * Update the specified fishing log in storage.
     *
     * Updates an existing fishing log entry for the authenticated user and
     * syncs the associated friends.
     */
    public function update(StoreFishingLogRequest $request, FishingLog $fishingLog): JsonResponse
    {
        // Ensure the user owns this fishing log
        if ($fishingLog->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        // Handle weather record
        $userWeatherId = $fishingLog->user_weather_id;
        if (!empty($validated['weather']) && $this->hasAnyValue($validated['weather'])) {
            if ($fishingLog->weather) {
                // Update existing weather record
                $fishingLog->weather->update([
                    'temperature' => $validated['weather']['temperature'] ?? null,
                    'cloud' => $validated['weather']['cloud'] ?? null,
                    'wind' => $validated['weather']['wind'] ?? null,
                    'precipitation' => $validated['weather']['precipitation'] ?? null,
                    'barometric_pressure' => $validated['weather']['barometric_pressure'] ?? null,
                ]);
            } else {
                // Create new weather record
                $weather = UserWeather::create([
                    'user_id' => auth()->id(),
                    'temperature' => $validated['weather']['temperature'] ?? null,
                    'cloud' => $validated['weather']['cloud'] ?? null,
                    'wind' => $validated['weather']['wind'] ?? null,
                    'precipitation' => $validated['weather']['precipitation'] ?? null,
                    'barometric_pressure' => $validated['weather']['barometric_pressure'] ?? null,
                ]);
                $userWeatherId = $weather->id;
            }
        } elseif (isset($validated['weather']) && !$this->hasAnyValue($validated['weather'])) {
            // Weather was cleared - delete the record if it exists
            if ($fishingLog->weather) {
                $fishingLog->weather->delete();
                $userWeatherId = null;
            }
        }

        // Handle water condition record
        $userWaterConditionId = $fishingLog->user_water_condition_id;
        if (!empty($validated['water_condition']) && $this->hasAnyValue($validated['water_condition'])) {
            if ($fishingLog->waterCondition) {
                // Update existing water condition record
                $fishingLog->waterCondition->update([
                    'temperature' => $validated['water_condition']['temperature'] ?? null,
                    'clarity' => $validated['water_condition']['clarity'] ?? null,
                    'level' => $validated['water_condition']['level'] ?? null,
                    'speed' => $validated['water_condition']['speed'] ?? null,
                    'surface_condition' => $validated['water_condition']['surface_condition'] ?? null,
                    'tide' => $validated['water_condition']['tide'] ?? null,
                ]);
            } else {
                // Create new water condition record
                $waterCondition = UserWaterCondition::create([
                    'user_id' => auth()->id(),
                    'temperature' => $validated['water_condition']['temperature'] ?? null,
                    'clarity' => $validated['water_condition']['clarity'] ?? null,
                    'level' => $validated['water_condition']['level'] ?? null,
                    'speed' => $validated['water_condition']['speed'] ?? null,
                    'surface_condition' => $validated['water_condition']['surface_condition'] ?? null,
                    'tide' => $validated['water_condition']['tide'] ?? null,
                ]);
                $userWaterConditionId = $waterCondition->id;
            }
        } elseif (isset($validated['water_condition']) && !$this->hasAnyValue($validated['water_condition'])) {
            // Water condition was cleared - delete the record if it exists
            if ($fishingLog->waterCondition) {
                $fishingLog->waterCondition->delete();
                $userWaterConditionId = null;
            }
        }

        // Use user-provided moon position, or calculate if not provided
        $moonAltitude = null;
        $moonPosition = $validated['moon_position'] ?? null;

        // Only calculate if user didn't provide a moon position and we have location
        if (empty($moonPosition) && !empty($validated['user_location_id'])) {
            $location = UserLocation::where('id', $validated['user_location_id'])
                ->where('user_id', auth()->id())
                ->first();

            if ($location && $location->latitude && $location->longitude) {
                $moonData = MoonPositionCalculator::calculate(
                    $validated['date'],
                    $validated['time'] ?? null,
                    $location->latitude,
                    $location->longitude
                );
                $moonAltitude = $moonData['altitude'];
                $moonPosition = $moonData['position'];
            }
        }

        // Update the fishing log
        $fishingLog->update([
            'date' => $validated['date'],
            'time' => $validated['time'] ?? null,
            'time_of_day' => $validated['time_of_day'] ?? null,
            'user_location_id' => $validated['user_location_id'] ?? null,
            'user_fish_id' => $validated['user_fish_id'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'max_size' => $validated['max_size'] ?? null,
            'max_weight' => $validated['max_weight'] ?? null,
            'user_fly_id' => $validated['user_fly_id'] ?? null,
            'user_rod_id' => $validated['user_rod_id'] ?? null,
            'user_weather_id' => $userWeatherId,
            'user_water_condition_id' => $userWaterConditionId,
            'style' => $validated['style'] ?? null,
            'moon_phase' => $validated['moon_phase'] ?? null,
            'moon_altitude' => $moonAltitude,
            'moon_position' => $moonPosition,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Sync friends (this will remove old associations and add new ones)
        if (isset($validated['friend_ids'])) {
            $fishingLog->friends()->sync($validated['friend_ids']);
        } else {
            $fishingLog->friends()->sync([]);
        }

        // Dispatch badge checking job asynchronously
        CheckUserBadgesJob::dispatch(auth()->id());

        return response()->json([
            'fishing_log' => $fishingLog->load(['location', 'fish', 'fly', 'rod', 'friends', 'weather', 'waterCondition']),
        ]);
    }

    /**
     * Remove the specified fishing log from storage.
     *
     * Deletes a fishing log entry for the authenticated user.
     */
    public function destroy(FishingLog $fishingLog): JsonResponse
    {
        // Ensure the user owns this fishing log
        if ($fishingLog->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userId = auth()->id();

        // Delete the fishing log (friends will be automatically detached due to cascade)
        $fishingLog->delete();

        // Dispatch badge checking job to revoke any badges no longer qualified for
        CheckUserBadgesJob::dispatch($userId);

        return response()->json(['message' => 'Fishing log deleted successfully']);
    }

    /**
     * Get available years from fishing logs.
     *
     * Returns a list of distinct years from the user's fishing logs,
     * ordered from most recent to oldest. Always includes current year.
     */
    public function availableYears(): JsonResponse
    {
        $years = FishingLog::where('user_id', auth()->id())
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->map(fn($year) => (string) $year)
            ->values()
            ->toArray();

        // Always include current year even if no data exists for it
        $currentYear = (string) now()->year;
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        return response()->json($years);
    }

    /**
     * Calculate time of day based on time, date, and location.
     *
     * Uses the TimeOfDayCalculator service to determine the time of day
     * based on sunrise/sunset calculations for the given location.
     */
    public function calculateTimeOfDay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'time' => 'required|string',
            'date' => 'required|date',
            'location_id' => 'nullable|integer|exists:user_locations,id',
        ]);

        $latitude = null;
        $longitude = null;

        // Get coordinates from location if provided
        if (!empty($validated['location_id'])) {
            $location = UserLocation::where('id', $validated['location_id'])
                ->where('user_id', auth()->id())
                ->first();

            if ($location) {
                $latitude = $location->latitude;
                $longitude = $location->longitude;
            }
        }

        $timeOfDay = TimeOfDayCalculator::calculate(
            $validated['time'],
            $validated['date'],
            $latitude,
            $longitude
        );

        return response()->json([
            'time_of_day' => $timeOfDay,
        ]);
    }

    /**
     * Calculate moon position based on time, date, and location.
     *
     * Uses the MoonPositionCalculator service to determine the moon's position
     * based on astronomical calculations for the given location.
     */
    public function calculateMoonPosition(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'time' => 'nullable|string',
            'date' => 'required|date',
            'location_id' => 'nullable|integer|exists:user_locations,id',
        ]);

        $latitude = null;
        $longitude = null;

        // Get coordinates from location if provided
        if (!empty($validated['location_id'])) {
            $location = UserLocation::where('id', $validated['location_id'])
                ->where('user_id', auth()->id())
                ->first();

            if ($location) {
                $latitude = $location->latitude;
                $longitude = $location->longitude;
            }
        }

        // If no coordinates, return empty
        if ($latitude === null || $longitude === null) {
            return response()->json([
                'moon_position' => null,
                'moon_altitude' => null,
            ]);
        }

        $moonData = MoonPositionCalculator::calculate(
            $validated['date'],
            $validated['time'] ?? null,
            $latitude,
            $longitude
        );

        return response()->json([
            'moon_position' => $moonData['position'],
            'moon_altitude' => $moonData['altitude'],
        ]);
    }

    /**
     * Check if an array has any non-null, non-empty values.
     */
    private function hasAnyValue(?array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        foreach ($data as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }
}
