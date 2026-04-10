<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /*
            | 'current_password' is a built-in Laravel validation rule.
            | It checks the value against the authenticated user's
            | actual hashed password automatically.
            */
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'The current password you entered is incorrect.',
            'password.confirmed'                => 'The new password confirmation does not match.',
        ];
    }
}
