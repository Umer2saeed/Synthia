<?php

namespace App\Http\Requests\Admin;

use App\Services\SanitizationService;
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
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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

    protected function prepareForValidation(): void
    {
        $sanitizer = app(\App\Services\SanitizationService::class);

        $mergeData = [];

        /*
        | Only sanitize fields that are actually present in the request.
        | If a field is null or missing, we do not force it to empty string
        | because that would break 'required' validation rules.
        | We only sanitize when the field has a value.
        */
        if ($this->has('title') && $this->title !== null) {
            $mergeData['title'] = $sanitizer->cleanText($this->title);
        }

        if ($this->has('content') && $this->content !== null) {
            $cleaned = $sanitizer->cleanRichText($this->content);

            /*
            | Only merge if the cleaned content is not empty.
            | If cleanRichText returns empty for non-empty input,
            | keep the original so validation can run on the raw value.
            | The fallback in cleanRichText should prevent this,
            | but this is an extra safety net.
            */
            if (trim($cleaned) !== '') {
                $mergeData['content'] = $cleaned;
            }
        }

        if ($this->has('ai_summary') && $this->ai_summary !== null) {
            $mergeData['ai_summary'] = $sanitizer->cleanText($this->ai_summary);
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }

        // Permission enforcement
        if (!$this->user()->can('publish posts')) {
            $this->merge([
                'status'      => 'draft',
                'is_featured' => false,
            ]);
        }
    }
}
