<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'metric' => ['nullable', 'in:0,1,true,false'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'city' => $input['city'] ?? null,
            'state' => $input['state'] ?? null,
            'country_id' => $input['country_id'] ?? null,
            'metric' => filter_var($input['metric'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);

        // Automatically follow user_id 1 (if it exists and is not the new user)
        $defaultUserToFollow = User::find(1);
        if ($defaultUserToFollow && $user->id !== 1) {
            $user->follow($defaultUserToFollow);
        }

        return $user;
    }
}
