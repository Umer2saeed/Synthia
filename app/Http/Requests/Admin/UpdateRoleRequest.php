<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage roles');
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name'          => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Role name is required.',
        ];
    }
}
