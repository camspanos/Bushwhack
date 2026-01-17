<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'rod_name' => 'required|string|max:255',
            'rod_weight' => 'nullable|string|max:255',
            'lure' => 'nullable|string|max:255',
            'line' => 'nullable|string|max:255',
            'tippet' => 'nullable|string|max:255',
        ];
    }
}
