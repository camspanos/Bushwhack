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
            'user_fly_id' => 'nullable|exists:user_flies,id',
            'user_rod_id' => 'nullable|exists:user_rods,id',
            'style' => 'nullable|string|max:255',
            'moon_phase' => 'nullable|string|in:New Moon,Waxing Crescent,First Quarter,Waxing Gibbous,Full Moon,Waning Gibbous,Last Quarter,Waning Crescent',
            'friend_ids' => 'nullable|array',
            'friend_ids.*' => 'exists:user_friends,id',
            'notes' => 'nullable|string',
        ];
    }
}
