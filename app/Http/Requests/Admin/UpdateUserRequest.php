<?php

namespace App\Http\Requests\Admin;

use App\Services\SanitizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage users');
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'nullable', 'string', 'max:50', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email'  => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'bio'    => ['nullable', 'string', 'max:300'],
            'status' => ['required', 'in:active,inactive'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'roles'  => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'User name is required.',
            'email.required'   => 'Email address is required.',
            'email.unique'     => 'This email is already registered.',
            'username.unique'  => 'This username is already taken.',
            'avatar.max'       => 'Avatar must be smaller than 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $sanitizer = app(SanitizationService::class);

        $this->merge([
            'name'     => $sanitizer->cleanText($this->name),
            'username' => $sanitizer->cleanUsername($this->username),
            'bio'      => $sanitizer->cleanText($this->bio),
        ]);
    }
}
