<?php

namespace App\Http\Requests\Admin;

use App\Services\SanitizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage categories');
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
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
