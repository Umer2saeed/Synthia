<?php

namespace App\Http\Requests\Admin;

use App\Services\SanitizationService;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage categories');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'slug.unique'   => 'This slug is already taken. Choose a different one.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $sanitizer = app(SanitizationService::class);

        $this->merge([
            'name'        => $sanitizer->cleanText($this->name),
            'description' => $sanitizer->cleanText($this->description),
        ]);
    }
}
