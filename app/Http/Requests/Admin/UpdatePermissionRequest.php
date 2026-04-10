<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage roles');
    }

    public function rules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ];
    }
}
