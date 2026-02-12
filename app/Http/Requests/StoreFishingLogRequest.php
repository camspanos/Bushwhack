<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFishingLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'user_location_id' => 'nullable|exists:user_locations,id',
            'user_fish_id' => 'nullable|exists:user_fish,id',
            'quantity' => 'nullable|integer|min:0',
            'max_size' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'avg_size' => 'nullable|numeric|min:0',
            'avg_weight' => 'nullable|numeric|min:0',
            'user_fly_id' => 'nullable|exists:user_flies,id',
            'user_rod_id' => 'nullable|exists:user_rods,id',
            'style' => 'nullable|string|max:255',
            'moon_phase' => 'nullable|string|in:New Moon,Waxing Crescent,First Quarter,Waxing Gibbous,Full Moon,Waning Gibbous,Last Quarter,Waning Crescent',
            'moon_position' => 'nullable|string|in:Overhead,Rising,Setting,Underfoot,Above Horizon,Below Horizon',
            'time_of_day' => 'nullable|string|in:Dawn,Morning,Midday,Afternoon,Dusk,Night',
            'friend_ids' => 'nullable|array',
            'friend_ids.*' => 'exists:user_friends,id',
            'notes' => 'nullable|string',

            // Weather conditions
            'weather' => 'nullable|array',
            'weather.temperature' => 'nullable|string|max:50',
            'weather.cloud' => 'nullable|string|in:Clear,Partly Cloudy,Mostly Cloudy,Overcast',
            'weather.wind' => 'nullable|string|in:Calm,Light,Moderate,Strong,Very Strong',
            'weather.precipitation' => 'nullable|string|in:None,Light Rain,Moderate Rain,Heavy Rain,Light Snow,Heavy Snow,Sleet,Hail',
            'weather.barometric_pressure' => 'nullable|string|max:50',

            // Water conditions
            'water_condition' => 'nullable|array',
            'water_condition.temperature' => 'nullable|string|max:50',
            'water_condition.clarity' => 'nullable|string|in:Crystal Clear,Clear,Slightly Stained,Stained,Murky,Muddy',
            'water_condition.level' => 'nullable|string|in:Very Low,Low,Normal,High,Very High,Flood',
            'water_condition.speed' => 'nullable|string|in:Still,Slow,Moderate,Fast,Very Fast',
            'water_condition.surface_condition' => 'nullable|string|in:Calm,Rippled,Choppy,Rough,Very Rough',
            'water_condition.tide' => 'nullable|string|in:Low,Rising,High,Falling,Slack',
        ];
    }
}
