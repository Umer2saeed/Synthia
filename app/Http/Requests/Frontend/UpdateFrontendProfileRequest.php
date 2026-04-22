<?php

namespace App\Http\Requests\Frontend;

use App\Services\SanitizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFrontendProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'nullable', 'string', 'max:50', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($this->user()->id),
            ],
            'bio'    => ['nullable', 'string', 'max:300'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Your name is required.',
            'username.unique' => 'This username is already taken.',
            'avatar.max'      => 'Avatar must be smaller than 2MB.',
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
