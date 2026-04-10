<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage roles');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is required.',
            'name.unique'   => 'This permission already exists.',
        ];
    }
}
