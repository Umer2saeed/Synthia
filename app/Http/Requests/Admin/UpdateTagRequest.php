<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage categories');
    }

    public function rules(): array
    {
        $tag = $this->route('tag');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('tags', 'name')->ignore($tag->id)],
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('tags', 'slug')->ignore($tag->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tag name is required.',
            'name.unique'   => 'This tag name already exists.',
        ];
    }
}
