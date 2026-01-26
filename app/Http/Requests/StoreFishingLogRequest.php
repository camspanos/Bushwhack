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
            'location_id' => 'nullable|exists:locations,id',
            'fish_id' => 'nullable|exists:user_fish,id',
            'quantity' => 'nullable|integer|min:0',
            'max_size' => 'nullable|numeric|min:0',
            'fly_id' => 'nullable|exists:user_flies,id',
            'equipment_id' => 'nullable|exists:user_rods,id',
            'style' => 'nullable|string|max:255',
            'friend_ids' => 'nullable|array',
            'friend_ids.*' => 'exists:user_friends,id',
            'notes' => 'nullable|string',
        ];
    }
}
