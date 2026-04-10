<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | authorize()
    |--------------------------------------------------------------------------
    | Runs BEFORE validation.
    | Return true  → allow the request through to validation
    | Return false → immediately return 403 Forbidden
    |
    | We check the permission here instead of the controller.
    | This means the controller never even runs if unauthorized.
    */
    public function authorize(): bool
    {
        return $this->user()->can('create posts');
    }

    /*
    |--------------------------------------------------------------------------
    | rules()
    |--------------------------------------------------------------------------
    | Define all validation rules for this specific form.
    | These are identical to what was in PostController@store before.
    | The difference is they now live in one dedicated class.
    */
    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'slug'         => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'category_id'  => ['required', 'exists:categories,id'],
            'content'      => ['required', 'string'],
            'status'       => ['required', 'in:draft,published,scheduled'],
            'is_featured'  => ['nullable', 'boolean'],
            'ai_summary'   => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tags'         => ['nullable', 'array'],
            'tags.*'       => ['exists:tags,id'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | messages()
    |--------------------------------------------------------------------------
    | Custom error messages for specific rules.
    | Format: 'field.rule' => 'message'
    | These replace the default Laravel messages in the view.
    */
    public function messages(): array
    {
        return [
            'title.required'       => 'The post title is required.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists'   => 'The selected category does not exist.',
            'content.required'     => 'Post content cannot be empty.',
            'cover_image.image'    => 'The cover must be an image file.',
            'cover_image.max'      => 'Cover image must be smaller than 2MB.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | prepareForValidation()
    |--------------------------------------------------------------------------
    | Runs BEFORE rules() are applied.
    | Modify or clean input data before validation runs.
    |
    | Here we enforce the publish permission rule at the request level.
    | If the user cannot publish, we force status to draft BEFORE
    | the data even reaches the controller.
    */
    protected function prepareForValidation(): void
    {
        /*
        | If user cannot publish posts, override whatever status
        | they submitted to 'draft'. This is a server-side guard
        | on top of the UI hiding the status field for authors.
        */
        if (!$this->user()->can('publish posts')) {
            $this->merge([
                'status'      => 'draft',
                'is_featured' => false,
            ]);
        }
    }
}
