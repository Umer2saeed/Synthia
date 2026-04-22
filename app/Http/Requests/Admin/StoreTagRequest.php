<?php

namespace App\Http\Requests\Admin;

use App\Services\SanitizationService;
use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage categories');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:tags,name'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:tags,slug'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tag name is required.',
            'name.unique'   => 'This tag already exists.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $sanitizer = app(SanitizationService::class);

        $this->merge([
            'name' => $sanitizer->cleanText($this->name),
        ]);
    }
}
