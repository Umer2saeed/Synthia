<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Post;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        /*
        | For update we need to check both permission AND ownership.
        | $this->route('post') returns the Post model from route binding.
        | This is the same ownership check that was in the controller.
        */
        $post = $this->route('post');

        return $this->user()->can('edit all posts') ||
            ($this->user()->can('edit own posts') && $post->user_id === $this->user()->id);
    }

    public function rules(): array
    {
        /*
        | $this->route('post') gives us the current Post model.
        | We need its ID to use Rule::unique()->ignore() so the post
        | can keep its own slug without triggering a unique conflict.
        */
        $post = $this->route('post');

        return [
            'title'        => ['required', 'string', 'max:255'],
            'slug'         => ['nullable', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($post->id)],
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

    public function messages(): array
    {
        return [
            'title.required'       => 'The post title is required.',
            'category_id.required' => 'Please select a category.',
            'content.required'     => 'Post content cannot be empty.',
            'cover_image.max'      => 'Cover image must be smaller than 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $post = $this->route('post');

        if (!$this->user()->can('publish posts')) {
            /*
            | Author cannot change a draft to published.
            | But if an editor already published it, keep it published.
            | We only lock it to draft if it was already a draft.
            */
            if ($post->status === 'draft') {
                $this->merge(['status' => 'draft']);
            }

            // Author cannot change featured status
            $this->merge(['is_featured' => $post->is_featured]);
        }
    }
}
